<?php

namespace App\Controller;

use App\Classes\Search;
use App\Form\SearchCardType;
use App\Repository\CardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardsController extends AbstractController
{
    /**
     * @Route("/cards/{collection}", name="cards", requirements={"collectionId"="\d+"})
     */
    public function index( int $collection, Request $request, CardRepository $cardRepository): Response
    {

        $cards = $cardRepository->findByCollection($collection);

        $search = new Search();

        $form = $this->createForm(SearchCardType::class, $search);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cards = $cardRepository->findByCollectionAndForm($search, $collection);
        }

        return $this->render('cards/index.html.twig', [
            'cards' => $cards,
            'form' => $form->createView(),
        ]);
    }
}
