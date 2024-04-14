<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    private RequestStack $requestStack;
    private LoggerInterface $logger;

    public function __construct(RequestStack $requestStack, LoggerInterface $logger)
    {
        $this->requestStack = $requestStack;
        $this->logger = $logger;
    }

    #[Route('/loginn', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $request = $this->requestStack->getCurrentRequest();

        $loginForm = $this->createForm(LoginType::class);
        $loginForm->handleRequest($request, $authenticationUtils);

        if ($request->isMethod('POST')) {
            $formData = $loginForm->getData();

            // Log the email and password entered by the user
            $email = $formData->getEmail();
            $password = $formData->getPassword();
            $this->logger->info('Email: ' . $email);
            $this->logger->info('Password: ' . $password);

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $formData->getEmail()]);

            // Log the fetched user data
            $this->logger->info('Fetched User Data: ' . json_encode($user));

            if (!$user) {
                $this->addFlash('error', 'Account does not exist.');
            //} else if (!$passwordHasher->isPasswordValid($user, $password)) {
            //    $this->addFlash('error', 'Invalid password.');
            } else {
                // Store user in session
                $this->setSessionUser($user);

                switch ($user->getRole()) {
                    case 'Admin':
                        return $this->redirectToRoute('dashboard');
                    case 'Funder':
                        return $this->redirectToRoute('dashboardFunder');
                    case 'Owner':
                        return $this->redirectToRoute('dashboardOwner');
                    default:
                        // Handle other roles if needed
                        return $this->redirectToRoute('dashboard');
                }
            }
        }

        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('login/login.twig', [
            'form' => $loginForm->createView(),
            'error' => $error,
        ]);
    }


    private function setSessionUser(User $user): void
    {
        // Inject the request object through dependency injection
        $request = $this->requestStack->getCurrentRequest();
        $request->getSession()->set('user', $user);
    }

    private function getSessionUser(): ?User
    {
        // Inject the request object through dependency injection
        $request = $this->requestStack->getCurrentRequest();
        return $request->getSession()->get('user');
    }

    #[Route('/dashboardFunder', name: 'dashboardFunder')]
    public function dashboardFunder(): Response
    {
        $user = $this->getSessionUser();

        if (!$user) {
            return $this->redirectToRoute('loginn');
        }

        return $this->render('Dashboard/dashboardFunder.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/dashboardOwner', name: 'dashboardOwner')]
    public function dashboardOwer(): Response
    {
        $user = $this->getSessionUser();
        if (!$user) {
            return $this->redirectToRoute('loginn');
        }

        return $this->render('Dashboard/dashboardOwner.twig', [
            'user' => $user,
        ]);

    }
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('Dashboard/dashboard.twig');
    }


}
