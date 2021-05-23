<?php

namespace App\Controller;

use App\Repository\CollectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(CollectionRepository $collectionRepository): Response
    {

        $collections = $collectionRepository->findAll();
        return $this->render('home/index.html.twig', [
            'collections' => $collections,
        ]);
    }
}
