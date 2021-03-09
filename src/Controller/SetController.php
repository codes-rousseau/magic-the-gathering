<?php

namespace App\Controller;

use App\Entity\Set;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SetController extends AbstractController
{
    /**
     * @Route("/", name="set")
     */
    public function index(): Response
    {
        $sets = $this->getDoctrine()->getRepository( Set::class )->findAll();
        return $this->render('set/index.html.twig', [ 'sets' => $sets ] );
    }
}
