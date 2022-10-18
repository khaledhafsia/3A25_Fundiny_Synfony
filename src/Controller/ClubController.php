<?php

namespace App\Controller;
use App\Entity\Club;
use App\Form\ClubType;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use SebastianBergmann\CodeCoverage\Report\Html\Renderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClubController extends AbstractController
{

    #[Route('/club', name: 'app_club')]
    public function index(): Response
    {
        return $this->render('club/index.html.twig', [
            'controller_name' => 'ClubController',
        ]);
    }


    #[Route('/club/getName/{name}', name: 'getName')]
    public function getName($name): Response
    {
        return $this->render('club/detail.html.twig', ['nom' => $name]);
    }
    #[Route('/List', name: 'listformation')]
    public function List(): Response
    {
        $var1= "3A51";
        $var2= "I12";
        $formations = array(
            array('ref' => 'form147', 'Titre' => 'Formation Symfony
4', 'Description' => 'formation theorique',
                'date_debut' => '12/06/2020', 'date_fin' => '19/06/2020',
                'nb_participants' => 19),
            array('ref' => 'form177', 'Titre' => 'Formation SOA',
                'Description' => 'formation theorique', 'date_debut' => '03/12/2020', 'date_fin' => '10/12/2020',
                'nb_participants' => 0),
            array('ref' => 'form178', 'Titre' => 'Formation Angular',
                'Description' => 'formation pratique', 'date_debut' => '10/06/2020', 'date_fin' => '14/06/2020',
                'nb_participants' => 22));
        return $this->render("club/list.html.twig",
            array("x"=>$var1,"y"=>$var2,"tabFormation"=>$formations));
    }
    #[Route('/getName/{id}', name: 'detail')]
    public function Detail($id)
    {
        return $this->render("club/detail.html.twig",
        array("ref"=>$id));
    }
#meth1
#[Route('/listClub', name:'listClub')]
    public function listClub(ManagerRegistry $doctrine):Response{
        $clubs=$doctrine->getRepository(Club::class)->findAll();
        return $this->render('club/listClub.html.twig', [ 'clubs' =>$clubs

        ]);
    }
#meth2
#[Route('/listClub2', name:'listClub2')]
    public function listClub2(ClubRepository $repo):Response{
        $clubs=$repo->findAll();
        return $this->render('club/listClub.html.twig', [ 'clubs' =>$clubs

        ]);
    }
#[Route('/show/{id}',name: "clubDetails")]
    public function show(ManagerRegistry $doctrine, $id):Response{
        $club=$doctrine->getRepository(Club::class)->find($id);
        return $this->render('club/showClub.html.twig', [ 'club' =>$club

    ]);
    }

    #[Route('/show1/{id}',name: "clubDetails1")]
    public function show1(ClubRepository $repo, $id):Response{
        $club=$repo->find($id);
        return $this->render('club/showClub.html.twig', [ 'club' =>$club

    ]);
    }
    #[Route('/show2/{id}',name: "clubDetails2")]
    public function show2(Club $club):Response{
        return $this->render('club/showClub.html.twig', [ 'club' =>$club

    ]);
    }
    #[Route('/delete/{id}',name: "clubDelete")]
    public function delete(ManagerRegistry $doctrine,$id):Response{
        $club=$doctrine->getRepository(Club::class)->find($id);
        $EntityManager=$doctrine->getManager();
        $EntityManager->remove($club);
        $EntityManager->flush();
        return new Response ('Delete');
    }

    #[Route('/delete1/{id}',name: "clubDelete1")]
    public function delete1(ClubRepository $repo,$id):Response{
        $club=$repo->find($id);
        $repo->remove($club,true);
        return new Response ('Delete1');
    }

    #[Route('/add',name: "clubAdd")]
    public function add(Request $request, ManagerRegistry $doctrine):Response{
        $club=new Club();
        //$club->setNom('test');
        $form=$this->createFormBuilder($club)
        ->add('nom',TextType::class)
        ->add('save',SubmitType::class)
        ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $club=$form->getData();
            $EntityManager=$doctrine->getManager();
            $EntityManager->persist($club);
            $EntityManager->flush();
            
            return $this->redirectToRoute('listClub');
        }

        return $this->renderForm('club/add.html.twig',[ //render mouch renderForm
            'form' =>$form //->createView() 
        ]);
        
       
    }

    #[Route('/add1',name: "clubAdd1")]
    public function add1(Request $request, ClubRepository $repo):Response{
        $club=new Club();
        //$club->setNom('test');
       // $form=$this->createFormBuilder($club)
       // ->add('nom',TextType::class)
       // ->add('save',SubmitType::class)
       // ->getForm();
$form=$this->createForm(ClubType::class,$club);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $club=$form->getData();
           $repo->add($club,true);
            
            return $this->redirectToRoute('listClub');
        }

        return $this->renderForm('club/add.html.twig',[ //render mouch renderForm
            'form' =>$form //->createView() 
        ]);
        
       
    }




}
