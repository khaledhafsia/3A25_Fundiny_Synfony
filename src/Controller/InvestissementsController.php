<?php

namespace App\Controller;

use App\Entity\Investissements;
use App\Form\InvestissementsType;
use App\Repository\InvestissementsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


//#[Route('/investissements')]
class InvestissementsController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function home(): Response
    {
        return $this->render('base.html.twig');
    }

    #[Route('/front/investissements', name: 'app_investissements_index', methods: ['GET'])]
    public function index(InvestissementsRepository $investissementsRepository, Request $request): Response
    {
        $investissements = $investissementsRepository->findAll();

        return $this->render('front/investissements/index.html.twig', [
            'investissements' => $investissements,
        ]);
    }
    
    #[Route('/front/investissements/search', name: 'app_investissements_search', methods: ['POST'])]
    public function search(Request $request, InvestissementsRepository $investissementsRepository): Response
    {
        $searchTerm = $request->request->get('search');
    
        // Fetch investissements based on the search term
        $investissements = $investissementsRepository->searchByDescription($searchTerm);
    
        return $this->render('front/investissements/index.html.twig', [
            'investissements' => $investissements,
        ]);
    }

    #[Route('/front/investissements/sort', name: 'app_investissements_sort', methods: ['GET'])]
    public function sort(Request $request, InvestissementsRepository $investissementsRepository): Response
    {
        $sortOrder = $request->query->get('sort');

        // Define the sorting criteria
        $orderBy = [];
        if ($sortOrder === 'asc') {
            $orderBy = ['montant' => 'ASC'];
        } elseif ($sortOrder === 'desc') {
            $orderBy = ['montant' => 'DESC'];
        }

        // Fetch sorted investissements from the repository
        $investissements = $investissementsRepository->findBy([], $orderBy);

        // Render the sorted data as HTML or return as JSON
        return $this->render('front/investissements/_table.html.twig', [
            'investissements' => $investissements,
        ]);
    }

    #[Route('/front/investissements/new', name: 'app_investissements_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $investissement = new Investissements();
        $form = $this->createForm(InvestissementsType::class, $investissement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($investissement);
            $entityManager->flush();

            return $this->redirectToRoute('app_investissements_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/investissements/new.html.twig', [
            'investissement' => $investissement,
            'form' => $form,
        ]);
    }

    #[Route('/front/investissements/{id}', name: 'app_investissements_show', methods: ['GET'])]
    public function show(Investissements $investissement): Response
    {
        return $this->render('front/investissements/show.html.twig', [
            'investissement' => $investissement,
        ]);
    }

    #[Route('/front/investissements/{id}/edit', name: 'app_investissements_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Investissements $investissement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InvestissementsType::class, $investissement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_investissements_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/investissements/edit.html.twig', [
            'investissement' => $investissement,
            'form' => $form,
        ]);
    }

    #[Route('/front/investissements/{id}', name: 'app_investissements_delete', methods: ['POST'])]
    public function delete(Request $request, Investissements $investissement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$investissement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($investissement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_investissements_index', [], Response::HTTP_SEE_OTHER);
    }
}
