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
use Symfony\Component\HttpFoundation\Session\SessionInterface;


#[Route('/reclamations')]
class ReclamationsController extends AbstractController
{
    #[Route('/', name: 'app_reclamations_index', methods: ['GET'])]
    public function index(Request $request, ReclamationsRepository $reclamationsRepository, PaginatorInterface $paginator): Response
    {
        // Get the search query from the request
        $searchQuery = $request->query->get('query');

        if ($searchQuery) {
            // If there's a search query, fetch by it (assuming a custom method in the repository)
            $reclamations = $reclamationsRepository->searchById($searchQuery);
        } else {
            // Otherwise, fetch all reclamations sorted by date of creation (descending order)
            $reclamations = $reclamationsRepository->findBy([], ['dateCreation' => 'ASC']);
        }

        // Paginate the results
        $pagination = $paginator->paginate(
            $reclamations,
            $request->query->getInt('page', 1), // Current page, default is 1
            5 // Number of items per page
        );

        // Render the view with the paginated results and the search query
        return $this->render('back/reclamations/index.html.twig', [
            'reclamations' => $pagination,
            'searchQuery' => $searchQuery,
        ]);
    }

    #[Route('/reclamations/{idReclamation}/repondre', name: 'app_reclamations_repondre', methods: ['GET', 'POST'])]
    public function repondre(int $idReclamation, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Récupérer la réclamation par son ID
        $reclamation = $entityManager->getRepository(Reclamations::class)->find($idReclamation);

        if (!$reclamation) {
            throw $this->createNotFoundException('Réclamation introuvable');
        }

        // Si la requête est POST, nous voulons traiter le formulaire
        if ($request->isMethod('POST')) {
            dump($request->request->all()); // Pour voir les données POST

            // Récupérer les données du formulaire
            $idUtilisateur = $request->request->get('reclamations')['idUtilisateur'];
            dump($idUtilisateur);
            $email = $request->request->get('email');
            $objet = $request->request->get('objet');
            $texte = $request->request->get('texte');

            // Valider que l'ID de l'utilisateur n'est pas vide ou nul
            if (!$idUtilisateur) {
                throw $this->createNotFoundException('ID d\'utilisateur non fourni.');
            }

            // Récupérer l'utilisateur à partir de la base de données
            $utilisateur = $entityManager->getRepository(User::class)->find($idUtilisateur);

            if (!$utilisateur) {
                throw $this->createNotFoundException('Utilisateur non trouvé.');
            }

            // Créer une nouvelle entrée Reponses
            $reponse = new Reponses();
            $reponse->setIdReclamation($reclamation); // Utiliser l'objet réclamation
            $reponse->setEmail($email);
            $reponse->setObjet($objet);
            $reponse->setTexte($texte);
            $reponse->setIdUtilisateur($utilisateur); // Associer l'utilisateur à la réponse

            // Persister dans la base de données
            $entityManager->persist($reponse);
            $entityManager->flush();

            // Rediriger ou afficher un message de succès
            $this->addFlash('success', 'Réponse enregistrée avec succès.');

            // Rediriger vers une autre page après soumission
            return $this->redirectToRoute('app_reponsesb_index');
        }


        // Récupérer les utilisateurs associés à cette réclamation pour le formulaire
        $users = $entityManager->getRepository(User::class)->findAll();

        // Rendre le gabarit avec les données appropriées
        return $this->render('back/reclamations/rep_rec.html.twig', [
            'reclamation' => $reclamation,
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'app_reclamations_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $Userid = $session->get('user_id');
        $User = $entityManager->getRepository(User::class)->find($Userid);



        $reclamation = new Reclamations();
        $reclamation->setIdUtilisateur($User);
        $reclamation->setEmail($User->getEmail());


        $form = $this->createForm(ReclamationsType::class, $reclamation, [
            'disable_etat' => true, // Désactiver le champ 'etat' lors de la création
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($reclamation->getEtat() === null) {
                $reclamation->setEtat(0); // Assurez-vous que 'etat' a une valeur par défaut
            }
            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reponses_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/reclamations/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/newb', name: 'app_reclamationsb_new', methods: ['GET', 'POST'])]
    public function newb(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $Userid = $session->get('user_id');
        $User = $entityManager->getRepository(User::class)->find($Userid);



        $reclamation = new Reclamations();
        $reclamation->setIdUtilisateur($User);
        $reclamation->setEmail($User->getEmail());


        $form = $this->createForm(ReclamationsType::class, $reclamation, [
            'disable_etat' => true, // Désactiver le champ 'etat' lors de la création
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($reclamation->getEtat() === null) {
                $reclamation->setEtat(0); // Assurez-vous que 'etat' a une valeur par défaut
            }
            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/reclamations/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{idReclamation}', name: 'app_reclamations_show', methods: ['GET'])]
    public function show($idReclamation, ReclamationsRepository $reclamationsRepository): Response
    {
        // Fetch the Reclamations entity by ID
        $reclamation = $reclamationsRepository->find($idReclamation);

        // Check if the entity exists
        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamations object not found');
        }

        // Render the template with the fetched entity
        return $this->render('front/reclamations/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/reclamations/{idReclamation}', name: 'app_reclamations_display', methods: ['GET'])]
    public function display($idReclamation, ReclamationsRepository $reclamationsRepository): Response
    {
        // Fetch the Reclamations entity by ID
        $reclamation = $reclamationsRepository->find($idReclamation);

        // Check if the entity exists
        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamations object not found');
        }

        // Render the template with the fetched entity
        return $this->render('front/reclamations/rep-rec.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{idReclamation}/edit', name: 'app_reclamations_edit', methods: ['GET', 'POST'])]
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

        return $this->renderForm('front/reclamations/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{idReclamation}', name: 'app_reclamations_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamations $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getIdReclamation(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

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

    #[Route('/export/pdf', name: 'app_reclamations_export_pdf', methods: ['GET'])]
    public function exportToPdf(ReclamationsRepository $reclamationsRepository): Response
    {
        // Fetch all reclamations
        $reclamations = $reclamationsRepository->findAll();

        // Render the PDF template with the fetched data
        $html = $this->renderView('front/reclamations/pdf_export.html.twig', [
            'reclamations' => $reclamations
        ]);

        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        // Create Dompdf
        $dompdf = new Dompdf($options);

        // Load HTML content into Dompdf
        $dompdf->loadHtml($html);

        // Render the PDF
        $dompdf->render();

        // Create the PDF response
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="reclamations.pdf"');

        return $response;
    }

    /**
     * @Route("/reclamations/pdf", name="reclamations_pdf")
     */
    public function generateRolePdf(ReclamationsRepository $reclamationsRepository, Request $request): Response
    {
        // Récupérer le paramètre de requête 'query' depuis la requête
        $query = $request->query->get('query');

        // Recherche de l'objet reclamations manuellement
        $reponse = $reclamationsRepository->findOneById($query);

        // Vérifier si un objet reclamations a été trouvé
        if (!$reponse) {
            throw $this->createNotFoundException('Aucune réponse trouvée pour le paramètre donné.');
        }

        // Rendre la vue pour l'export PDF
        $html = $this->renderView('front/reclamations/pdf_export.html.twig', [
            'reclamations' => $reponse
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
     * @Route("/reclamations/search", name="app_reclamations_search")
     */
    public function search(Request $request, ReclamationsRepository $reclamationsRepository, PaginatorInterface $paginator): Response
    {
        // Récupérer le terme de recherche depuis la requête
        $searchTerm = $request->query->get('q');

        // Effectuer la recherche dans le dépôt de réponses
        $results = $reclamationsRepository->search($searchTerm);

        // Paginer les résultats
        $pagination = $paginator->paginate(
            $results,
            $request->query->getInt('page', 1), // Current page number, default is 1
            2 // Number of items per page
        );

        // Rendre la vue avec les résultats de la recherche paginés
        return $this->render('front/reclamations/index.html.twig', [
            'pagination'=>$pagination,
            'reclamations' => $pagination,
            'searchTerm' => $searchTerm
        ]);
    }
}
