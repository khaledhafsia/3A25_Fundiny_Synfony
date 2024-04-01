<?php

namespace App\Controller;
use App\Entity\Poste;
use App\Form\PosteType;
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
class UserController extends AbstractController

{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/SignUp', name: 'SignUp')]
    public function AddUser(ManagerRegistry $registry, Request $request,UserPasswordHasherInterface $passwordHasher): Response
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
            // Populate $user object with form data
            $user = $form->getData();

            $email = $user->getEmail();
            $existingUser = $registry->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($existingUser === null) {
                $password = $passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($password);

                $em = $registry->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('Login');
            } else {
                return $this->render('user/SignUp.html.twig', [
                    'registrationForm' => $form->createView(),
                    'errors' => 'Email already in use',
                ]);
            }
        }


        return $this->renderForm('User/front/SignUp.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/list_user', name: 'list_user')]
    public function ListUser(UserRepository $repository): Response
    {

        $users = $repository->findAll();
        return $this->render('user/back/list_user.html.twig', [
            'user' => $users,
        ]);
    }
    #[Route('/update_user/{id}', name: 'update_user')]
    public function updatePoste(ManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $doctrine->getRepository(User::class)->find($id);
        $form = $this->createForm(UserType::class, $user);
        $form->add('Modifier', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-primary btn-user btn-block'
            ]
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $id = $user->getTitre();
            $em = $doctrine->getManager();
            $existingUser = $em->getRepository(User::class)->findOneBy(['id' => $id]);

            if ($existingUser) {
                $this->addFlash('error', 'User exists.');
            } else {
                $user = $form->getData();

                $em->persist($user);
                $em->flush();
                $this->addFlash(
                    'info',
                    'Modifié avec succès'
                );
                return $this->redirectToRoute('update_user', ['id' => $user    ->getId()]);
            }
        }
        return $this->renderForm('user/back/update_user.html.twig', [
            'form' => $form,
        ]);
    }
/*
    #[Route('/update_user{id}',name: 'update_user')]
    public function UpdateUser(Request $request,ManagerRegistry $registry,$id,UserPasswordHasherInterface $passwordHasher)
    {

        $user = $registry->getRepository(User::class)->find($id);

        $form = $this->createForm(UserType::class,$user);

        $form->add('Modifier', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-primary btn-user btn-block'
            ]
        ]);
        if ($form->isSubmitted() && $form->isValid()) {
            // Populate $user object with form data
            $user = $form->getData();

            $email = $user->getEmail();
            $existingUser = $registry->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($existingUser === null) {
                $password = $passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($password);

                $em = $registry->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('login');
            }
            else
            {
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'errors' => 'Email déjà utilisé',
                ]);
            }
        }

        return $this->renderForm('user/back/update_user.html.twig',
            ['form'=>$form]);
    }

*/

    #[Route('/delete_user{id}', name: 'delete_user')]
    public function DropUser(ManagerRegistry $repository,$id): Response
    {

        $users = $repository->getRepository(User::class)->find($id);
        $em = $repository->getManager();
        $em->remove($users);
        $em->flush();
        return $this->redirectToRoute('list_user');
    }


}
