<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\User;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;



class ProjetController extends AbstractController
{
    #[Route('/front/Projet', name: 'app_projet_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $projets = $entityManager
            ->getRepository(Projet::class)
            ->findAll();

        return $this->render('front/projet/index.html.twig', [
            'projets' => $projets,
        ]);
    }

    #[Route('/front/Projet2', name: 'app_projet_index2', methods: ['GET'])]
    public function index2(ProjetRepository $projetRepository, SessionInterface $session): Response
    {
        $User = $session->get('user_id');
        $projet = $projetRepository->findByUserId($User);

        return $this->render('front/projet/index.html.twig', [
            'projets' => $projet,
        ]);
    }


    #[Route('/front/Projet/new', name: 'app_projet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $Userid = $session->get('user_id');
        $User = $entityManager->getRepository(User::class)->find($Userid);


        $projet = new Projet();
        $projet->setUser($User);

        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($projet);
            $entityManager->flush();

            return $this->redirectToRoute('app_projet_index2', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/projet/new.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }

    #[Route('/front/Projet/{id}', name: 'app_projet_show', methods: ['GET'])]
#[ParamConverter('projet', class: Projet::class, options: ['id' => 'id'])]
public function show(Request $request, EntityManagerInterface $entityManager): Response
{
    $id = $request->get('id');
    $projet = $entityManager->getRepository(Projet::class)->find($id);

    if (!$projet) {
        throw $this->createNotFoundException(
            'No project found for id '.$id
        );
    }
    return $this->render('front/projet/show.html.twig', [
        'projet' => $projet,
    ]);
}


    #[Route('/front/Projet/{id}/edit', name: 'app_projet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_projet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/projet/edit.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }

    #[Route('/front/Projet/{id}', name: 'app_projet_delete', methods: ['POST'])]
    public function delete(Request $request, Projet $projet, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$projet->getId(), $request->request->get('_token'))) {
            $entityManager->remove($projet);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_projet_index', [], Response::HTTP_SEE_OTHER);
    }//l'exception ici
    
    #[Route('/front/projet/export', name: 'app_projet_export', methods: ['GET'])]
    public function export(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): Response
    {
        $projets = $entityManager->getRepository(Projet::class)->findAll();
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Id');
        $sheet->setCellValue('B1', 'Nompr');
        $sheet->setCellValue('C1', 'Nompo');
        $sheet->setCellValue('D1', 'Dated');
        $sheet->setCellValue('E1', 'Ca');
    
        $row = 2;
        foreach ($projets as $projet) {
            $sheet->setCellValue('A'. $row, $projet->getId());
            $sheet->setCellValue('B'. $row, $projet->getNompr());
            $sheet->setCellValue('C'. $row, $projet->getNompo());
            $sheet->setCellValue('D'. $row, $projet->getDated()->format('Y-m-d'));
            $sheet->setCellValue('E'. $row, $projet->getCa());
            $row++;
        }
    
        $writer = new Xlsx($spreadsheet);
        $filename = 'export_projet.xlsx';
        $writer->save($filename);
    
        $response = new Response(file_get_contents($filename));
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="'. $filename. '"');
        $response->headers->set('Cache-Control', 'max-age=0');
    
        return $response->send();
        
        $projectId = 1; // Replace this with the actual project ID
        $showUrl = $urlGenerator->generate('app_projet_show', ['id' => $projectId]);
    
        // Redirect to the show route
        return $this->redirect($showUrl);
    }
   // Controller action for handling the search
    public function searchAction(Request $request)
    {
     $nomPr = $request->query->get('nomPr');
    
     // Recherchez les projets en fonction de $nomPr
     $entityManager = $this->getDoctrine()->getManager();
     $projets = $entityManager->getRepository(Projet::class)->findBy(['nomPr' => $nomPr]);
    
     return $this->render('votre_template.html.twig', [
        'projets' => $projets,
        'nomPr' => $nomPr
    ]);
   }

   #[Route('/front/projet/export2', name: 'app_projet_export2', methods: ['GET'])]
    public function export2(EntityManagerInterface $entityManager,ProjetRepository $ProjetRepository, UrlGeneratorInterface $urlGenerator, SessionInterface $session): Response
    {
        $userId = $session->get('user_id');
        $projets = $ProjetRepository->findByUserId($userId);
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Id');
        $sheet->setCellValue('B1', 'Nompr');
        $sheet->setCellValue('C1', 'Nompo');
        $sheet->setCellValue('D1', 'Dated');
        $sheet->setCellValue('E1', 'Ca');
    
        $row = 2;
        foreach ($projets as $projet) {
            $sheet->setCellValue('A'. $row, $projet->getId());
            $sheet->setCellValue('B'. $row, $projet->getNompr());
            $sheet->setCellValue('C'. $row, $projet->getNompo());
            $sheet->setCellValue('D'. $row, $projet->getDated()->format('Y-m-d'));
            $sheet->setCellValue('E'. $row, $projet->getCa());
            $row++;
        }
    
        $writer = new Xlsx($spreadsheet);
        $filename = 'export_projet.xlsx';
        $writer->save($filename);
    
        $response = new Response(file_get_contents($filename));
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="'. $filename. '"');
        $response->headers->set('Cache-Control', 'max-age=0');
    
        return $response->send();
        
        $projectId = 1; // Replace this with the actual project ID
        $showUrl = $urlGenerator->generate('app_projet_show', ['id' => $projectId]);
    
        // Redirect to the show route
        return $this->redirect($showUrl);
    }
   

    
    

}
                                                                                                                                                                                                                                                                                                                                                                                                                                        