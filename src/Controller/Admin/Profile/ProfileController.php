<?php

namespace App\Controller\Admin\Profile;

// use App\Form\EditUserPasswordFormType;
use App\Form\EditUserProfileFormType;
use App\Form\EditUserPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
    #[Route('/admin/profile', name: 'admin.profile.index', methods:['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/profile/index.html.twig');
    }


    #[Route('/admin/profile/edit', name: 'admin.profile.edit', methods:['GET', 'PUT'])]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $admin = $this->getUser();

        $form = $this->createForm(EditUserProfileFormType::class, $admin, [
            "method" => "PUT"
        ]);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {
            $em->persist($admin);
            $em->flush();

            $this->addFlash("success", "Le profil a bien été modifé.");

            return $this->redirectToRoute("admin.profile.index");
        }

        return $this->render("pages/admin/profile/edit.html.twig", [
            "form" => $form->createView()
        ]);
    }


    #[Route('/admin/profile/edit/password', name: 'admin.profile.edit.password', methods:['GET', 'PUT'])]
    public function editPassword(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        $admin = $this->getUser();

        $form = $this->createForm(EditUserPasswordFormType::class, null, [
            "method" => "PUT"
        ]);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {
            $newPassword = $form->getData()['password'];

            $passwordHashed = $hasher->hashPassword($admin, $newPassword);

            $admin->setPassword($passwordHashed);

            $em->persist($admin);
            $em->flush();

            $this->addFlash('success', "Le mot de passe a été modifié.");
            return $this->redirectToRoute("admin.profile.index");
        }

        return $this->render("pages/admin/profile/edit_password.html.twig", [
            "form" => $form->createView()
        ]);
    }

}
