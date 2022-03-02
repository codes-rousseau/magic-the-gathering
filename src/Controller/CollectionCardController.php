<?php

namespace App\Controller;

use App\Service\CollectionCardService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CollectionCardController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{

    private $collectionCardService;

    public function __construct(CollectionCardService $collectionCardService)
    {
        $this->collectionCardService = $collectionCardService;
    }

    /**
     * @return Response
     * @Route("collections")
     */
    public function getCollections()
    {

        $collections = $this->collectionCardService->getAllCollections();

        return $this->render('card.collection.html.twig', [
            'collections' => $collections
        ]);
    }
}