<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Set;
use App\Form\SearchCardType;
use App\Repository\CardRepository;
use App\Repository\SetRepository;
use Doctrine\ORM\NonUniqueResultException;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SetController extends AbstractController
{
    private SetRepository $sets;

    public function __construct(SetRepository $sets)
    {
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
     *
     * @throws NonUniqueResultException
     */
    public function showAction(
        CardRepository $cards,
        Request $request,
        string $id
    ): Response {
        $uuid = Uuid::fromString($id);
        $form = $this->createForm(SearchCardType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Charge le set sans charger les cartes pour des questions de performance.
            /** @var ?Set $set */
            $set = $this->sets->find($uuid);

            if (!$set instanceof Set) {
                throw new NotFoundHttpException(sprintf('Not found this set.'));
            }

            $criteria = $form->getData();
            $criteria['set_id'] = $set->getId();

            // Recherche les cartes selon des critères / filtres
            $cards = $cards->findAllByCriteria($criteria);
        } else {
            // Charge le set en réalisant les jointures nécessaire pour des questions de perfomance.
            /** @var Set $set */
            $set = $this->sets->findWithAllJoin($uuid);
            if (!$set instanceof Set) {
                throw new NotFoundHttpException(sprintf('Not found this set.'));
            }

            $cards = $set->getCards();
        }

        return $this->render('set/show.html.twig', [
            'form' => $form->createView(),
            'set' => $set,
            'cards' => $cards,
        ]);
    }
}
