<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Set;
use App\Repository\SetRepository;
use App\Service\ScryfallService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SetController extends AbstractController
{
    private ScryfallService $scryfall;
    private SetRepository $sets;

    public function __construct(ScryfallService $scryfall, SetRepository $sets)
    {
        $this->scryfall = $scryfall;
        $this->sets = $sets;
    }

    /**
     * @Route(methods={"GET"}, name="set_list", path="/list")
     */
    public function listAction(): Response
    {
        return $this->render('set/list.html.twig', ['sets' => $this->sets->findAll()]);
    }

    /**
     * @Route(methods={"GET", "POST"}, name="set_show", path="/show/{id}")
     */
    public function showAction(Set $set): Response
    {
        return $this->render('set/show.html.twig', ['set' => $set]);
    }
}
