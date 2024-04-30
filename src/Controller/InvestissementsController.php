<?php

namespace App\Controller;

use App\Entity\Investissements;
use App\Entity\Projet;
use App\Entity\User;
use App\Form\InvestissementsType;
use App\Repository\InvestissementsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Dompdf\Dompdf;
use Dompdf\Options;



//#[Route('/investissements')]
class InvestissementsController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function home(): Response
    {
        return $this->render('base.html.twig');
    }

    #[Route('/back', name: 'back')]
    public function back(): Response
    {
        return $this->render('baseBack.html.twig');
    }

    #[Route('/front/investissements', name: 'app_investissements_index', methods: ['GET'])]
    public function index(InvestissementsRepository $investissementsRepository, Request $request): Response
    {
        $investissements = $investissementsRepository->findAll();

        return $this->render('front/investissements/index.html.twig', [
            'investissements' => $investissements,
        ]);
    }
    
    #[Route('/front/investissements/search', name: 'app_investissements_search', methods: ['POST'])]
    public function search(Request $request, InvestissementsRepository $investissementsRepository): Response
    {
        $searchTerm = $request->request->get('search');
    
        // Fetch investissements based on the search term
        $investissements = $investissementsRepository->searchByDescription($searchTerm);
    
        return $this->render('front/investissements/index.html.twig', [
            'investissements' => $investissements,
        ]);
    }

    #[Route('/front/investissements/sort', name: 'app_investissements_sort', methods: ['GET'])]
    public function sort(Request $request, InvestissementsRepository $investissementsRepository): Response
    {
        $sortOrder = $request->query->get('sort');

        // Define the sorting criteria
        $orderBy = [];
        if ($sortOrder === 'asc') {
            $orderBy = ['montant' => 'ASC'];
        } elseif ($sortOrder === 'desc') {
            $orderBy = ['montant' => 'DESC'];
        }

        // Fetch sorted investissements from the repository
        $investissements = $investissementsRepository->findBy([], $orderBy);

        // Render the sorted data as HTML or return as JSON
        return $this->render('front/investissements/_table.html.twig', [
            'investissements' => $investissements,
        ]);
    }

    #[Route('/front/investissements/new', name: 'app_investissements_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, RequestStack $requestStack): Response
    {

        $Projet = $entityManager->getRepository(Projet::class)->find(10);
        $User = $entityManager->getRepository(User::class)->find(7);


        $investissement = new Investissements();
        $investissement->setProjetid($Projet);
        $investissement->setUserid($User);


        $form = $this->createForm(InvestissementsType::class, $investissement);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'investissement',
                        ],
                        'unit_amount' => $investissement->getMontant() * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);


            $entityManager->persist($investissement);
            $entityManager->flush();
        
            return $this->redirect($session->url);
        }
    
        return $this->renderForm('front/investissements/new.html.twig', [
            'investissement' => $investissement,
            'form' => $form,
        ]);
    }

    #[Route('/front/investissements/{id}', name: 'app_investissements_show', methods: ['GET'])]
    public function show(Investissements $investissement): Response
    {
        return $this->render('front/investissements/show.html.twig', [
            'investissement' => $investissement,
        ]);
    }

    #[Route('/front/investissements/{id}/edit', name: 'app_investissements_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Investissements $investissement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InvestissementsType::class, $investissement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_investissements_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('front/investissements/edit.html.twig', [
            'investissement' => $investissement,
            'form' => $form,
        ]);
    }

    #[Route('/front/investissements/{id}', name: 'app_investissements_delete', methods: ['POST'])]
    public function delete(Request $request, Investissements $investissement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$investissement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($investissement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_investissements_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/back/investissements', name: 'app_investissementsBack_index', methods: ['GET'])]
    public function indexBack(InvestissementsRepository $investissementsRepository, Request $request): Response
    {
        $investissements = $investissementsRepository->findAll();

        return $this->render('back/investissements/index.html.twig', [
            'investissements' => $investissements,
        ]);
    }

    #[Route('/back/investissements/new', name: 'app_investissementsBack_new', methods: ['GET', 'POST'])]
    public function newBack(Request $request, EntityManagerInterface $entityManager): Response
    {
        $investissement = new Investissements();
        $form = $this->createForm(InvestissementsType::class, $investissement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($investissement);
            $entityManager->flush();

            return $this->redirectToRoute('app_investissementsBack_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/investissements/new.html.twig', [
            'investissement' => $investissement,
            'form' => $form,
        ]);
    }

    #[Route('/back/investissements/{id}/edit', name: 'app_investissementsBack_edit', methods: ['GET', 'POST'])]
    public function editBack(Request $request, Investissements $investissement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InvestissementsType::class, $investissement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_investissementsBack_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/investissements/edit.html.twig', [
            'investissement' => $investissement,
            'form' => $form,
        ]);
    }

    #[Route('/back/investissements/{id}', name: 'app_investissementsBack_delete', methods: ['POST'])]
    public function deleteBack(Request $request, Investissements $investissement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$investissement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($investissement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_investissementsBack_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/back/investissements/{id}', name: 'app_investissementsBack_show', methods: ['GET'])]
    public function showBack(Investissements $investissement): Response
    {
        return $this->render('back/investissements/show.html.twig', [
            'investissement' => $investissement,
        ]);
    }

    #[Route('/back/investissements/sort', name: 'app_investissements2_sort', methods: ['GET'])]
    public function sortBack(Request $request, InvestissementsRepository $investissementsRepository): Response
    {
        $sortOrder = $request->query->get('sort');

        // Define the sorting criteria
        $orderBy = [];
        if ($sortOrder === 'asc') {
            $orderBy = ['montant' => 'ASC'];
        } elseif ($sortOrder === 'desc') {
            $orderBy = ['montant' => 'DESC'];
        }

        // Fetch sorted investissements from the repository
        $investissements = $investissementsRepository->findBy([], $orderBy);

        // Render the sorted data as HTML or return as JSON
        return $this->render('back/investissements/_table.html.twig', [
            'investissements' => $investissements,
        ]);
    }

    #[Route('/pdfinv', name: 'pdf_inv')]
    public function generatePdf(): Response
    {
        $html = $this->renderView('front/investissements/pdf.html.twig', [
            'investissements' => $this->getDoctrine()->getRepository(Investissements::class)->findAll(),
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'landscape');

        $dompdf->render();

        $dompdf->stream('investissements.pdf', [
            'Attachment' => true,
        ]);

        return new Response();
    }

    #[Route('/checkout', name: 'checkout')]
    public function checkout(): Response
    {
        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'investissement',
                    ],
                    'unit_amount' => 1000, // Amount in cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($session->url);
    }


    #[Route('/payment/success', name: 'payment_success')]
    public function success(): Response
    {
        return $this->redirectToRoute('app_investissements_index');
    }

    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function cancel(): Response
    {
        return $this->redirectToRoute('app_investissements_new');
    }

    
    

}
