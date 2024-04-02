<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\Reclamations;
use App\Entity\Typesreclamation;
use App\Entity\User;
use App\Form\ReclamationsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ReclamationsRepository;

#[Route('/reclamations')]
class ReclamationsController extends AbstractController
{
    #[Route('/', name: 'app_reclamations_index', methods: ['GET'])]
    public function index(ReclamationsRepository $reclamationsRepository): Response
    {

        $reclamations = $reclamationsRepository->findBy([], ['idReclamation' => 'DESC']);

        return $this->render('reclamations/index.html.twig', [
            'reclamations' => $reclamations,
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
                dump('bonjour');
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
    public function show(Reclamations $reclamation): Response
    {
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
        if ($this->isCsrfTokenValid('delete'.$reclamation->getIdReclamation(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reclamations_index', [], Response::HTTP_SEE_OTHER);
    }
}
