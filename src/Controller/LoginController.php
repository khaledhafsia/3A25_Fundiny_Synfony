<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\UpdatePasswordType;
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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

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
        $loginForm = $this->createForm(LoginType::class, null, [
            'reset_password_route' => $this->generateUrl('password_reset'),
        ]);
        //$loginForm = $this->createForm(LoginType::class);
        $loginForm->handleRequest($request, $authenticationUtils);

        if ($request->isMethod('POST')) {
            $formData = $loginForm->getData();

            // Log the email and password entered by the user
            $email = $formData->getEmail();
            $password = $formData->getPassword();
            $this->logger->info('Email: ' . $email);
            $this->logger->info('Password: ' . $password);

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $formData->getEmail()]);

            $this->setSessionUserEmail($email);

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
        if ($request->query->get('password_reset')) {
            return $this->redirectToRoute('password_reset');
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

    private function setSessionUserEmail(string $email): void
    {
        // Inject the request object through dependency injection
        $request = $this->requestStack->getCurrentRequest();
        $request->getSession()->set('user_email', $email);
    }
    private function getSessionUserEmail(): ?string
    {
        // Inject the request object through dependency injection
        $request = $this->requestStack->getCurrentRequest();
        return $request->getSession()->get('user_email');
    }
    #[Route('/password_reset', name: 'password_reset')]
    public function passwordReset(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {

        $email = $this->getSessionUserEmail();

    if ($email === null) {
        $this->addFlash('error', 'Email is required.');
        return $this->redirectToRoute('login');
    }

    $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);


        if ($user) {
            $token = uniqid();

            $user->setResetToken($token);
            $user->setTokenExpiration(new \DateTime('+1 day'));
            $entityManager->flush();

            $email = (new Email())
                ->from('leaguetrading4@gmail.com')
                ->to($user->getEmail())
                ->subject('Password Reset')
                ->html($this->renderView('login/password_reset.twig', [
                    'token' => $token,
                ]));

            $mailer->send($email);

            $this->addFlash('success', 'Password reset email sent.');
        } else {
            $this->addFlash('error', 'User not found.');
        }

        return $this->redirectToRoute('login');
    }



    #[Route('/update_password/{token}', name: 'update_password')]
    public function updatePassword(Request $request, string $token, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if ($user && $user->getTokenExpiration() > new \DateTime()) {
            $form = $this->createForm(UpdatePasswordType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $password = $passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($password);
                $user->setResetToken(null);
                $user->setTokenExpiration(null);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Your password has been updated successfully. You can now login with your new password.');
                return $this->redirectToRoute('login');
            }

            return $this->render('login/update_password.twig', [
                'form' => $form->createView(),
            ]);
        }

        $this->addFlash('error', 'Invalid or expired token.');
        return $this->redirectToRoute('update_password');
    }

}
