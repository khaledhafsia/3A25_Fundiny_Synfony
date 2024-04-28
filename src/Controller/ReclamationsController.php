<?php

namespace App\Controller;


use App\Entity\Reclamations;
use App\Entity\Projet;
use App\Entity\User;
use App\Entity\Reponses;
use App\Entity\Typesreclamation;
use App\Form\ReclamationsType;
use App\Repository\ReclamationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Knp\Component\Pager\PaginatorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Kilik\TableBundle\Components\Column;
use Kilik\TableBundle\Components\Filter;
use Kilik\TableBundle\Components\FilterCheckbox;
use Kilik\TableBundle\Components\FilterSelect;
use Kilik\TableBundle\Components\MassAction;
use Kilik\TableBundle\Components\Table;
use Kilik\TableBundle\Services\TableService;
use PHPUnit\Framework\Constraint\Callback;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ReclamationsController extends AbstractController
{   private Callback $callback;

    private ManagerRegistry $managerRegistry;

    private AuthorizationCheckerInterface $authChecker;


    public function __construct(ManagerRegistry $managerRegistry,AuthorizationCheckerInterface $authChecker)
    {
        $this->managerRegistry=$managerRegistry;
        $this->authChecker = $authChecker;
    }

    

    public function getReclamationsTable(ReclamationsRepository $Repo){
                /** @var Users $user */
                $user = $this->getUser();
        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            $queryBuilder = $Repo->createQueryBuilder('r')
            ->select('r', 'p', "CASE WHEN r.etat = 0 THEN 'En attente' ELSE 'Repondu' END AS status")
            ->leftJoin('r.idTypeReclamation', 'p');      
          } else {


            $queryBuilder = $Repo->createQueryBuilder('r')
                ->select('r', 'p', "CASE WHEN r.etat = 0 THEN 'En attente' ELSE 'Repondu' END AS status")
                ->leftJoin('r.idTypeReclamation', 'p')
                ->where('r.idUtilisateur = :userId')
                ->setParameter('userId', $user->getId()); 
        }

        $table = (new Table())
            ->setRowsPerPage(5)// custom rows per page
            ->setId('reclamations_list')
            ->setPath($this->generateUrl('reclamations_list_ajax'))
            ->setTemplate('admin/reclamationTableAJAXCustoms.html.twig')
            ->setQueryBuilder($queryBuilder, 'r')
            ->addColumn(
                (new Column())->setLabel('id')
                ->setSort(['r.id' => 'asc', 'r.id' => 'asc'])
                ->setSortReverse(['r.id' => 'desc', 'r.id' => 'desc'])
                    ->setFilter(
                        (new Filter())
                            ->setField('r.id')
                            ->setName('r_id')
                    )
            )
            
            ->addColumn(
                (new Column())->setLabel('email')
                ->setSort(['r.email' => 'asc', 'r.id' => 'asc'])
                ->setSortReverse(['r.email' => 'desc', 'r.id' => 'asc'])

                    ->setFilter(
                        (new Filter())
                            ->setField('r.email')
                            ->setName('r_email')
                    )
            )


            ->addColumn(
                (new Column())->setLabel('Type Reclamation')
                ->setSort(['p.nomTypeReclamation' => 'asc', 'r.id' => 'asc'])
                ->setSortReverse(['p.nomTypeReclamation' => 'desc', 'r.id' => 'asc'])
                    ->setFilter(
                        (new Filter())
                            ->setField('p.nomTypeReclamation')
                            ->setName('p_nomTypeReclamation')
                    )
            )

            
            ->addColumn(
                (new Column())->setLabel('Texte')
                    ->setFilter(
                        (new Filter())
                            ->setField('r.texte')
                            ->setName('r_texte')
                    )
            )


            ->addColumn(
                (new Column())->setLabel('Objet')
                    ->setFilter(
                        (new Filter())
                            ->setField('r.objet')
                            ->setName('r_objet')
                    )
            )

            
            ->addColumn(
                (new Column())->setLabel('status')
                    ->setFilter(
                        (new Filter())
                            ->setField('status')
                            ->setName('status')
                    )
            )
            ->addColumn(
                (new Column())->setLabel('Creation Date')
                    ->setSort(['r.id' => 'asc', 'r.id' => 'asc'])
                    ->setSortReverse(['r.id' => 'desc', 'r.id' => 'asc'])
                    ->setDisplayFormat(Column::FORMAT_DATE)
                    ->setDisplayFormatParams('d/m/Y')
                    ->setFilter(
                        (new Filter())
                            ->setField('r.dateCreation')
                            ->setName('r_dateCreation')
                            ->setDataFormat(Filter::FORMAT_DATE)
                    )

            );
                return $table;
    }

    #[Route('/admin/reclamationsAJAX', name: 'reclamations_list')]
    public function listAction(TableService $tableService,ReclamationsRepository $repo)
    {
        return $this->render(
            'admin/reclamationTableAJAX.html.twig',
            [
                'table' => $tableService->createFormView($this->getReclamationsTable($repo)),
            ]
        );
    }

    #[Route('/admin/reclamationsAJAX/AJAX', name: 'reclamations_list_ajax')]
    public function _listAction(Request $request, TableService $tableService,ReclamationsRepository $re)
    {
      return $tableService->handleRequest($this->getReclamationsTable($re),$request);
    }


    #[Route('/admin/reclamations', name: 'app_reclamations_index_admin', methods: ['GET'])]
    public function indexAdmin(Request $request, ReclamationsRepository $reclamationsRepository, PaginatorInterface $paginator): Response
    {
        if (!$this->getUser())
        return $this->redirectToRoute('app_login');


        return $this->render('admin/index.html.twig', [
        ]);
    }

    

    


    #[Route('/reclamations/pdf', name: 'app_reclamations_export_pdf', methods: ['GET'])]
    public function generateRolePdf(ReclamationsRepository $reclamationsRepository, Request $request): Response
    {
        // Récupérer le paramètre de requête 'query' depuis la requête
        $searchQuery = $request->query->get('query');

        if ($searchQuery) {
            // If there's a search query, fetch by it (assuming a custom method in the repository)
            $reclamations = $reclamationsRepository->searchById($searchQuery);
        } else {
            // Otherwise, fetch all reclamations sorted by date of creation (descending order)
            $reclamations = $reclamationsRepository->findBy([], ['dateCreation' => 'ASC']);
        }

        // Vérifier si un objet Reponses a été trouvé
        if (!$reclamations) {
            throw $this->createNotFoundException('Aucune reclamation trouvée pour le paramètre donné.');
        }

        // Rendre la vue pour l'export PDF
        $html = $this->renderView('reclamations/pdf_export.html.twig', [
            'reclamations' => $reclamations
        ]);
        dump($html);

        // Configuration de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        // Création de Dompdf
        $dompdf = new Dompdf($options);

        // Charger le contenu HTML dans Dompdf
        $dompdf->loadHtml($html);

        // Rendre le PDF
        $dompdf->render();

        // Créer la réponse PDF
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="reponse.pdf"');

        return $response;
    }


    #[Route('/reclamations', name: 'app_reclamations_index', methods: ['GET'])]
    public function index(Request $request, ReclamationsRepository $reclamationsRepository, PaginatorInterface $paginator): Response
    {           if (!$this->getUser())
        return $this->redirectToRoute('app_login');

        // Get the search query from the request
        $searchQuery = $request->query->get('query');

        if ($searchQuery) {
            // If there's a search query, fetch by it (assuming a custom method in the repository)
            $reclamations = $reclamationsRepository->searchById($searchQuery);
        } else {
            // Otherwise, fetch all reclamations sorted by date of creation (descending order)
            $reclamations = $reclamationsRepository->findBy(['email' => $this->getUser()->getUserIdentifier()], ['dateCreation' => 'ASC']);
        }

        // Paginate the results
        $pagination = $paginator->paginate(
            $reclamations,
            $request->query->getInt('page', 1), // Current page, default is 1
            5 // Number of items per page
        );

        // Render the view with the paginated results and the search query
        return $this->render('reclamations/index.html.twig', [
            'reclamations' => $pagination,
            'searchQuery' => $searchQuery,
        ]);
    }


    #[Route('/reclamations/new', name: 'app_reclamations_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser())
            return $this->redirectToRoute('app_login');

        $reclamation = new Reclamations();
        $form = $this->createForm(ReclamationsType::class, $reclamation, [
            'disable_etat' => true, // Désactiver le champ 'etat' lors de la création
        ]);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
             /** @var \App\Entity\User $user */
            $user = $this->getUser();
            if ($reclamation->getEtat() === null) {
                $reclamation->setEtat(0); // Assurez-vous que 'etat' a une valeur par défaut
            }
            $reclamation->setEmail($user->getEmail());
            $reclamation->setIdUtilisateur($user);

            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamations/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/allReclamations', name: 'admin_reclamations_all')]
    public function adminReclamationBackEnd(){
        return $this->render('admin/allReclamations.html.twig'); 
    }

    #[Route('/reclamations/{id}', name: 'app_reclamations_show', methods: ['GET'])]
    public function show($id, ReclamationsRepository $reclamationsRepository): Response
    {
        // Fetch the Reclamations entity by ID
        $reclamation = $reclamationsRepository->find($id);

        // Check if the entity exists
        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamations object not found');
        }

        // Render the template with the fetched entity
        return $this->render('reclamations/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }


    #[Route('/admin/reclamations/{id}', name: 'app_reclamations_admin_show', methods: ['GET'])]
    public function adminShow($id, ReclamationsRepository $reclamationsRepository): Response
    {
        // Fetch the Reclamations entity by ID
        $reclamation = $reclamationsRepository->find($id);

        // Check if the entity exists
        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamations object not found');
        }

        // Render the template with the fetched entity
        return $this->render('admin/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }





    #[Route('/reclamations/{id}/edit', name: 'app_reclamations_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamations $reclamation, EntityManagerInterface $entityManager): Response
    {
        // Passer l'option 'disable_etat' à 'true' pour désactiver le champ
        $form = $this->createForm(ReclamationsType::class, $reclamation, [
            'disable_etat' => true, // Désactiver le champ 'état'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_reclamations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reclamations/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }


    #[Route('/admin/reclamations/{id}/edit', name: 'app_reclamations_edit_admin', methods: ['GET', 'POST'])]
    public function editAdmin(Request $request, Reclamations $reclamation, EntityManagerInterface $entityManager): Response
    {
        // Passer l'option 'disable_etat' à 'true' pour désactiver le champ
        $form = $this->createForm(ReclamationsType::class, $reclamation, [
            'disable_etat' => true, // Désactiver le champ 'état'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_reclamations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/admin/reclamations/delete/{id}', name: 'app_reclamations_delete_admin', methods: ['POST','GET'])]
    public function deleteAdmin(Request $request, Reclamations $reclamation, EntityManagerInterface $entityManager): Response
    {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        

        return $this->redirectToRoute('admin_reclamations_all', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/reclamations/delete/{id}', name: 'app_reclamations_delete', methods: ['POST','GET'])]
    public function delete(Request $request, Reclamations $reclamation, EntityManagerInterface $entityManager): Response
    {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        return $this->redirectToRoute('app_reclamations_index', [], Response::HTTP_SEE_OTHER);
    }
    

    #[Route('/export/excel', name: 'app_reclamations_export_excel', methods: ['GET'])]
    public function exportToExcel(ReclamationsRepository $reclamationsRepository): Response
    {
        $reclamations = $reclamationsRepository->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID Réclamation');
        $sheet->setCellValue('B1', 'Email');
        // Add other columns according to your Reclamations structure

        $row = 2;
        foreach ($reclamations as $reclamation) {
            $sheet->setCellValue('A' . $row, $reclamation->getIdReclamation());
            $sheet->setCellValue('B' . $row, $reclamation->getEmail());
            // Add other columns according to your Reclamations structure
            $row++;
        }

        $fileName = 'reclamations_' . date('YmdHis') . '.xlsx';
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);

        return $response;
    }






}









