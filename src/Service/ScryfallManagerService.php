<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Scryfall\CardDto;
use App\Dto\Scryfall\SetDto;
use App\Entity\Card;
use App\Entity\Color;
use App\Entity\Set;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception as HttpClientException;

class ScryfallManagerService
{
    private ScryfallApiService $scryfallApi;
    private EntityManagerInterface $entityManager;
    private DownloadFileService $downloadFile;

    public function __construct(
        ScryfallApiService $scryfallApi,
        EntityManagerInterface $entityManager,
        DownloadFileService $downloadFile
    ) {
        $this->scryfallApi = $scryfallApi;
        $this->entityManager = $entityManager;
        $this->downloadFile = $downloadFile;
    }

    /**
     * Créer la collection complète en base de données depuis l'API Scryfall :
     * - créer la collection si elle n'existe pas,
     * - créer les cartes de la collection,
     * - télécharger les images des cartes sur le serveur.
     * Si la collection existe déjà avec des cartes :
     * - supprimer les cartes qui ne sont plus présente dans la collection,
     * - supprimer l'image lié à la carte.
     *
     * Note importante :
     * - Si la collection ou la carte existe déjà, on ne l'a créé pas de nouveau
     *   et nous le mettons pas à jour.
     *
     * @throws HttpClientException\TransportExceptionInterface
     * @throws HttpClientException\ServerExceptionInterface
     * @throws HttpClientException\RedirectionExceptionInterface
     * @throws HttpClientException\ClientExceptionInterface
     */
    public function createCompleteSet(SetDto $setDto): void
    {
        $cardRepository = $this->entityManager->getRepository(Card::class);
        $setRepository = $this->entityManager->getRepository(Set::class);
        $set = $setRepository->find($setDto->id);

        if (!$set instanceof Set) {
            // Collection absente de la BDD, nous créons la collection
            $set = $this->transformSetFromApi($setDto);
            $this->entityManager->persist($set);
        }

        // Alimente un tableau qui servira à déterminer s'il faut supprimer
        // des cartes qui ne sont plus présente dans la collection.

        /** @var Card[] $toDelete */
        $toDelete = [];
        foreach ($set->getCards() as $card) {
            $toDelete[$card->getId()->toString()] = $card;
        }

        // Récupère les cartes de la collection
        foreach ($this->scryfallApi->getCardsBySetCode($set->getCode()) as $cardDto) {
            if (array_key_exists($cardDto->id, $toDelete)) {
                // Carte présente
                unset($toDelete[$cardDto->id]);
            }

            $card = $cardRepository->find($cardDto->id);
            if (!$card instanceof Card) {
                // Carte absente de la BDD, nous créons la carte
                $card = $this->transformCardFromApi($cardDto);
            }

            $set->addCard($card);
        }

        foreach ($toDelete as $cardToDelete) {
            if (null !== $cardToDelete->getImageUrl()) {
                // Supprimer l'image qui est lié à cette carte
                $this->downloadFile->removeFileInPublicDirectory($cardToDelete->getImageUrl());
            }

            // Supprimer la carte de la collection
            $set->removeCard($cardToDelete);
        }

        $this->entityManager->flush();
    }

    /**
     * Transforme une collection de l'API Scryfall en une collection entité Doctrine.
     */
    public function transformSetFromApi(SetDto $setDto): Set
    {
        $uuid = Uuid::fromString($setDto->id);
        $releasedAt = (null !== $setDto->released_at)
            ? DateTimeImmutable::createFromFormat('Y-m-d', $setDto->released_at)
            : null;

        return (new Set())
            ->setId($uuid)
            ->setName($setDto->name)
            ->setCode($setDto->code)
            ->setIconUrl($setDto->icon_svg_uri)
            ->setReleasedAt($releasedAt)
        ;
    }

    /**
     * Transforme une carte de l'API Scryfall en une carte entité Doctrine.
     * - Se charge de télécharger l'image sur le serveur.
     */
    public function transformCardFromApi(CardDto $cardDto): Card
    {
        $uuid = Uuid::fromString($cardDto->id);
        $pathPublic = null;
        if (is_array($cardDto->image_uris)) {
            $imageUrl = $cardDto->image_uris['normal'] ?? null;
            $imageUrl = filter_var($imageUrl, FILTER_VALIDATE_URL);

            if (false !== $imageUrl) {
                $pathTemporaryFile = $this->downloadFile->downloadFileByHttpsUrl($imageUrl, $uuid->toString());
                $pathPublic = $this->downloadFile->moveFileInPublicDirectory($pathTemporaryFile, 'cards');
            }
        }

        $card = (new Card())
            ->setId($uuid)
            ->setName($cardDto->name)
            ->setType($cardDto->type_line)
            ->setDescription($cardDto->flavor_text)
            ->setImageUrl($pathPublic)
            ->setArtist($cardDto->artist)
        ;

        $colors = $this->entityManager->getRepository(Color::class);
        foreach ($cardDto->color_identity as $abbreviation) {
            $color = $colors->find($abbreviation);
            if (!$color instanceof Color) {
                throw new RuntimeException(sprintf('Abbreviation "%s" not found in your database.', $abbreviation));
            }

            $card->addColor($color);
        }

        return $card;
    }
}
