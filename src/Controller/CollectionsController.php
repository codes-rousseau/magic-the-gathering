<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Collections;
use App\Entity\Carte;

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
    public function listCartesCollectionAction(int $id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $collection = $em->getRepository(Collections::class)->find($id);
        $cartes = $em->getRepository(Carte::class)->findBy([
            'collection' => $collection
        ]);
        
        return $this->render('collections/cartes.html.twig', [
            'collection' => $collection,
            'cartes' => $cartes
        ]);
    }
}
