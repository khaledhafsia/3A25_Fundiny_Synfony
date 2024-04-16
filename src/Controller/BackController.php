<?php

namespace App\Controller;

use App\Entity\Collaboration;
use App\Form\CollaborationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class BackController extends AbstractController
{
   
       

    #[Route('/back/collaboration', name: 'app_collaboration_indexb', methods: ['GET'])]
    public function indexb(EntityManagerInterface $entityManager): Response
    {
        $collaborations = $entityManager
            ->getRepository(Collaboration::class)
            ->findAll();

        return $this->render('collaboration/indexb.html.twig', [
            'collaborations' => $collaborations,
        ]);
    }

    #[Route('/back/collaboration/newb', name: 'app_collaboration_newb', methods: ['GET', 'POST'])]
    public function newb(Request $request, EntityManagerInterface $entityManager): Response
    {
        $collaboration = new Collaboration();
        $form = $this->createForm(CollaborationType::class, $collaboration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($collaboration);
            $entityManager->flush();

            return $this->redirectToRoute('app_collaboration_indexb', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('collaboration/newb.html.twig', [
            'collaboration' => $collaboration,
            'form' => $form,
        ]);
    }

    #[Route('/back/collaboration/{id}', name: 'app_collaboration_showb', methods: ['GET'])]
    public function showb(Collaboration $collaboration): Response
    {
        return $this->render('collaboration/showb.html.twig', [
            'collaboration' => $collaboration,
        ]);
    }

    #[Route('/back/collaboration/{id}/editb', name: 'app_collaboration_editb', methods: ['GET', 'POST'])]
    public function editb(Request $request, Collaboration $collaboration, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CollaborationType::class, $collaboration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_collaboration_indexb', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('collaboration/editb.html.twig', [
            'collaboration' => $collaboration,
            'form' => $form,
        ]);
    }

    #[Route('/back/collaboration/{id}', name: 'app_collaboration_deleteb', methods: ['POST'])]
    public function deleteb(Request $request, Collaboration $collaboration, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$collaboration->getId(), $request->request->get('_token'))) {
            $entityManager->remove($collaboration);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_collaboration_index', [], Response::HTTP_SEE_OTHER);
    }
 }

