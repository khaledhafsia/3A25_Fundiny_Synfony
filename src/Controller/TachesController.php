<?php

namespace App\Controller;

use App\Entity\Investissements;
use App\Entity\Taches;
use App\Form\TachesType;
use App\Repository\TachesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


//#[Route('/taches')]
class TachesController extends AbstractController
{
    #[Route('/front/taches', name: 'app_taches_index', methods: ['GET'])]
    public function index(TachesRepository $tachesRepository): Response


    {
        return $this->render('front/taches/index.html.twig', [
            'taches' => $tachesRepository->findAll(),
        ]);
    }

    #[Route('/front/{id}/taches', name: 'app_taches_index2', methods: ['GET'])]
    public function index2(TachesRepository $tachesRepository, $id): Response
    {
        // Fetch the Investissement entity based on the provided ID
        $investissement = $this->getDoctrine()->getRepository(Investissements::class)->find($id);

        // If the investissement is not found, you might want to handle this case appropriately (e.g., show an error message)
        if (!$investissement) {
            throw $this->createNotFoundException('Investissement not found');
        }

        // Fetch the tasks associated with the Investissement ID
        $taches = $tachesRepository->findBy(['invid' => $investissement]);

        return $this->render('front/taches/index.html.twig', [
            'taches' => $taches,

            'investissement' => $investissement,
        ]);
    }

    #[Route('/front/{id}/taches/search', name: 'app_taches_search', methods: ['POST'])]
    public function search(Request $request, TachesRepository $tachesRepository, $id): Response
    {
        // Fetch the Investissement entity based on the provided ID
        $investissement = $this->getDoctrine()->getRepository(Investissements::class)->find($id);

        

        // If the investissement is not found, you might want to handle this case appropriately (e.g., show an error message)
        if (!$investissement) {
            throw $this->createNotFoundException('Investissement not found');
        }

        $searchTerm = $request->request->get('search');

        // Fetch tasks associated with the Investissement ID based on the search term
        $taches = $tachesRepository->searchByTitre($investissement, $searchTerm);

        return $this->render('front/taches/index.html.twig', [
            'taches' => $taches,
            'investissement' => $investissement,
            
        ]);
    }

    #[Route('/front/{id}/taches/sort', name: 'app_taches_sort', methods: ['POST'])]
    public function sortTaches(Request $request, TachesRepository $tachesRepository, $id): Response
    {
        $investissement = $this->getDoctrine()->getRepository(Investissements::class)->find($id);

        if (!$investissement) {
            throw $this->createNotFoundException('Investissement not found');
        }

        $sortBy = $request->request->get('sort');

        if ($sortBy === 'priority') {
            $taches = $tachesRepository->findBy(['invid' => $investissement], ['priorite' => 'ASC']);
            usort($taches, function($a, $b) {
                $order = ['élevée', 'moyenne', 'faible'];
                return array_search($a->getPriorite(), $order) - array_search($b->getPriorite(), $order);
            });
        } elseif ($sortBy === 'status') {
            $taches = $tachesRepository->findBy(['invid' => $investissement], ['statut' => 'ASC']);
        } else {
            // Default sorting or error handling
            $taches = $tachesRepository->findBy(['invid' => $investissement]);
        }

        return $this->render('front/taches/_taches_table.html.twig', [
            'taches' => $taches,
        ]);
    }



    #[Route('/front/taches/new', name: 'app_taches_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tach = new Taches();
        $form = $this->createForm(TachesType::class, $tach);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tach);
            $entityManager->flush();

            return $this->redirectToRoute('app_taches_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/taches/new.html.twig', [
            'tach' => $tach,
            'form' => $form,
        ]);
    }

    #[Route('/front/taches/new/{id}', name: 'app_taches_new2', methods: ['GET', 'POST'])]
    public function new2(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        // Fetch the Investissement entity based on the provided ID
        $investissement = $entityManager->getRepository(Investissements::class)->find($id);

        // Create a new Taches instance
        $tach = new Taches();

        // Set the invID attribute of the Taches entity with the ID of the Investissement
        $tach->setInvid($investissement);

        // Create the Taches form
        $form = $this->createForm(TachesType::class, $tach);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tach);
            $entityManager->flush();

            return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/taches/new.html.twig', [
            'tach' => $tach,
            'form' => $form,
        ]);
    }

    #[Route('/front/taches/{id}', name: 'app_taches_show', methods: ['GET'])]
    public function show(Taches $tach): Response
    {
        return $this->render('front/taches/show.html.twig', [
            'tach' => $tach,
        ]);
    }


    #[Route('/front/taches/{id}/edit', name: 'app_taches_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Taches $tach, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TachesType::class, $tach);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/taches/edit.html.twig', [
            'tach' => $tach,
            'form' => $form,
        ]);
    }

    #[Route('/front/taches/{id}', name: 'app_taches_delete', methods: ['POST'])]
    public function delete(Request $request, Taches $tach, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tach->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tach);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/back/{id}/taches', name: 'app_tachesBack_index2', methods: ['GET'])]
    public function index2Back(TachesRepository $tachesRepository, $id): Response
    {
        // Fetch the Investissement entity based on the provided ID
        $investissement = $this->getDoctrine()->getRepository(Investissements::class)->find($id);

        // If the investissement is not found, you might want to handle this case appropriately (e.g., show an error message)
        if (!$investissement) {
            throw $this->createNotFoundException('Investissement not found');
        }

        // Fetch the tasks associated with the Investissement ID
        $taches = $tachesRepository->findBy(['invid' => $investissement]);

        return $this->render('back/taches/index.html.twig', [
            'taches' => $taches,
            'investissement' => $investissement,
        ]);
    }

    #[Route('/back/taches/{id}', name: 'app_tachesBack_show', methods: ['GET'])]
    public function showBack(Taches $tach): Response
    {
        return $this->render('back/taches/show.html.twig', [
            'tach' => $tach,
        ]);
    }

    #[Route('/back/taches/{id}/edit', name: 'app_tachesBack_edit', methods: ['GET', 'POST'])]
    public function editBack(Request $request, Taches $tach, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TachesType::class, $tach);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_investissementsBack_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/taches/edit.html.twig', [
            'tach' => $tach,
            'form' => $form,
        ]);
    }

    #[Route('/back/taches/{id}', name: 'app_tachesBack_delete', methods: ['POST'])]
    public function deleteBack(Request $request, Taches $tach, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tach->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tach);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_investissementsBack_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/back/taches/new/{id}', name: 'app_tachesBack_new2', methods: ['GET', 'POST'])]
    public function new2Back(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        // Fetch the Investissement entity based on the provided ID
        $investissement = $entityManager->getRepository(Investissements::class)->find($id);

        // Create a new Taches instance
        $tach = new Taches();

        // Set the invID attribute of the Taches entity with the ID of the Investissement
        $tach->setInvid($investissement);

        // Create the Taches form
        $form = $this->createForm(TachesType::class, $tach);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tach);
            $entityManager->flush();

            return $this->redirectToRoute('app_investissementsBack_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/taches/new.html.twig', [
            'tach' => $tach,
            'form' => $form,
        ]);
    }

}
