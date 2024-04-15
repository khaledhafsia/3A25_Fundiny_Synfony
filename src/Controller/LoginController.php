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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
        $loginForm->handleRequest($request, $authenticationUtils);

        if ($request->isMethod('POST')) {
            $formData = $loginForm->getData();

            $email = $formData->getEmail(); // Corrected accessing email

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', 'Account does not exist.');
            } elseif (!$user->getBanState()) {
                return $this->redirectToRoute('ban_view');
            } else {
                $this->setSessionUser($user);

                switch ($user->getRole()) {
                    case 'Admin':
                        return $this->redirectToRoute('dashboard');
                    case 'Funder':
                        return $this->redirectToRoute('dashboardFunder');
                    case 'Owner':
                        return $this->redirectToRoute('dashboardOwner');
                    default:
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


    #[Route('/password_reset', name: 'password_reset')]
    public function passwordReset(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $staticEmail = 'khaledhafsia2@gmail.com';
        $user = $this->getSessionUser();

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('login');
        }

        $token = uniqid();
        $user->setResetToken($token);
        $user->setTokenExpiration(new \DateTime('+1 day'));
        $entityManager->flush();

        // Generate the URL with the token parameter
        $tokenizedUrl = $this->generateUrl('update_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new Email())
            ->from('leaguetrading4@gmail.com')
            ->to($staticEmail)
            ->subject('Password Reset')
            ->html($this->renderView('login/password_reset.twig', [
                'tokenizedUrl' => $tokenizedUrl,
            ]));

        $mailer->send($email);

        $this->addFlash('success', 'Password reset email sent.');

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
    public function dashboardOwner(): Response
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



    #[Route('/ban_view', name: 'ban_view')]
    public function bannedview(): Response
    {
        return $this->render('login/banview.twig');
    }

    private function setSessionUser(User $user): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $request->getSession()->set('user', $user);
    }

    private function getSessionUser(): ?User
    {
        $request = $this->requestStack->getCurrentRequest();
        return $request->getSession()->get('user');
    }

}
