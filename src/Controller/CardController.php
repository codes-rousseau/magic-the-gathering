<?php

namespace App\Controller;

use App\Repository\CardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardController extends AbstractController
{
    /**
     * @Route(name="card_show", path="/card/{id}")
     */
    public function show(CardRepository $cardRepository, int $id): Response
    {
        $card = $cardRepository->find($id);
        $colors = $card->getColors();

        return $this->render('card/show.html.twig', [
            'set' => $card->getSet(),
            'card' => $card,
            'colors' => $colors,
        ]);
    }
}
