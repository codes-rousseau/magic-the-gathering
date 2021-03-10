<?php

namespace App\Controller;

use App\Entity\Set;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SetController extends AbstractController
{
    /**
     * @Route("/", name="set.list")
     */
    public function index(Request $request): Response
    {
        $search = $request->query->get('q');
        if(!empty($search)) {
            $sets = $this->getDoctrine()->getRepository( Set::class )->findBy(['name' => $search]);
        } else {
            $sets = $this->getDoctrine()->getRepository( Set::class )->findAll();
        }

        return $this->render('set/index.html.twig', [ 'search' => $search, 'sets' => $sets ] );
    }

    /**
     * @Route("/set/{idSet}", name="set.detail")
     * @param int $idSet
     * @return Response
     */
    public function set(int $idSet): Response
    {
        $set = $this->getDoctrine()->getRepository( Set::class )->find($idSet);
        return $this->render('set/detail.html.twig', [ 'set' => $set ] );
    }
}
