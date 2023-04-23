<?php
namespace App\Controller;
use App\Entity\CardSet;
use App\Form\CardFilterType;
use App\Service\CardServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(name="card_set_")
 */
class CardSetController extends AbstractController
{
    protected CardServiceInterface $cardService;
    protected TranslatorInterface $translator;

    public function __construct(CardServiceInterface $cardService, TranslatorInterface $translator) {
        $this->cardService = $cardService;
        $this->translator = $translator;
    }
    /**
     * @Route(path="/", name="index")
     */
    public function index(): Response
    {
        $cardSets = $this->cardService->getAllCardSets();
        return $this->render('card/cardSetList.html.twig', ['cardSets' => $cardSets]);
    }

    /**
     * @Route(path="/{id}/cards", name="cards")
     */
    public function cards(CardSet $set, Request $request): Response
    {
        $colors = $this->cardService->getColors();
        $colorOptions = [];
        foreach($colors as $color) {
            $colorOptions[$color->getId()] = $this->translator->trans('colors.'.strtolower($color->getCode()), [], 'colors' );
        }
        $types = $this->cardService->getTypesForSet($set);
        asort($types);
        $options = [
            'options' => [
                'color' => array_flip($colorOptions),
                'type' => array_flip($types)
            ]
        ];
        $formFilter = $this->createForm(CardFilterType::class, null, $options);

        $formFilter->handleRequest($request);
        $filters = [];
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $filters = $formFilter->getData();
        }
        $cards = $this->cardService->getAllCards($set, $filters);

        return $this->render('card/cardList.html.twig', ['set' => $set, 'cards' => $cards, 'formFilter' => $formFilter->createView()]);
    }



}