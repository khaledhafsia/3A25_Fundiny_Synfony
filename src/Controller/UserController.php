<?php

namespace App\Controller;
use App\Form\UpdateUserType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Controller\LoginController;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class UserController extends AbstractController

{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/SignUpp', name: 'SignUpp')]
    public function AddUser(ManagerRegistry $registry, Request $request, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->add('Register_Account', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-primary btn-user btn-block'
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            if (!$this->isPasswordComplex($password)) {
                $errorMessage = 'Password must contain at least 8 characters and include both numbers and letters.';
                $form->get('password')->addError(new FormError($errorMessage));
                return $this->renderForm('SignUp/SignUp.twig', [
                    'form' => $form,
                ]);
            }

            $email = $user->getEmail();
            $existingUser = $registry->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($existingUser === null) {
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                $em = $registry->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('login');
            } else {
                $errorMessage = 'Email already in use. Please choose another email.';
                $this->addFlash('error', $errorMessage);

                return $this->renderForm('SignUp/SignUp.twig', [
                    'form' => $form,
                ]);
            }
        }
     //   return $this->redirectToRoute('loginn');

        return $this->renderForm('SignUp/SignUp.twig', [
            'form' => $form,
        ]);

    }
    #[Route('/update_user/{id}', name: 'update_user')]
    public function updatePoste(ManagerRegistry $doctrine, Request $request, $id, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $doctrine->getRepository(User::class)->find($id);
        $form = $this->createForm(UpdateUserType::class, $user);
        $form->add('Modifier', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-primary btn-user btn-block'
            ]
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $id = $user->getTitre();
            $password = $form->get('password')->getData();

            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $em = $doctrine->getManager();
            $user = $form->getData();

            $em->persist($user);
            $em->flush();
            $this->addFlash(
                'info',
                'Modifié avec succès'
            );
            return $this->redirectToRoute('update_user', ['id' => $user->getId()]);
            //  }
        }
        return $this->renderForm('user/back/update_user.html.twig', [
            'form' => $form,
        ]);
    }

    private function isPasswordComplex(string $password): bool
    {
        $lengthRequirement = strlen($password) >= 8;

        $containsNumbers = preg_match('/\d/', $password) > 0;
        $containsLetters = preg_match('/[a-zA-Z]/', $password) > 0;

        return $lengthRequirement && $containsNumbers && $containsLetters;
    }


    #[Route('/delete_user{id}', name: 'delete_user')]
    public function DropUser(ManagerRegistry $repository, $id): Response
    {

        $users = $repository->getRepository(User::class)->find($id);
        $em = $repository->getManager();
        $em->remove($users);
        $em->flush();
        return $this->redirectToRoute('list_user');
    }

    #[Route('/ban_user/{id}', name: 'ban_user')]
    public function banUser(ManagerRegistry $doctrine, $id): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $user->setBanState(true);
        $entityManager->flush();

        $this->addFlash('success', 'User banned successfully.');

        return $this->redirectToRoute('list_user');
    }

    #[Route('/unban_user/{id}', name: 'ban_user')]
    public function UnbanUser(ManagerRegistry $doctrine, $id): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $user->setBanState(false);
        $entityManager->flush();

        $this->addFlash('success', 'User banned successfully.');

        return $this->redirectToRoute('list_user');
    }


    #[Route('/list_user', name: 'list_user')]
    public function ListUser(UserRepository $repository): Response
    {

        $users = $repository->findAll();
        return $this->render('user/back/list_user.html.twig', [
            'user' => $users,
        ]);
    }

    #[Route('/filter_user_by_role', name: 'filter_user_by_role')]
    public function filterUserByRole(Request $request, UserRepository $userRepository): JsonResponse
    {
        $role = $request->query->get('role');
        $users = $userRepository->findByRole($role);

        $userData = [];
        foreach ($users as $user) {
            $userData[] = [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
            ];
        }

        return new JsonResponse($userData);
    }

    #[Route('/search_user', name: 'search_user')]
    public function searchUser(Request $request, UserRepository $userRepository): JsonResponse
    {
        $searchInput = $request->query->get('searchInput');

        if ($searchInput !== null) {
            $users = $userRepository->findByPartialNom($searchInput);

            $userData = [];
            foreach ($users as $user) {
                $userData[] = [
                    'nom' => $user->getNom(),
                    'prenom' => $user->getPrenom(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole(),
                    'banstate' => $user->isBanState(),
                ];
            }
            return new JsonResponse($userData);
        } else {
            return new JsonResponse(['message' => 'No search input provided'], Response::HTTP_BAD_REQUEST);
        }
    }
}