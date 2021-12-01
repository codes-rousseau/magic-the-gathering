<?php

namespace App\Controller;

use App\Repository\SetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SetController extends AbstractController
{
    /**
     * @Route("/sets/{id<\d+>}", name="set_show")
     */
    public function show(int $id, SetRepository $setRepository): Response
    {
        $set = $setRepository->find($id);
        if (null === $set) {
            throw $this->createNotFoundException("Set $id not found");
        }

        return $this->render('set/show.html.twig', ['set' => $set]);
    }

    /**
     * @Route("/sets", name="set_list")
     */
    public function list(SetRepository $setRepository): Response
    {
        return $this->render('set/list.html.twig', ['sets' => $setRepository->findAll()]);
    }
}
