<?php

namespace App\Controller\Visitor\Blog;

use App\Entity\Tag;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Category;
use App\Entity\PostLike;
use App\Form\CommentFormType;
use App\Repository\TagRepository;
use App\Repository\PostRepository;
use App\Repository\CommentRepository;
use App\Repository\CategoryRepository;
use App\Repository\PostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'visitor.blog.index', methods:['GET'])]
    public function index(
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        PostRepository $postRepository
    ): Response
    {
        $categories = $categoryRepository->findAll();
        $tags = $tagRepository->findAll();
        $posts = $postRepository->findBy(["isPublished" => true]);

        return $this->render('pages/visitor/blog/index.html.twig', [
            "categories" => $categories,
            "tags"       => $tags,
            "posts"      => $posts
        ]);
    }

    #[Route('/blog/posts/filter-by-category/{id}/{slug}', name: 'visitor.blog.posts.filter_by_category', methods:['GET'])]
    public function postsFilterByCategory(
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        PostRepository $postRepository,
        Category $category
    ): Response
    {

        $categories = $categoryRepository->findAll();
        $tags       = $tagRepository->findAll();
        $posts      = $postRepository->findPostsByCategory($category->getId());

        return $this->render('pages/visitor/blog/index.html.twig', [
            "categories" => $categories,
            "tags"       => $tags,
            "posts"      => $posts
        ]);
    }


    #[Route('/blog/posts/filter-by-tag/{id}/{slug}', name: 'visitor.blog.posts.filter_by_tag', methods:['GET'])]
    public function postsFilterByTag(
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        PostRepository $postRepository,
        Tag $tag
    ): Response
    {

        $categories = $categoryRepository->findAll();
        $tags       = $tagRepository->findAll();
        $posts      = $postRepository->findPostsByTag($tag->getId());

        return $this->render('pages/visitor/blog/index.html.twig', [
            "categories" => $categories,
            "tags"       => $tags,
            "posts"      => $posts
        ]);
    }


    #[Route('/blog/post/{id}/{slug}/show', name: 'visitor.blog.post.show', methods:['GET', 'POST'])]
    public function show(Post $post, Request $request, EntityManagerInterface $em, CommentRepository $commentRepository): Response
    {

        $comment = new Comment();

        $form = $this->createForm(CommentFormType::class, $comment);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {

            $comment->setPost($post);
            $comment->setUser($this->getUser());

            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('visitor.blog.post.show', [
                "id" => $post->getId(),
                "slug" => $post->getSlug(),
            ]);
        }

        return $this->render('pages/visitor/blog/show.html.twig', [
            "post" => $post,
            "form" => $form->createView(),
        ]);
    }


    #[Route('/blog/post/{id}/{slug}/like', name: 'visitor.blog.post.like', methods:['GET'])]
    public function like(
        Post $post, 
        PostLikeRepository $postLikeRepository,
        EntityManagerInterface $em
        ) : Response
    {
        // Récupérons l'utilisateur censé être connecté.
        $user = $this->getUser();

        // S'il n'est pas connecté,
        if (null === $user) 
        {
            // Retournons la réponse au navigateur du client, lui expliquant que l'utilisateur n'est pas connecté.
            return $this->json(['message' => "Vous devez être connecté avant d'aimer cet article."], 403);
        }

        // Dans le cas contraire,

        // Vérifions, si l'article a déjà été aimé par l'utilisateur connecté,
        if ( $post->isLikedBy($user) ) 
        {
            // Récupérons ce like,
            $like = $postLikeRepository->findOneBy(['post'=>$post, 'user' =>$user]);

            // Demandons au gestionnaire des entités de supprimer le like.
            $em->remove($like);
            $em->flush();

            // Retournons la réponse correspondante au navigateur du client pour qu'il mette à jour les données.
            return $this->json([
                'message' => "Le like a été retiré.",
                'totalLikes' => $postLikeRepository->count(['post' => $post])
            ]);
        }
        
        // Dans le cas contraire,
        
        // Créons le nouveau like
        $postLike = new PostLike();
        $postLike->setUser($user);
        $postLike->setPost($post);

        // Demandons au gestionnaire des entités de réaliser la requête la requête d'insertion en base.
        $em->persist($postLike);
        $em->flush();

        // Retournons la réponse correspondante au navigateur du client pour qu'il mette à jour les données.
        return $this->json([
            'message' => "Le like a été ajouté.",
            'totalLikes' => $postLikeRepository->count(['post' => $post])
        ]);

    }


}
