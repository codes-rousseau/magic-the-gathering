<?php

namespace App\Controller;

use App\Form\CardFilterType;
use App\Repository\CardRepository;
use App\Repository\SetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SetController extends AbstractController
{
    /**
     * Affichage de la liste des collections.
     * @Route("/", name="set_list")
     */
    public function index(SetRepository $setRepository): Response
    {
        return $this->render('set/index.html.twig', [
            'sets' => $setRepository->findAll()
        ]);
    }

    /**
     * Affichage des cartes d'une collection.
     * @Route(name="set_show", path="/set/{id}")
     */
    public function show(
        CardRepository $cardRepository,
        SetRepository $setRepository,
        Request $request,
        int $id
    ): Response {
        $set = $setRepository->find($id);
        if ($set == null) {
            throw new NotFoundHttpException('Ce set n\'existe pas.');
        }

        $form = $this->createForm(CardFilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if($data['name'] != null){
                $criteria['name'] = $data['name'];
            }
            if($data['colors'] != null && count($data['colors']) > 0){
                $criteria['colors'] = $data['colors'];
            }
            if($data['type'] != null){
                $criteria['type'] = $data['type'];
            }
        }
        $criteria['set'] = $id;

        $cards = $cardRepository->applyFilter($criteria);

        return $this->render('set/show.html.twig', [
            'set' => $set,
            'cards' => $cards,
            'form' => $form->createView(),
        ]);
    }
}
