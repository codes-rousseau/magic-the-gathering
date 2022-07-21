<?php

namespace App\Service;

use App\Entity\Card;
use App\Entity\Set;
use Doctrine\ORM\EntityManagerInterface;

class CardService
{
    private const CARD_IMAGE_URL = '/public/cards/';

    private EntityManagerInterface $em;
    private string $projectDir;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $projectDir
    )
    {
        $this->em = $entityManager;
        $this->projectDir = $projectDir;
    }

    /**
     * Ajoute une carte en base de données.
     */
    public function createCard(Card $card, Set $set): void
    {
        $imageFile = file_get_contents($card->getPngURI());
        $imagePath = $this->projectDir . self::CARD_IMAGE_URL . $card->getImage();
        file_put_contents($imagePath, $imageFile);

        $card->setSet($set);
        $this->em->persist($card);
    }

}
