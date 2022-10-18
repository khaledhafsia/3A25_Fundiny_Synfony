<?php

namespace App\Controller;

use App\Entity\Student;
use App\Form\StudentType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Repository\ClubRepository;
use App\Repository\StudentRepository;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

class StudentController extends AbstractController
{
    #[Route('/student', name: 'app_student')]
    public function index(): Response
    {
        return $this->render('student/index.html.twig', [
            'controller_name' => 'StudentController',
        ]);
 
    }


    #[Route('/listStudent', name:'listStudent')]
    public function listStudent(StudentRepository $repo):Response{
        $students=$repo->findAll();
        return $this->render('student/listStudent.html.twig', [ 'students' =>$students

        ]);
    }

    #[Route('/showStudent/{numinscri}',name: "studentDetails")]
    public function show3(Student $student):Response{
        return $this->render('student/showStudent.html.twig', [ 'student' =>$student

    ]);
    }

    #[Route('/deleteStudent/{numinscri}',name: "studentDelete")]
    public function delete(ManagerRegistry $doctrine,$numinscri):Response{
        $student=$doctrine->getRepository(Student::class)->find($numinscri);
        $EntityManager=$doctrine->getManager();
        $EntityManager->remove($student);
        $EntityManager->flush();
        return new Response ('Delete');
    }

    #[Route('/deleteStudent1/{numinscri}',name: "studentDelete1")]
    public function delete1(StudentRepository $repo,$numinscri):Response{
        $student=$repo->find($numinscri);
        $repo->remove($student,true);
        return new Response ('Delete111');
    }

    #[Route('/add3',name: "studentAdd2")]
    public function add3(Request $request, ManagerRegistry $doctrine):Response{
        $student=new Student();
        //$club->setNom('test');
        $form=$this->createFormBuilder($student)
        ->add('numinscri')
        ->add('nom',TextType::class)
        ->add('prenom',TextType::class)
        ->add('save',SubmitType::class)
        ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $club=$form->getData();
            $EntityManager=$doctrine->getManager();
            $EntityManager->persist($student);
            $EntityManager->flush();
            
            return $this->redirectToRoute('listStudent');
        }

        return $this->renderForm('student/add.html.twig',[ //render mouch renderForm
            'form' =>$form //->createView() 
        ]);
        
       
    }



    #[Route('/addStudent',name: "studentAdd")]
    public function add2(Request $request, StudentRepository $repo):Response{
        $student=new Student();
        //$club->setNom('test');
       // $form=$this->createFormBuilder($club)
       // ->add('nom',TextType::class)
       // ->add('save',SubmitType::class)
       // ->getForm();
$form=$this->createForm(StudentType::class,$student);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $student=$form->getData();
           $repo->add($student,true);
            
            return $this->redirectToRoute('listStudent');
        }

        return $this->renderForm('student/add.html.twig',[ //render mouch renderForm
            'form' =>$form //->createView() 
        ]);
        
       
    }


}


