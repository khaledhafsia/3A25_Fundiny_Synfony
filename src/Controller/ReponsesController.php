<?php

namespace App\Controller;

use App\Entity\Reponses;
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

#[Route('/reponses')]
class ReponsesController extends AbstractController
{
    #[Route('/', name: 'app_reponses_index', methods: ['GET'])]
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

    #[Route('/new', name: 'app_reponses_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reponse = new Reponses();
        $form = $this->createForm(ReponsesType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nomUs = $form->get('idUtilisateur')->getData()->getNom();

            // Recherchez les objets correspondants dans la base de données
            $utilis = $entityManager->getRepository(User::class)->findOneBy(['nom' => $nomUs]);
            if ($utilis) {
                // Affectez les objets à la réclamation

                $reponse->setIdUtilisateur($utilis);

                // Persistez la réclamation
                $entityManager->persist($reponse);
                $entityManager->flush();
                dump('bonjour');
                // Redirection vers la page d'index des réclamations
                return $this->redirectToRoute('app_reponses_index', [], Response::HTTP_SEE_OTHER);
            } else {
                // Gérer le cas où les données ne sont pas trouvées
                $this->addFlash('error', 'Certaines données associées aux reponses n\'ont pas été trouvées.');
                // Vous pouvez rediriger vers une autre page ou afficher un message d'erreur dans le formulaire
            }
        }

        return $this->renderForm('reponses/new.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/{idReponse}', name: 'app_reponses_show', methods: ['GET'])]
    public function show(Reponses $reponse): Response
    {
        return $this->render('reponses/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('/{idReponse}/edit', name: 'app_reponses_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponses $reponse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReponsesType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reponses_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reponses/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/{idReponse}', name: 'app_reponses_delete', methods: ['POST'])]
    public function delete(Request $request, Reponses $reponse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reponse->getIdReponse(), $request->request->get('_token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reponses_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export/excel', name: 'app_reponses_export_excel', methods: ['GET'])]
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
            $sheet->setCellValue('A' . $row, $reponse->getIdReponse());
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

