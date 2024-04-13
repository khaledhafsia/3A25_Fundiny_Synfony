<?php


namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{


    #[Route('/loginn', name: 'login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        // Get the login form
        $loginForm = $this->createForm(LoginType::class);

        // Check for submitted form
        if ($request->isMethod('POST')) {
            $loginForm->handleRequest($request);

            // Get submitted data
            $formData = $loginForm->getData();

            // Find user by email
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $formData->getEmail()]);

            // Check if user exists
            if (!$user) {
                // Display "Account doesn't exist" message (consider using a flash message)
                $this->addFlash('error', 'Account does not exist.');
                return $this->redirectToRoute('login');
            }

            // Validate password using password encoder
            if (!$passwordEncoder->isPasswordValid($user, $formData->getPassword())) {
                // Display "Wrong password" message (consider using a flash message)
                $this->addFlash('error', 'Wrong password.');
                return $this->redirectToRoute('login');
            } else {
                // Authentication successful (handled by Security component)
                return $this->redirectToRoute('dashboard'); // Replace with your actual route
            }
        }
        // Render login form
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('login/login.twig', [
            'form' => $loginForm->createView(),
            'error' => $error,
        ]);
    }


    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('Dashboard/dashboard.html.twig');
    }


}