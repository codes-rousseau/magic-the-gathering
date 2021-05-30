<?php

namespace App\Controller\Pages;

use App\Repository\CardRepository;
use App\Repository\SetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CollectionController extends AbstractController
{
    /**
     * @Route("/collection", name="collection")
     */
    public function index(SetRepository $setRepository): Response
    {
        $sets = $setRepository->findAll();

        return $this->render('pages/index.html.twig', [
            'sets' => $sets
        ]);
    }

    /**
     * @Route("/collection/{id}", name="collection_detail")
     */
    public function collection(CardRepository $cardRepository, int $id): Response
    {
        $cards = $cardRepository->findBy(['set' => $id]);

        return $this->render('pages/collection.html.twig', [
            'cards' => $cards
        ]);
    }
}
