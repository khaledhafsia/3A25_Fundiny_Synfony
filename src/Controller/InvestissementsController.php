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

//#[Route('/investissements')]
class InvestissementsController extends AbstractController
{
    #[Route('/front/investissements', name: 'app_investissements_index', methods: ['GET'])]
    public function index(InvestissementsRepository $investissementsRepository): Response
    {
        return $this->render('front/investissements/index.html.twig', [
            'investissements' => $investissementsRepository->findAll(),
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
