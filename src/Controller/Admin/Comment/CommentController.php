<?php

namespace App\Controller\Admin\Comment;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    #[Route('/admin/comment/list', name: 'admin.comment.index', methods:['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('pages/admin/comment/index.html.twig', [
            "comments" => $commentRepository->findAll()
        ]);
    }

    #[Route('/admin/comment/{id}/activate', name: 'admin.comment.activate', methods:['PUT'])]
    public function publish(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        // Si le CSRF TOKEN est valide
        if ( $this->isCsrfTokenValid('activate_comment_'.$comment->getId(), $request->request->get('csrf_token')) ) 
        {
            // Si l'article n'est pas encore publié
            if ( false === $comment->isIsActivated() ) 
            {
                // Publier l'article
                $comment->setIsActivated(true);   

                // Générer le message flash correspondant
                $this->addFlash('success', "Le commentaire a été activé.");
            }
            else // Dans le cas contraire,
            {
                // Retirer l'article de la liste des publications
                $comment->setIsActivated(false);

                // Générer le message flash correspondant
                $this->addFlash('success', "Le commentaire a été désactivé.");
            }
                
            // Demander au manager des entités de préparer la requête de modification de cet article
            $em->persist($comment);

            // Demander au manager d'exécuter la requête
            $em->flush();
        }

        return $this->redirectToRoute('admin.comment.index');
    }


    #[Route('/admin/comment/{id}/delete', name: 'admin.comment.delete', methods:['DELETE'])]
    public function delete(Comment $comment, Request $request, EntityManagerInterface $em): Response
    {
        if ( $this->isCsrfTokenValid('delete_comment_'.$comment->getId(), $request->request->get('csrf_token') ) )
        {
            $em->remove($comment);

            $em->flush();

            $this->addFlash('success', "Ce commentaire a été supprimé!");
        }

        return $this->redirectToRoute('admin.comment.index');
    }
}