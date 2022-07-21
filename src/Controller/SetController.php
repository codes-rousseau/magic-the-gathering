<?php

namespace App\Controller;

use App\Repository\CardRepository;
use App\Repository\SetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SetController extends AbstractController
{
    /**
     * @Route("/", name="sets_list")
     */
    public function index(SetRepository $setRepository): Response
    {
        return $this->render('set/index.html.twig', [
            'sets' => $setRepository->findAll()
        ]);
    }

    /**
     * @Route(name="set_show", path="/show/{id}")
     */
    public function show(CardRepository $cardRepository, SetRepository $setRepository, int $id): Response {
        $set = $setRepository->find($id);
        if($set == null){
            throw new NotFoundHttpException('Ce set n\'existe pas.');
        }
        $criteria['set'] = $id;
        $cards = $cardRepository->findBy($criteria);

        return $this->render('set/show.html.twig', [
            'set' => $set,
            'cards' => $cards
        ]);
    }
}
