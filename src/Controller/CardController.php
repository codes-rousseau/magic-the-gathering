<?php

namespace App\Controller;

use App\Service\CardService;
use Symfony\Component\Routing\Annotation\Route;

class CardController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{

    private $cardService;

    public function __construct(CardService $cardService)
    {
        $this->cardService = $cardService;
    }


    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("cards/{collection_id}" , name="cards.collection")
     */
    public function getCardsByCollection($collection_id)
    {

        $cards = $this->cardService->getCardsByCollectionId($collection_id);
        return $this->render('card.html.twig', [
            'cards' => $cards
        ]);
    }
}