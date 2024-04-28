<?php

namespace App\Controller;

use App\Entity\Reponses;
use App\Entity\Reclamations;
use App\Entity\User;
use App\Form\ReponsesType;
use App\Repository\ReponsesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
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
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;



class ReponsesController extends AbstractController
{
    #[Route('/user/reclamations/reponses', name: 'app_reponses_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request, ReponsesRepository $reponsesRepository, PaginatorInterface $paginator): Response
    {         

        // Get the search query from the request
        $reponses = $entityManager
            ->getRepository(Reponses::class)
            ->findAll();

        // Fetch all reclamations or search based on the query
        // Paginate the results
        $pagination = $paginator->paginate(
            $reponses,
            $request->query->getInt('page', 1), // Current page number, default is 1
            5// Number of items per page
        );

        // Render the view with the search results
        return $this->render('reponses/index.html.twig', [
            'reponses' => $pagination, $reponses,
        ]);
    }

    #[Route('admin/reclamations/reponses/all', name: 'app_reponses_index_admin', methods: ['GET'])]
    public function indexAdmin(EntityManagerInterface $entityManager, Request $request, ReponsesRepository $reponsesRepository, PaginatorInterface $paginator): Response
    {         if (!$this->getUser())
        return $this->redirectToRoute('user/reclamations/reponsesapp_login');

        // Render the view with the search results
        return $this->render('admin/indexReponses.html.twig', [
        ]);
    }
    

    private ManagerRegistry $managerRegistry;

    private AuthorizationCheckerInterface $authChecker;


    public function __construct(ManagerRegistry $managerRegistry,AuthorizationCheckerInterface $authChecker)
    {
        $this->managerRegistry=$managerRegistry;
        $this->authChecker = $authChecker;
    }

    

    public function getReponsesTable(ReponsesRepository $Repo){

           /** @var Users $user */
           $user = $this->getUser();
           if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            $queryBuilder = $Repo->createQueryBuilder('r')
            ->select('r','p')
            ->leftJoin('r.idReclamation','p');}
            else{
                $queryBuilder = $Repo->createQueryBuilder('r')
                ->select('r', 'p')
                ->leftJoin('r.idReclamation', 'p')
                ->where('p.idUtilisateur = :userId')
                ->setParameter('userId', $user->getId()); 
            
            }



        $table = (new Table())
            ->setRowsPerPage(5)// custom rows per page
            ->setId('reponses_list')
            ->setPath($this->generateUrl('reponses_list_ajax'))
            ->setTemplate('reponses/reponsesTableAJAXCustoms.html.twig')
            ->setQueryBuilder($queryBuilder, 'r')

            

            ->addColumn(
                (new Column())->setLabel('ID Reponse')
                ->setSort(['r.id' => 'asc', 'r.id' => 'asc'])
                ->setSortReverse(['r.id' => 'desc', 'r.id' => 'desc'])
                    ->setFilter(
                        (new Filter())
                            ->setField('r.id')
                            ->setName('r_id')
                    )
            )

            ->addColumn(
                (new Column())->setLabel('ID Reclamation')
                ->setSort(['p.id' => 'asc', 'r.id' => 'asc'])
                ->setSortReverse(['p.id' => 'desc', 'r.id' => 'asc'])
                    ->setFilter(
                        (new Filter())
                            ->setField('p.id')
                            ->setName('p_id')
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
                (new Column())->setLabel('Date Reponse')
                    ->setSort(['r.id' => 'asc', 'r.id' => 'asc'])
                    ->setSortReverse(['r.id' => 'desc', 'r.id' => 'asc'])
                    ->setDisplayFormat(Column::FORMAT_DATE)
                    ->setDisplayFormatParams('d/m/Y')
                    ->setFilter(
                        (new Filter())
                            ->setField('r.dateReponse')
                            ->setName('r_dateReponse')
                            ->setDataFormat(Filter::FORMAT_DATE)
                    )

            );
                return $table;
    }

    #[Route('user/reclamations/reponses/reponsesAJAX', name: 'reponses_list')]
    public function listAction(TableService $tableService,ReponsesRepository $repo)
    {
        return $this->render(
            'reponses/reponsesTableAJAX.html.twig',
            [
                'table' => $tableService->createFormView($this->getReponsesTable($repo)),
            ]
        );
    }

