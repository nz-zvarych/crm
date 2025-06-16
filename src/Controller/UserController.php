<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use App\Service\FileUploader;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

use App\Form\UserForm;

final class UserController extends AbstractController
{
    #[Route('/users', name: 'app_users')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/new', name: 'user_new')]
    public function new(
        EntityManagerInterface $entityManager, 
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher,
        FileUploader $fileUploader
    ): Response
    {
        $user = new User();
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('password')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $profilePicture = $form->get('profilePicture')->getData();
            if ($profilePicture) {
                $profilePictureName = $fileUploader->upload($profilePicture, $this->getParameter('profiles_directory'));
                $user->setProfilePicture($profilePictureName);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('notice', 'User created successfully!');
            return $this->redirectToRoute('app_users');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/user/{id<\d+>}/edit', name: 'user_edit')]
    public function edit(
        User $user, 
        EntityManagerInterface $entityManager, 
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher,
        FileUploader $fileUploader,
    ): Response
    {
        $form = $this->createForm(UserForm::class, $user);

        $previousProfilePicture = $user->getProfilePicture();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('password')->getData();

            if ( $plainPassword !== null ) {
                // encode the plain password
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

            $profilePicture = $form->get('profilePicture')->getData();

            if ($profilePicture) {
                $profilePictureName = $fileUploader->upload($profilePicture, $this->getParameter('profiles_directory'));
                $user->setProfilePicture($profilePictureName);
            } else {
                // $profilePictureName = $fileUploader->upload($previousProfilePicture);
                $user->setProfilePicture($previousProfilePicture);
            }

            $entityManager->flush();

            $this->addFlash('notice', 'User updated successfully!');
            return $this->redirectToRoute('app_users');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/user/{id<\d+>}/delete', name: 'user_delete')]
    public function delete(User $user, EntityManagerInterface $entityManager, Request $request): Response
    {

        if ($request->isMethod('POST')) {

            $entityManager->remove($user);

            $entityManager->flush();

            $this->addFlash('notice', 'User deleted successfully!');
            return $this->redirectToRoute('app_users');
        }

        return $this->render('user/delete.html.twig', [
            'user' => $user->getId(),
            'user_email' => $user->getEmail(),
        ]);
    }
}
