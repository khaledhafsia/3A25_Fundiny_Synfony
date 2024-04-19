<?php

namespace App\Controller;

use App\Entity\Collaboration;
use App\Entity\Projet;
use App\Form\CollaborationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class CollaborationController extends AbstractController
{
    #[Route('/front/collaboration', name: 'app_collaboration_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $collaborations = $entityManager
            ->getRepository(Collaboration::class)
            ->findAll();

        return $this->render('collaboration/index.html.twig', [
            'collaborations' => $collaborations,
        ]);
    }

    #[Route('/front/collaboration/new', name: 'app_collaboration_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $collaboration = new Collaboration();
        $form = $this->createForm(CollaborationType::class, $collaboration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($collaboration);
            $entityManager->flush();

            return $this->redirectToRoute('app_collaboration_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('collaboration/new.html.twig', [
            'collaboration' => $collaboration,
            'form' => $form,
        ]);
    }

    #[Route('/front/collaboration/{id}', name: 'app_collaboration_show', methods: ['GET'])]
    public function show(Collaboration $collaboration): Response
    {
        return $this->render('collaboration/show.html.twig', [
            'collaboration' => $collaboration,
        ]);
    }

    #[Route('/front/collaboration/{id}/edit', name: 'app_collaboration_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Collaboration $collaboration, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CollaborationType::class, $collaboration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_collaboration_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('collaboration/edit.html.twig', [
            'collaboration' => $collaboration,
            'form' => $form,
        ]);
    }

    #[Route('/front/collaboration/{id}', name: 'app_collaboration_delete', methods: ['POST'])]
    public function delete(Request $request, Collaboration $collaboration, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$collaboration->getId(), $request->request->get('_token'))) {
            $entityManager->remove($collaboration);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_collaboration_index', [], Response::HTTP_SEE_OTHER);
    }


    /////////
    ////////
    /////////

    /**
 * @Route("/statistics", name="app_statistics")
 */
  public function statistics(EntityManagerInterface $entityManager): Response
  {
    $collaborationsByProjectId = $entityManager->createQueryBuilder()
    ->select('IDENTITY(c.idProjet) as idProjet, COUNT(c.id) as numCollaborations')
    ->from(Collaboration::class, 'c')
    ->groupBy('c.idProjet')
    ->getQuery()
    ->getResult();

    return $this->render('collaboration/statistic.html.twig', [
        'collaborationsByProjectId' => $collaborationsByProjectId,
    ]);
  }

  /**
 * @Route("/projet/{idProjet}", name="app_project_show", methods={"GET"})
 */
public function searchProjetById(int $idProjet, EntityManagerInterface $entityManager): Response
{
    $project = $entityManager->find(Projet::class, $idProjet);

    if (!$project) {
        throw $this->createNotFoundException('The project does not exist.');
    }

    return $this->render('project/show.html.twig', [
        'project' => $project,
    ]);
}

}
