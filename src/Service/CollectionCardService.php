<?php

namespace App\Service;

use App\Repository\CardCollectionRepository;

class CollectionCardService
{

    private $cardCollectionRepository;

    public function __construct(CardCollectionRepository $cardCollectionRepository)
    {
        $this->cardCollectionRepository = $cardCollectionRepository;
    }

    public function getAllCollections()
    {
        return $this->cardCollectionRepository->findAll();
    }
}