    #[Route('user/reclamations/reponses/reponsesAJAX/AJAX', name: 'reponses_list_ajax')]
    public function _listAction(Request $request, TableService $tableService,ReponsesRepository $re)
    {
      return $tableService->handleRequest($this->getReponsesTable($re),$request);
    }



    
    #[Route('admin/reclamations/reponses/repondre/{id}', name: 'app_reclamations_repondre_admin', methods: ['GET', 'POST'])]
    public function repondre(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Récupérer la réclamation par son ID
        $reclamation = $entityManager->getRepository(Reclamations::class)->find($id);

                     /** @var \App\Entity\User $user */
                     $user = $this->getUser();

        if (!$reclamation) {
            throw $this->createNotFoundException('Réclamation introuvable');
        }

        // Si la requête est POST, nous voulons traiter le formulaire
        if ($request->isMethod('POST')) {
            dump($request->request->all()); // Pour voir les données POST

            // Récupérer les données du formulaire

            $email = $request->request->get('email');
            $objet = $request->request->get('objet');
            $texte = $request->request->get('texte');





            // Créer une nouvelle entrée Reponses
            $reponse = new Reponses();
            $reponse->setIdReclamation($reclamation); // Utiliser l'objet réclamation
            $reponse->setEmail($user->getEmail());
            $reponse->setObjet($objet);
            $reponse->setTexte($texte);
            $reponse->setIdUtilisateur($user); // Associer l'utilisateur à la réponse

            // Persister dans la base de données
            $entityManager->persist($reponse);
            $reclamation->setEtat(1);
            $entityManager->persist($reclamation);

            $entityManager->flush();

            // Rediriger ou afficher un message de succès
            $this->addFlash('success', 'Réponse enregistrée avec succès.');

            // Rediriger vers une autre page après soumission
            return $this->redirectToRoute('app_reponses_index');
        }


        // Récupérer les utilisateurs associés à cette réclamation pour le formulaire
        $users = $entityManager->getRepository(User::class)->findAll();

        // Rendre le gabarit avec les données appropriées
        return $this->render('admin/createReponse.html.twig', [
            'reclamation' => $reclamation,
            'users' => $users,
        ]);
    }

    #[Route('user/reclamations/reponses/{id}', name: 'app_reponses_show_user', methods: ['GET'])]
    public function showUser(Reponses $reponse): Response
    {
        return $this->render('reponses/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }
    

    #[Route('admin/reclamations/reponses/{id}', name: 'app_reponses_show', methods: ['GET'])]
    public function show(Reponses $reponse): Response
    {
        return $this->render('admin/showReponse.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('admin/reclamations/reponses/edit/{id}', name: 'app_reponses_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponses $reponse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReponsesType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reponses_index_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/editReponse.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('admin/reclamations/reponses/delete/{id}', name: 'app_reponses_delete', methods: ['POST','GET'])]
    public function delete(Request $request, Reponses $reponse, EntityManagerInterface $entityManager): Response
    {

        $reclamation = $reponse->getIdReclamation();

            $entityManager->remove($reponse);
            $reclamation->setEtat(0);
            $entityManager->persist($reclamation);

            $entityManager->flush();



        return $this->redirectToRoute('app_reponses_index_admin', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('user/reclamations/reponses/export/excel', name: 'app_reponses_export_excel', methods: ['GET'])]
    public function exportToExcel(ReponsesRepository $reponsesRepository): BinaryFileResponse
    {
        $reponses = $reponsesRepository->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID Réponse');
        $sheet->setCellValue('B1', 'Email');
        // Ajoutez les autres colonnes selon votre structure Reponses

        $row = 2;
        foreach ($reponses as $reponse) {
            $sheet->setCellValue('A' . $row, $reponse->getId());
            $sheet->setCellValue('B' . $row, $reponse->getEmail());
            // Ajoutez les autres colonnes selon votre structure Reponses
            $row++;
        }

        $fileName = 'reponses_' . date('YmdHis') . '.xlsx';
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);

        return $response;
    }


//    #[Route("/pdf", name: "reponses_pdf")]
    /**
     * @Route("/reponses/pdf", name="reponses_pdf")
     */
    public function generateRolePdf(ReponsesRepository $reponsesRepository, Request $request): Response
    {
        // Récupérer le paramètre de requête 'query' depuis la requête
        $query = $request->query->get('query');

        // Recherche de l'objet Reponses manuellement
        $reponse = $reponsesRepository->findOneById($query);

        // Vérifier si un objet Reponses a été trouvé
        if (!$reponse) {
            throw $this->createNotFoundException('Aucune réponse trouvée pour le paramètre donné.');
        }

        // Rendre la vue pour l'export PDF
        $html = $this->renderView('reponses/pdf_export.html.twig', [
            'reponses' => $reponse
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

    /**
     * @Route("/reponses/search", name="app_reponses_search")
     */
    public function search(Request $request, ReponsesRepository $reponsesRepository, PaginatorInterface $paginator): Response
    {
        // Récupérer le terme de recherche depuis la requête
        $searchTerm = $request->query->get('q');

        // Effectuer la recherche dans le dépôt de réponses
        $results = $reponsesRepository->search($searchTerm);

        // Paginer les résultats
        $pagination = $paginator->paginate(
            $results,
            $request->query->getInt('page', 1), // Current page number, default is 1
            5 // Number of items per page
        );

        // Rendre la vue avec les résultats de la recherche paginés
        return $this->render('reponses/index.html.twig', [
            'pagination'=>$pagination,
            'reponses' => $pagination,
            'searchTerm' => $searchTerm
        ]);
    }

}

