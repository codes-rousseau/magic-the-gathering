<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Color;
use App\Entity\Set;
use App\Entity\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SetController extends AbstractController
{
    /**
     * @Route("/", name="set.list")
     * @param Request $request
     * @return Response
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
     * @param Request $request
     * @param int $idSet
     * @return Response
     */
    public function set(Request $request, int $idSet): Response
    {
        $filter = [
            'name'   => $request->query->get('name'),
            'colors' => $request->query->get('colors'),
            'type'   => $request->query->get('type')
        ];

        $set = $this->getDoctrine()->getRepository( Set::class )->find($idSet);
        $colors = $this->getDoctrine()->getRepository(Color::class)->findAll();
        $types = $this->getDoctrine()->getRepository(Type::class)->findAllInSet($idSet);

        $filteredCards = $this->getDoctrine()->getRepository( Card::class )->findWithFilter($filter, $idSet);

        return $this->render('set/detail.html.twig', [
            'filter' => $filter,
            'set' => $set,
            'colors' => $colors,
            'types' => $types,
            'filteredCards' => $filteredCards
        ] );
    }
}
