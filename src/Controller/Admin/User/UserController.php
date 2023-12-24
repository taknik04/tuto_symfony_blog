<?php

namespace App\Controller\Admin\User;

use App\Entity\User;
use App\Form\EditRolesFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/admin/user/list', name: 'admin.user.index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('pages/admin/user/index.html.twig', [
            "users" => $users
        ]);
    }

    #[Route('/admin/user/{id}/edit/roles', name: 'admin.user.edit.roles', methods: ['GET', 'PUT'])]
    public function editRoles(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EditRolesFormType::class, $user, [
            "method" => "PUT"
        ]);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {
            $em->persist($user);

            $em->flush();

            $this->addFlash("success", "La modification du rôle a été un succès.");

            return $this->redirectToRoute('admin.user.index');
        }
        
        return $this->render("pages/admin/user/edit_roles.html.twig", [
            "form" => $form->createView()
        ]);
    }


    #[Route('/admin/user/{id}/delete', name: 'admin.user.delete', methods: ['DELETE'])]
    public function delete(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ( $this->isCsrfTokenValid('delete_user_'.$user->getId(), $request->request->get('csrf_token') ) )
        {

            $posts = $user->getPosts();
            foreach ($posts as $post) 
            {
                $post->setUser(null);
            }

            $this->container->get('security.token_storage')->setToken(null);

            $em->remove($user);

            $em->flush();

            $this->addFlash('success', "Cet utilisateur a été supprimé.");
        }

        return $this->redirectToRoute('admin.user.index');
    }


}
