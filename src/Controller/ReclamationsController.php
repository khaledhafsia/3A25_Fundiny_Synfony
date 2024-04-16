<?php

namespace App\Controller;

use App\Entity\Reclamations;
use App\Entity\Projet;
use App\Entity\User;
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

#[Route('/reclamations')]
class ReclamationsController extends AbstractController
{
    #[Route('/', name: 'app_reclamations_index', methods: ['GET'])]
    public function index(Request $request, ReclamationsRepository $reclamationsRepository, PaginatorInterface $paginator): Response
    {
        // Get the search query from the request
        $searchQuery = $request->query->get('query');

        // Fetch all reclamations or search based on the query
        if ($searchQuery) {
            $reclamations = $reclamationsRepository->searchById($searchQuery);
        } else {
            $reclamations = $reclamationsRepository->findAll();
        }

        // Paginate the results
        $pagination = $paginator->paginate(
            $reclamations,
            $request->query->getInt('page', 1), // Current page number, default is 1
            10 // Number of items per page
        );

        // Render the view with the search results
        return $this->render('reclamations/index.html.twig', [
            'reclamations' => $pagination,
            'searchQuery' => $searchQuery, // Pass the search query to the view for display
        ]);
    }

    #[Route('/new', name: 'app_reclamations_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamations();
        $form = $this->createForm(ReclamationsType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nomProjet = $form->get('idProjet')->getData()->getNompr();
            $nomTypeRec = $form->get('idTypeReclamation')->getData()->getNomTypeReclamation();
            $nomUs = $form->get('idUtilisateur')->getData()->getNom();

            // Recherchez les objets correspondants dans la base de données
            $projet = $entityManager->getRepository(Projet::class)->findOneBy(['nompr' => $nomProjet]);
            $typeReclam = $entityManager->getRepository(Typesreclamation::class)->findOneBy(['nomTypeReclamation' => $nomTypeRec]);
            $utilis = $entityManager->getRepository(User::class)->findOneBy(['nom' => $nomUs]);

            // Vérifiez si toutes les données sont trouvées
            if ($projet && $typeReclam && $utilis) {
                // Affectez les objets à la réclamation
                $reclamation->setIdProjet($projet);
                $reclamation->setIdTypeReclamation($typeReclam);
                $reclamation->setIdUtilisateur($utilis);

                // Persistez la réclamation
                $entityManager->persist($reclamation);
                $entityManager->flush();

                // Redirection vers la page d'index des réclamations
                return $this->redirectToRoute('app_reclamations_index', [], Response::HTTP_SEE_OTHER);
            } else {
                // Gérer le cas où les données ne sont pas trouvées
                $this->addFlash('error', 'Certaines données associées à la réclamation n\'ont pas été trouvées.');
                // Vous pouvez rediriger vers une autre page ou afficher un message d'erreur dans le formulaire
            }
        }

        return $this->render('reclamations/new.html.twig', [
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
        return $this->render('reclamations/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{idReclamation}/edit', name: 'app_reclamations_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamations $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationsType::class, $reclamation);
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
        $html = $this->renderView('reclamations/pdf_export.html.twig', [
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
        $html = $this->renderView('reclamations/pdf_export.html.twig', [
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
        return $this->render('reclamations/index.html.twig', [
            'pagination'=>$pagination,
            'reclamations' => $pagination,
            'searchTerm' => $searchTerm
        ]);
    }

}
