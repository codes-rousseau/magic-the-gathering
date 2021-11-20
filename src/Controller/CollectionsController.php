<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Collections;
use App\Entity\Carte;
use App\Form\SearchCarteType;
use Symfony\Component\HttpFoundation\Request;

class CollectionsController extends AbstractController
{
    /**
     * @Route("/collections", name="collections")
     */
    public function listCollectionsAction(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $collections = $em->getRepository(Collections::class)->findAll();
        return $this->render('collections/collections.html.twig', [
            'collections' => $collections
        ]);
    }

    /**
     * @Route("/cartes/collection/{id}", name="cartes_collection")
     */
    public function listCartesCollectionAction(int $id, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $collection = $em->getRepository(Collections::class)->find($id);
        $cartes = $em->getRepository(Carte::class)->findBy([
            'collection' => $collection
        ]);

        //Partie formulaire de recherche
        $form = $this->createForm(SearchCarteType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
           $formData = $form->getData();
           $selectedCartes = $em->getRepository(Carte::class)->findArticlesByName($formData['recherche'], $formData['optionRecherche'], $id);
           
           return $this->render('collections/cartes.html.twig', [
            'collection' => $collection,
            'cartes' => $selectedCartes,
            'form' => $form->createView()
            ]
           );
        }

        return $this->render('collections/cartes.html.twig', [
            'collection' => $collection,
            'cartes' => $cartes,
            'form' => $form->createView()
        ]);
    }
}
