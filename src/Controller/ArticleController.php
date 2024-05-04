<?php



namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use SQLite3Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


//#[Route('/front/article')]
class ArticleController extends AbstractController
{
 
    #[Route('/home', name: 'home')]
    public function home(): Response
    {
        return $this->render('base.html.twig');
    }
    #[Route('/back', name: 'back')]
    public function back(): Response
    {
        return $this->render('back/baseBack.html.twig');
    }

 
    #[Route('/front/article', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository, CommentRepository $commentRepository): Response
    {
        // Get all articles
        $articles = $articleRepository->findAll();
    
        // Create an associative array to store comments for each article
        $articleComments = [];
    
        // Loop through each article and fetch its associated comments
        foreach ($articles as $article) {
            $comments = $commentRepository->findBy(['postid' => $article->getId()]);
            $articleComments[$article->getId()] = $comments;
        }
    
        // Create a new comment object for the comment form
        $newComment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $newComment);
    
        // Render the Twig template with articles, comments, and the comment form
        return $this->render('front/article/index.html.twig', [
            'articles' => $articles,
            'articleComments' => $articleComments,
            'commentForm' => $commentForm->createView(),
        ]);
    }
    #[Route('/front/article/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/images/front',
                        $newFilename
                    );
                } catch (SQLite3Exception  ) {
                    printf("error");
                }

                $article->setImage($newFilename);
            }

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/front/article/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/images/front',
                        $newFilename
                    );
                } catch (SQLite3Exception ) {
                    // Handle file upload error
                }

                $article->setImage($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/front/article/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('front/article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/front/article/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('front/article/search', name: 'app_article_search', methods: ['GET','POST'])]
public function search(Request $request, ArticleRepository $articleRepository): Response
{
    $searchTerm = $request->query->get('search');

    // Fetch articles based on the search term
    $articles = $articleRepository->findByDescription($searchTerm);

    // Create an associative array to store comments for each article
    $articleComments = [];
    $commentRepository = $this->getDoctrine()->getRepository(Comment::class);

    // Loop through each fetched article and fetch its associated comments
    foreach ($articles as $article) {
        $comments = $commentRepository->findBy(['article' => $article]);
        $articleComments[$article->getId()] = $comments;
    }

    // Create a new comment object for the comment form
    $newComment = new Comment();
    $commentForm = $this->createForm(CommentType::class, $newComment);

    return $this->render('front/article/index.html.twig', [
        'articles' => $articles,
        'articleComments' => $articleComments,
        'commentForm' => $commentForm->createView(),
    ]);
}
#[Route('/back/article', name: 'app_articleback_index', methods: ['GET'])]
public function indexback(ArticleRepository $articleRepository, CommentRepository $commentRepository): Response
{
    // Get all articles
    $articles = $articleRepository->findAll();

    // Create an associative array to store comments for each article
    $articleComments = [];

    // Loop through each article and fetch its associated comments
    foreach ($articles as $article) {
        $comments = $commentRepository->findBy(['postid' => $article->getId()]);
        $articleComments[$article->getId()] = $comments;
    }

    // Create a new comment object for the comment form
    $newComment = new Comment();
    $commentForm = $this->createForm(CommentType::class, $newComment);

    // Render the Twig template with articles, comments, and the comment form
    return $this->render('back/article/index.html.twig', [
        'articles' => $articles,
        'articleComments' => $articleComments,
        'commentForm' => $commentForm->createView(),
    ]);
}
#[Route('/back/article/new', name: 'app_articleback_new', methods: ['GET', 'POST'])]
public function newback(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $article = new Article();
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('imageFile')->getData();

        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/images/front',
                    $newFilename
                );
            } catch (SQLite3Exception  ) {
                printf("error");
            }

            $article->setImage($newFilename);
        }

        $entityManager->persist($article);
        $entityManager->flush();

        return $this->redirectToRoute('app_articleback_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('back/article/new.html.twig', [
        'article' => $article,
        'form' => $form,
    ]);
}

#[Route('/back/article/{id}/edit', name: 'app_articleback_edit', methods: ['GET', 'POST'])]
public function editback(Request $request, Article $article, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('imageFile')->getData();

        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/images/front',
                    $newFilename
                );
            } catch (SQLite3Exception ) {
                // Handle file upload error
            }

            $article->setImage($newFilename);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_articleback_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('back/article/edit.html.twig', [
        'article' => $article,
        'form' => $form,
    ]);
}

#[Route('/back/article/{id}', name: 'app_articleback_show', methods: ['GET'])]
public function showback(Article $article): Response
{
    return $this->render('back/article/show.html.twig', [
        'article' => $article,
    ]);
}

#[Route('/front/article/{id}', name: 'app_articleback_delete', methods: ['POST'])]
public function deleteback(Request $request, Article $article, EntityManagerInterface $entityManager): Response
{
    if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
        $entityManager->remove($article);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_articleback_index', [], Response::HTTP_SEE_OTHER);
}
#[Route('back/article/search', name: 'app_articleback_search', methods: ['GET','POST'])]
public function searchback(Request $request, ArticleRepository $articleRepository): Response
{
$searchTerm = $request->query->get('search');

// Fetch articles based on the search term
$articles = $articleRepository->findByDescription($searchTerm);

// Create an associative array to store comments for each article
$articleComments = [];
$commentRepository = $this->getDoctrine()->getRepository(Comment::class);

// Loop through each fetched article and fetch its associated comments
foreach ($articles as $article) {
    $comments = $commentRepository->findBy(['article' => $article]);
    $articleComments[$article->getId()] = $comments;
}

// Create a new comment object for the comment form
$newComment = new Comment();
$commentForm = $this->createForm(CommentType::class, $newComment);

return $this->render('back/article/index.html.twig', [
    'articles' => $articles,
    'articleComments' => $articleComments,
    'commentForm' => $commentForm->createView(),
]);
}

}