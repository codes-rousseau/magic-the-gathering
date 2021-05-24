<?php

namespace App\Controller;

use App\Repository\CardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardsController extends AbstractController
{
    /**
     * @Route("/{collectionId}/cards", name="cards", requirements={"collectionId"="\d+"})
     */
    public function index(int $collectionId, CardRepository $cardRepository ): Response
    {

       $cards = $cardRepository->findBy(['collection' => $collectionId]);

        return $this->render('cards/index.html.twig', [
            'cards' => $cards,
        ]);
    }
}
