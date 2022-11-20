<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CollectionsRepository;

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
    public function viewCollection(String $code, CollectionsRepository $collectionsRepository): Response
    {
        $collection = $collectionsRepository->findOneBy(['code' => $code]);

        if( !is_null($collection)) {
            return $this->render('collections/view.html.twig', [
                'collection' => $collection,
            ]);
        } else {
            throw $this->createNotFoundException('The collection does not exist');

        }
    }
}
