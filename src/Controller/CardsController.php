<?php

namespace App\Controller;

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
        return $this->render('cards/index.html.twig', [
            'cards' => $cardRepository->findByCollection($collection),
        ]);
    }
}
