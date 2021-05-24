<?php

namespace App\Controller;

use App\Classe\Search;
use App\Form\SearchType;
use App\Repository\CardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardsController extends AbstractController
{
    /**
     * @Route("/{collectionId}/cards", name="cards", requirements={"collectionId"="\d+"})
     */
    public function index(int $collectionId, CardRepository $cardRepository, Request $request): Response
    {

        $cards = $cardRepository->findBy(['collection' => $collectionId]);

        $search = new Search();

        $form = $this->createForm(SearchType::class, $search);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $cards = $cardRepository->findWithSearch($search) ;

        }



        return $this->render('cards/index.html.twig', [
            'cards' => $cards,
            'form' =>  $form->createView()
        ]);
    }
}
