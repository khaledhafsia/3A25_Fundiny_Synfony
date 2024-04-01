<?php


namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/Login', name: 'Login')]
    public function login(Request $request): Response
    {
        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $email = $user->getEmail();
            $password = $user->getPassword();

            $userRepository = $this->getDoctrine()->getRepository(User::class);
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user && password_verify($password, $user->getPassword())) {
                return $this->redirectToRoute('dashboard');
            } else {
                $this->addFlash('error', 'Invalid email or password.');
                return $this->render('login/login.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
        }

        return $this->render('login/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('Dashboard/dashboard.html.twig');
    }
}
