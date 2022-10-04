<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ClubController extends AbstractController
{
    /**
     * @Route("/club", name="club")
     */
    public function index()
    {
        return $this->render('club/index.html.twig', [
            'controller_name' => 'ClubController',
        ]);
    }


    /**
     * @Route("/affiche/{name}", name="getname")
     */
    public function getName($name)
    {
        return $this->render('club/affiche2.html.twig', [
            'controller_name' => 'ClubController','nom'=>$name
        ]);
    }


    /**
     * @Route("/exercice2", name="ex1")
     */
    public function Affiche2()
    {$listTableau = array(
        array('ref' => 'form147', 'Titre' => 'Formation Symfony 4','Description'=>'formation pratique',
            'date_debut'=>'12/06/2020', 'date_fin'=>'19/06/2020', 'nb_participants'=>19) ,
        array('ref'=>'form177','Titre'=>'Formation SOA' ,
            'Description'=>'formation theorique','date_debut'=>'03/12/2020','date_fin'=>'10/12/2020',
            'nb_participants'=>0),
        array('ref'=>'form178','Titre'=>'Formation Angular' ,
            'Description'=>'formation theorique','date_debut'=>'10/06/2020','date_fin'=>'14/06/2020',
            'nb_participants'=>12)

    );
        // var_dump($listTableau);


        return $this->render('club/affiche.html.twig',[
            'controller_name' => 'ClubController', 'listeTableau'=> $listTableau
        ]);
    }

    /**
     * @Route("/detail/{id}", name="detail")
     */
    public function detail($id)
    {return $this->render('club/detail.html.twig', [
        'controller_name' => 'ClubController','nom'=>$id
    ]);
    }



}
