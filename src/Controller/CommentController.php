<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Article;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


//#[Route('/comment')]
class CommentController extends AbstractController
{
    #[Route('/c', name: 'app_comment_index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_comment_new', methods: ['POST'])]
public function new(Request $request): Response
{
    $commentText = $request->request->get('comment');
    $articleId = $request->request->get('articleId');

    // Fetch the Article entity based on the ID
    $article = $this->getDoctrine()->getRepository(Article::class)->find($articleId);

    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    // Create a new Comment object
    $comment = new Comment();
    $comment->setComment($commentText);
    $comment->setPostid($article);

    // Get the entity manager
    $entityManager = $this->getDoctrine()->getManager();

    // Persist and flush the new comment
    $entityManager->persist($comment);
    $entityManager->flush();

    // Redirect back to the index page after adding the comment
    return $this->redirectToRoute('app_article_index');
}
    
    #[Route('/{commentid}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $commentid): Response
    {
        // Get the comment entity from the database
        $entityManager = $this->getDoctrine()->getManager();
        $comment = $entityManager->getRepository(Comment::class)->find($commentid);

        // Check if the comment exists
        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }

        // Create the comment edit form
        $form = $this->createForm(CommentType::class, $comment, [
            'action' => $this->generateUrl('app_comment_edit', ['commentid' => $commentid]),
            'method' => 'POST',
        ]);

        // Handle form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the edited comment
            $entityManager->persist($comment);
            $entityManager->flush();

            // Redirect or return a response as needed
            return $this->redirectToRoute('app_article_index'); // Example redirect
        }

        // Render the edit comment form
        return $this->render('comment/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    
}
    #[Route('/{commentid}', name: 'app_comment_delete', methods: ['POST'])] 
        public function delete(Request $request, int $commentid): Response
    {
        // Get the comment entity from the database
        $entityManager = $this->getDoctrine()->getManager();
        $comment = $entityManager->getRepository(Comment::class)->find($commentid);

        // Check if the comment exists
        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }

        // Handle comment deletion logic
        $entityManager->remove($comment);
        $entityManager->flush();

        // Redirect or return a response as needed
        return $this->redirectToRoute('app_article_index'); // Example redirect
    }
    #[Route('/back/comment', name: 'app_commentback_index', methods: ['GET'])]
    public function indexback(CommentRepository $commentRepository): Response
    {
        return $this->render('front/comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }

    #[Route('back/comment/new', name: 'app_commentback_new', methods: ['POST'])]
public function newback(Request $request): Response
{
    $commentText = $request->request->get('comment');
    $articleId = $request->request->get('articleId');

    // Fetch the Article entity based on the ID
    $article = $this->getDoctrine()->getRepository(Article::class)->find($articleId);

    if (!$article) {
        throw $this->createNotFoundException('Article not found');
    }

    // Create a new Comment object
    $comment = new Comment();
    $comment->setComment($commentText);
    $comment->setPostid($article);

    // Get the entity manager
    $entityManager = $this->getDoctrine()->getManager();

    // Persist and flush the new comment
    $entityManager->persist($comment);
    $entityManager->flush();

    // Redirect back to the index page after adding the comment
    return $this->redirectToRoute('app_articleback_index');
}
    
    #[Route('back/comment/{commentid}/edit', name: 'app_commentback_edit', methods: ['GET', 'POST'])]
    public function editback(Request $request, int $commentid): Response
    {
        // Get the comment entity from the database
        $entityManager = $this->getDoctrine()->getManager();
        $comment = $entityManager->getRepository(Comment::class)->find($commentid);

        // Check if the comment exists
        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }

        // Create the comment edit form
        $form = $this->createForm(CommentType::class, $comment, [
            'action' => $this->generateUrl('app_commentback_edit', ['commentid' => $commentid]),
            'method' => 'POST',
        ]);

        // Handle form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the edited comment
            $entityManager->persist($comment);
            $entityManager->flush();

            // Redirect or return a response as needed
            return $this->redirectToRoute('app_articleback_index'); // Example redirect
        }

        // Render the edit comment form
        return $this->render('back/comment/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    
}
    #[Route('back/comment/{commentid}', name: 'app_commentback_delete', methods: ['POST'])] 
        public function deleteback(Request $request, int $commentid): Response
    {
        // Get the comment entity from the database
        $entityManager = $this->getDoctrine()->getManager();
        $comment = $entityManager->getRepository(Comment::class)->find($commentid);

        // Check if the comment exists
        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }

        // Handle comment deletion logic
        $entityManager->remove($comment);
        $entityManager->flush();

        // Redirect or return a response as needed
        return $this->redirectToRoute('app_articleback_index'); // Example redirect
    }
}
