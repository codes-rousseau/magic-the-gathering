<?php

namespace App\Controller;

use App\Entity\CollectionCard;
use App\Form\CollectionCardType;
use App\Repository\CollectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/collection/card")
 */
class CollectionCardController extends AbstractController
{
    /**
     * @Route("/", name="app_collection_card_index", methods={"GET"})
     */
    public function index(CollectionRepository $collectionRepository): Response
    {
        return $this->render('collection_card/index.html.twig', [
            'collection_cards' => $collectionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_collection_card_new", methods={"GET", "POST"})
     */
    public function new(Request $request, CollectionRepository $collectionRepository): Response
    {
        $collectionCard = new CollectionCard();
        $form = $this->createForm(CollectionCardType::class, $collectionCard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $collectionRepository->add($collectionCard);
            return $this->redirectToRoute('app_collection_card_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('collection_card/new.html.twig', [
            'collection_card' => $collectionCard,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/{order}",defaults={"order" = "name"}, name="app_collection_card_show", methods={"GET"})
     */
    public function show(CollectionCard $collectionCard, $order): Response
    {
        return $this->render('collection_card/show.html.twig', [
            'collection_card' => $collectionCard,
            'order'=> $order,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_collection_card_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, CollectionCard $collectionCard, CollectionRepository $collectionRepository): Response
    {
        $form = $this->createForm(CollectionCardType::class, $collectionCard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $collectionRepository->add($collectionCard);
            return $this->redirectToRoute('app_collection_card_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('collection_card/edit.html.twig', [
            'collection_card' => $collectionCard,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_collection_card_delete", methods={"POST"})
     */
    public function delete(Request $request, CollectionCard $collectionCard, CollectionRepository $collectionRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$collectionCard->getId(), $request->request->get('_token'))) {
            $collectionRepository->remove($collectionCard);
        }

        return $this->redirectToRoute('app_collection_card_index', [], Response::HTTP_SEE_OTHER);
    }
}
