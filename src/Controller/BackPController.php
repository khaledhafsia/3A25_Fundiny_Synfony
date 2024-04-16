<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Form\ProjetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackPController extends AbstractController
{
    #[Route('/back/Projet', name: 'app_projet_indexb', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $projets = $entityManager
            ->getRepository(Projet::class)
            ->findAll();

        return $this->render('projet/indexb.html.twig', [
            'projets' => $projets,
        ]);
    }

    #[Route('/back/Projet/newb', name: 'app_projet_newb', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projet);
            $entityManager->flush();

            return $this->redirectToRoute('app_projet_indexb', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('projet/newb.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }

    #[Route('/back/Projet/{id}', name: 'app_projet_showb', methods: ['GET'])]
    public function show(Projet $projet): Response
    {
        return $this->render('projet/showb.html.twig', [
            'projet' => $projet,
        ]);
    }

    #[Route('/back/Projet/{id}/editb', name: 'app_projet_editb', methods: ['GET', 'POST'])]
    public function edit(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_projet_indexb', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('projet/editb.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }

    #[Route('/back/Projet/{id}', name: 'app_projet_delete', methods: ['POST'])]
    public function delete(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete'.$projet->getId(), $request->request->get('_token'))) {
                $entityManager->remove($projet);
                $entityManager->flush();
            }
    
            return $this->redirectToRoute('app_projet_index', [], Response::HTTP_SEE_OTHER);
        } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {
            $this->addFlash('danger', 'Cannot delete or update a parent row: a foreign key constraint fails');
            return $this->redirectToRoute('app_projet_index', [], Response::HTTP_SEE_OTHER);
        }
    }
}
