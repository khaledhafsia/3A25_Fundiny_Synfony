<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\UpdatePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

            $email = $formData->getEmail();

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', 'Account does not exist.');
            } elseif (!$user->getBanState() && $user->getBanState()==!NULL) {
                return $this->redirectToRoute('ban_view');
            } else {
                $this->setSessionUser($user);

                switch ($user->getRole()) {
                    case 'Admin':
                        return $this->redirectToRoute('list_user');
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
        $user->setTokenExpiration(new \DateTime('9999-12-31 23:59:59'));

        //$user->setTokenExpiration(new \DateTime('+1 day'));
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
    public function updatePassword(ManagerRegistry $doctrine, Request $request, string $token, UserPasswordHasherInterface $passwordHasher): Response
    {
        $entityManager = $doctrine->getManager();

        //$user = $entityManager->getRepository(User::class)->findOneBy(['resetToken' => $token]);
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'khaledhafsia2@gmail.com']);

        if (!$user) {
            $this->addFlash('error', 'Invalid or expired token. Please request a new password reset.');
            return $this->redirectToRoute('password_reset');
        }

        $tokenExpiration = $user->getTokenExpiration();
        if ($tokenExpiration instanceof \DateTime && $tokenExpiration < new \DateTime()) {
            $this->addFlash('error', 'Token expired. Please request a new password reset.');
            return $this->redirectToRoute('password_reset');
        }

        $form = $this->createForm(UpdatePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the new password
            $password = $passwordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($password);

            $user->setResetToken(null);
            $user->setTokenExpiration(null); // Set token expiration to null after password reset

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Your password has been updated successfully. You can now login with your new password.');
            return $this->redirectToRoute('login');
        }

        return $this->render('login/update_password.twig', [
            'form' => $form->createView(),
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
