<?php

namespace App\Controller;

use App\Form\Type\SearchType;
use App\Repository\CardRepository;
use App\Repository\SetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SetController extends AbstractController
{
    /**
     * @Route("/sets/{id<\d+>}", name="set_show")
     */
    public function show(int $id, SetRepository $setRepository, CardRepository $cardRepository, Request $request): Response
    {
        $set = $setRepository->find($id);
        if (null === $set) {
            throw $this->createNotFoundException("Set $id not found");
        }

        // Création formulaire de recherche
        $form = $this->createForm(SearchType::class);

        // Gestion des retours du formulaire de recherche
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $cards = $cardRepository->findCardsByName($data['name'], $id);
            if (count($cards) > 0) {
                $this->addFlash('success', count($cards).' card(s) found');
                $returnSetId = $id;
            } else {
                $this->addFlash('warning', 'Card not found');
                $cards = $set->getCards();
                $returnSetId = false;
            }
        } else {
            $cards = $set->getCards();
            $returnSetId = false;
        }

        return $this->render('set/show.html.twig', ['cards' => $cards, 'returnSetId' => $returnSetId, 'form' => $form->createView()]);
    }

    /**
     * @Route("/sets", name="set_list")
     */
    public function list(SetRepository $setRepository): Response
    {
        return $this->render('set/list.html.twig', ['sets' => $setRepository->findAll()]);
    }
}
