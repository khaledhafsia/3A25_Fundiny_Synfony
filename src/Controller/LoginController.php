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
use Symfony\Component\HttpFoundation\RedirectResponse;
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
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;
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
        public function login(AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, SessionInterface $session): Response
        {
            $request = $this->requestStack->getCurrentRequest();
            $loginForm = $this->createForm(LoginType::class, null, [
                'reset_password_route' => $this->generateUrl('password_reset'),
            ]);
            $loginForm->handleRequest($request, $authenticationUtils);

            if ($request->isMethod('POST')) {
                $formData = $loginForm->getData();

                $email = $formData->getEmail();
                $password = $formData->getPassword();

                $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if (!$user) {
                    $this->addFlash('error', 'Account does not exist.');
                    // } elseif (!$user->getBanState() && $user->getBanState()==!NULL) {
                } elseif (!$user->isBanState() == false && $user->isBanState() == !NULL) {
                    return $this->redirectToRoute('ban_view');
                } elseif (!$passwordHasher->isPasswordValid($user, $password)) {
                    $this->addFlash('error', 'Incorrect password.');
                } else {
                    $this->setSessionUser($user);
                    $session->set('user_id', $user->getId());

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

    #[Route('/logout', name: 'logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->remove('user_id');

        return $this->redirectToRoute('login');
    }

    #[Route('/password_reset', name: 'password_reset')]
    public function passwordReset(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $email = $request->get('email');
        $this->addFlash('success', 'Password reset email sent.');

        if ($email) {
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                $token = uniqid();
                $user->setResetToken($token);
                $user->setTokenExpiration(new \DateTime('+1 day'));
                $entityManager->flush();

                $tokenizedUrl = $this->generateUrl('update_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                $email = (new Email())
                    ->from('leaguetrading4@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Password Reset')
                    ->html($this->renderView('login/password_reset.twig', [
                        'tokenizedUrl' => $tokenizedUrl,
                    ]));

                $mailer->send($email);
                $this->addFlash('success', 'Password reset email sent.');
            } else {
                $this->addFlash('error', 'User not found.');
            }
        }

        return $this->redirectToRoute('login');
    }

    #[Route('/update_password/{token}', name: 'update_password')]
    public function updatePassword(ManagerRegistry $doctrine, Request $request, string $token, UserPasswordHasherInterface $passwordHasher): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['resetToken' => $token]);


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
            $password = $passwordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($password);

            $user->setResetToken(null);
            $user->setTokenExpiration(null);

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


        return $this->render('Dashboard/dashboardOwner.twig', [
            'user' => $user,
        ]);

    }

    #[Route('/dashboardFunder', name: 'dashboardOwner')]
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

    #[Route('/google_login', name: 'google_login')]
    public function test(): RedirectResponse
    {
        $provider = new Google([
            'clientId' => $_ENV['GOOGLE_CLIENT_ID'],
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'],
            'redirectUri' => $_ENV['GOOGLE_REDIRECT_URI'],
        ]);

        $authorizationUrl = $provider->getAuthorizationUrl();

        $_SESSION['oauth2state'] = $provider->getState();

        return $this->redirect($authorizationUrl);
    }

    #[Route('/login-google-check', name: 'google_auth_callback')]
    public function googleAuthCallback(Request $request): Response
    {
        if (empty($_SESSION['oauth2state']) || ($request->query->get('state') !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        }

        $provider = new Google([
            'clientId' => $_ENV['GOOGLE_CLIENT_ID'],
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'],
            'redirectUri' => $_ENV['GOOGLE_REDIRECT_URI'],
        ]);

        try {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $request->query->get('code'),
            ]);

            $this->storeTokens($accessToken);

            if ($accessToken->hasExpired()) {
                $newAccessToken = $provider->getAccessToken('refresh_token', [
                    'refresh_token' => $accessToken->getRefreshToken(),
                ]);

                $this->storeTokens($newAccessToken);

                $accessToken = $newAccessToken;
            }

            $user = $provider->getResourceOwner($accessToken);

            $email = $user->getEmail();

            return $this->render('dashboard/dashboardOwner.twig', [
                'user' => $user, // Pass the user object to the template
            ]);
        } catch (IdentityProviderException $e) {
            exit($e->getMessage());
        }
    }
    private function storeTokens($accessToken)
    {
        $_SESSION['access_token'] = $accessToken->getToken();
        $_SESSION['refresh_token'] = $accessToken->getRefreshToken();
    }





    }
