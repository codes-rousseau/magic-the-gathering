<?php

namespace App\Controller;

use App\Entity\Card;
use App\Repository\CardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CollectionsRepository;
use App\Form\SearchCardsType;

class CollectionsController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function homeCollections(CollectionsRepository $collectionsRepository): Response
    {

        return $this->render('collections/index.html.twig', [
            'collections' => $collectionsRepository->findAll(),
        ]);
    }
    /**
     * @Route("/collection/{code}", name="collection")
     */
    public function viewCollection(
        String $code,
        CollectionsRepository $collectionsRepository,
        CardRepository $cardRepository,
        Request $request
    ): Response
    {
        $card = new Card();

        $collection = $collectionsRepository->findOneBy(['code' => $code]);

        $form = $this->createForm(SearchCardsType::class, $card);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $card->setCollection($collection);

            $cards = $cardRepository->findByForm($card);
        } else {
            $cards = $collection->getCards();
        }


        if( !is_null($collection)) {
            return $this->render('collections/view.html.twig', [
                'collection_name' => $collection->getName(),
                'cards' => $cards,
                'search_form' => $form->createView(),
            ]);
        } else {
            throw $this->createNotFoundException('The collection does not exist');

        }
    }
}
