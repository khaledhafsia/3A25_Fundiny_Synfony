<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Investissements;
use App\Form\EventsType;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


class EventsController extends AbstractController
{

    #[Route('/home', name: 'home')]
    public function home(): Response
    {
        
        return $this->render('base.html.twig');


    }

    #[Route('/back', name: 'back')]


    public function back(): Response
    {
        return $this->render('baseBack.html.twig');
    }

    #[Route('/front/events', name: 'app_events_index', methods: ['GET'])]
    public function index(EventsRepository $eventsRepository, Request $request): Response
    {

        $events = $eventsRepository->findAll();

        return $this->render('front/events/index.html.twig', [
            'events' => $events,
        ]);
    }
    
    #[Route('/front/events/search', name: 'app_events_search', methods: ['POST'])]
    public function search(Request $request, EventsRepository $eventsRepository): Response
    {
        $searchTerm = $request->request->get('search');
    
        // Fetch evenements based on the search term
        $events = $eventsRepository->searchByDescription($searchTerm);
    
        return $this->render('front/investissements/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/front/events/sort', name: 'app_events_sort', methods: ['GET'])]
    public function sort(Request $request, EventsRepository $eventsRepository): Response
    {
        $sortOrder = $request->query->get('sort');

        // Define the sorting criteria
        $orderBy = [];
        if ($sortOrder === 'asc') {
            $orderBy = ['description' => 'ASC'];
        } elseif ($sortOrder === 'desc') {
            $orderBy = ['description' => 'DESC'];
        }

        // Fetch sorted evenements from the repository
        $events = $eventsRepository->findBy([], $orderBy);

        // Render the sorted data as HTML or return as JSON
        return $this->render('front/events/_table.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/front/events/new', name: 'app_events_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evenement = new Evenement();
        
        $form = $this->createForm(EventsType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($evenement);
            $entityManager->flush();

            return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/events/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form, 
        ]);
    }

    #[Route('/front/events/{id}', name: 'app_events_show', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('front/events/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/front/events/{id}/edit', name: 'app_events_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/events/edit.html.twig', [
            'evenement' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/front/events/{id}', name: 'app_events_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/back/investissements', name: 'app_investissementsBack_index', methods: ['GET'])]
    public function indexBack(EventsRepository $eventsRepository, Request $request): Response
    {
        $investissements = $eventsRepository->findAll();

        return $this->render('back/investissements/index.html.twig', [
            'investissements' => $investissements,
        ]);
    }

    #[Route('/back/investissements/new', name: 'app_investissementsBack_new', methods: ['GET', 'POST'])]
    public function newBack(Request $request, EntityManagerInterface $entityManager): Response
    {
        $investissement = new Investissements();
        $form = $this->createForm(EventsType::class, $investissement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($investissement);
            $entityManager->flush();

            return $this->redirectToRoute('app_investissementsBack_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/investissements/new.html.twig', [
            'investissement' => $investissement,
            'form' => $form,
        ]);
    }

    #[Route('/back/investissements/{id}/edit', name: 'app_investissementsBack_edit', methods: ['GET', 'POST'])]
    public function editBack(Request $request, Investissements $investissement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventsType::class, $investissement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_investissementsBack_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/investissements/edit.html.twig', [
            'investissement' => $investissement,
            'form' => $form,
        ]);
    }

    #[Route('/back/investissements/{id}', name: 'app_investissementsBack_delete', methods: ['POST'])]
    public function deleteBack(Request $request, Investissements $investissement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$investissement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($investissement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_investissementsBack_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/back/investissements/{id}', name: 'app_investissementsBack_show', methods: ['GET'])]
    public function showBack(Investissements $investissement): Response
    {
        return $this->render('back/investissements/show.html.twig', [
            'investissement' => $investissement,
        ]);
    }

    #[Route('/back/investissements/sort', name: 'app_investissements2_sort', methods: ['GET'])]
    public function sortBack(Request $request, EventsRepository $eventsRepository): Response
    {
        $sortOrder = $request->query->get('sort');

        // Define the sorting criteria
        $orderBy = [];
        if ($sortOrder === 'asc') {
            $orderBy = ['nom' => 'ASC'];
        } elseif ($sortOrder === 'desc') {
            $orderBy = ['nom' => 'DESC'];
        }

        // Fetch sorted investissements from the repository
        $investissements = $eventsRepository->findBy([], $orderBy);

        // Render the sorted data as HTML or return as JSON
        return $this->render('back/investissements/_table.html.twig', [
            'investissements' => $investissements,
        ]);
    }
    

}
