<?php

namespace App\Controller\Pages;

use App\Form\CardsType;
use App\Repository\CardRepository;
use App\Repository\SetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CollectionController extends AbstractController
{
    private array $colorsMapping;

    public function __construct(array $colorsMapping)
    {
        $this->colorsMapping = $colorsMapping;
    }

    /**
     * @Route("/collection", name="collection")
     */
    public function index(SetRepository $setRepository): Response
    {
        return $this->render('pages/index.html.twig', [
            'sets' => $setRepository->findAll()
        ]);
    }

    /**
     * @Route("/collection/{id}", name="collection_detail")
     */
    public function collection(CardRepository $cardRepository, Request $request, int $id): Response
    {
        $cards = $cardRepository->filter($id);

        $typesAvailable = array_values($cardRepository->getTypesAvailable($id));

        $options = [
            'data' => [
                'color_options' => array_flip($this->colorsMapping),
                'type_options' => array_combine($typesAvailable, $typesAvailable)
            ]
        ];

        $form = $this->createForm(CardsType::class, null, $options);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $name = empty($data['name']) ? null : $data['name'];
            $color = empty($data['color']) ? null : $data['color'];
            $type = empty($data['type']) ? null : $data['type'];

            $cards = $cardRepository->filter($id, $name, $color, $type);
        }

        return $this->render('pages/collection.html.twig', [
            'cards' => $cards,
            'form' => $form->createView()
        ]);
    }
}
