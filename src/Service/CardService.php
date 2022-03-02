<?php

namespace App\Service;




use App\Repository\CardRepository;

class CardService
{

    private $cardRepository;

    public function __construct(CardRepository $cardRepository)
    {
        $this->cardRepository = $cardRepository;
    }

    public function getCardsByCollectionId($id)
    {
        return $this->cardRepository->findBy([
            'collection' => $id
        ]);
    }



}