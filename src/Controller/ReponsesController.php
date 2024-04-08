<?php

namespace App\Controller;

use App\Entity\Reponses;
use App\Entity\User;
use App\Form\ReponsesType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reponses')]
class ReponsesController extends AbstractController
{
    #[Route('/', name: 'app_reponses_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $reponses = $entityManager
            ->getRepository(Reponses::class)
            ->findAll();

        return $this->render('reponses/index.html.twig', [
            'reponses' => $reponses,
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
        if ($this->isCsrfTokenValid('delete'.$reponse->getIdReponse(), $request->request->get('_token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reponses_index', [], Response::HTTP_SEE_OTHER);
    }
}
