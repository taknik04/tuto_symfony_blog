<?php

namespace App\Controller\Admin\Post;

use App\Entity\Post;
use DateTimeImmutable;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    #[Route('/admin/post/list', name: 'admin.post.index', methods:['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();
        return $this->render('pages/admin/post/index.html.twig', [
            "posts" => $posts
        ]);
    }

    #[Route('/admin/post/create', name: 'admin.post.create', methods:['GET', 'POST'])]
    public function create(
        Request $request, 
        EntityManagerInterface $em, 
        CategoryRepository $categoryRepository
    ): Response
    {

        if ( count($categoryRepository->findAll()) <= 0 ) 
        {
            $this->addFlash("warning", "Vous devez créer au moins une catégorie avant de rédiger des articles.");
            return $this->redirectToRoute('admin.category.index');
        }

        $post = new Post();

        $form = $this->createForm(PostFormType::class, $post);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {

            $admin = $this->getUser();
            $post->setUser($admin);

            $em->persist($post);
            $em->flush();

            $this->addFlash('success', "L'article a été créé et sauvegardé.");

            return $this->redirectToRoute('admin.post.index');
        }

        return $this->render("pages/admin/post/create.html.twig", [
            "form" => $form->createView()
        ]);
    }


    #[Route('/admin/post/{id}/publish', name: 'admin.post.publish', methods:['PUT'])]
    public function publish(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        // Si le CSRF TOKEN est valide
        if ( $this->isCsrfTokenValid('publish_post'.$post->getId(), $request->request->get('csrf_token')) ) 
        {
            // Si l'article n'est pas encore publié
            if ( false === $post->isIsPublished() ) 
            {
                // Publier l'article
                $post->setIsPublished(true);

                // Mettre à jour la date de publication
                $post->setPublishedAt(new DateTimeImmutable());

                // Générer le message flash correspondant
                $this->addFlash('success', "L'article a été publié.");
            }
            else // Dans le cas contraire,
            {
                // Retirer l'article de la liste des publications
                $post->setIsPublished(false);

                // Générer le message flash correspondant
                $this->addFlash('success', "L'article a été retiré de la liste des publications.");
            }
                
            // Demander au manager des entités de préparer la requête de modification de cet article
            $em->persist($post);

            // Demander au manager d'exécuter la requête
            $em->flush();
        }

        return $this->redirectToRoute('admin.post.index');
    }


    #[Route('/admin/post/{id}/show', name: 'admin.post.show', methods:['GET'])]
    public function show(Post $post): Response
    {
        return $this->render("pages/admin/post/show.html.twig", [
            "post" => $post
        ]);
    }


    #[Route('/admin/post/{id}/edit', name: 'admin.post.edit', methods:['GET', 'PUT'])]
    public function edit(
        Post $post, 
        Request $request, 
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository
    ): Response
    {

        if ( count($categoryRepository->findAll()) <= 0 ) 
        {
            $this->addFlash("warning", "Vous devez créer au moins une catégorie avant de rédiger des articles.");
            return $this->redirectToRoute('admin.category.index');
        }

        $form = $this->createForm(PostFormType::class, $post, [
            "method" => "PUT"
        ]);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {

            $post->setUser($this->getUser());

            $em->persist($post);
            $em->flush();

            $this->addFlash("success", "L'article a été modifié et sauvegardé.");

            return $this->redirectToRoute('admin.post.index');
        }
        
        return $this->render("pages/admin/post/edit.html.twig", [
            "form" => $form->createView(),
            "post" => $post
        ]);
    }


    #[Route('/admin/post/{id}/delete', name: 'admin.post.delete', methods:['DELETE'])]
    public function delete(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        if ( $this->isCsrfTokenValid('delete_post_'.$post->getId(), $request->request->get('csrf_token') ) )
        {
            $em->remove($post);

            $em->flush();

            $this->addFlash('success', "Ce article a été supprimé.");
        }

        return $this->redirectToRoute('admin.post.index');
    }





}
