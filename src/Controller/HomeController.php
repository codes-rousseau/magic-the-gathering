<?php

namespace App\Controller;

use App\Repository\CollectionsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(CollectionsRepository $collectionsRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'collections' => $collectionsRepository->findAll(),
        ]);
    }
}
