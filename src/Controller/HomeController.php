<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route(methods={"GET"}, name="home_index", path="/")
     */
    public function indexAction(): Response
    {
        return $this->render('base.html.twig');
    }
}
