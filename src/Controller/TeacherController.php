<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeacherController extends AbstractController
{
    #[Route('/teacher/{classe}', name: 'app_teacher')]
    public function index($classe ='3A51'): Response
    {
        return $this->render('teacher/index.html.twig', [
            'controller_name' => $classe,
        ]);
        
    }

}
