<?php

namespace App\Service;

use App\Entity\Card;
use App\Entity\Set;
use App\Exception\AlreadyExistsException;
use App\Exception\NoCardFoundException;
use App\Exception\NoSetFoundException;
use App\Exception\SetNotExistingException;
use App\Repository\SetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use DateTime;

class GetCollectionService
{
    public const CARD_IMAGE_BASE_URL = '/public/cards/';
    public const COLLECTION_IMAGE_BASE_URL = '/public/sets/';
    public const SETS_ENDPOINT = '/sets';

    private string $projectDirectory;

    private string $apiBaseUrl;

    private HttpClientInterface $httpClient;

    private EntityManagerInterface $entityManager;

    private SetRepository $setRepository;

    public function __construct(
        string $projectDirectory,
        string $apiBaseUrl,
        HttpClientInterface $client,
        EntityManagerInterface $entityManager,
        SetRepository $setRepository
    ) {
        $this->projectDirectory = $projectDirectory;
        $this->apiBaseUrl = $apiBaseUrl;
        $this->httpClient = $client;
        $this->entityManager = $entityManager;
        $this->setRepository = $setRepository;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws AlreadyExistsException
     * @throws NoCardFoundException
     * @throws SetNotExistingException
     * @throws Exception
     */
    public function getCollectionByName(string $collectionName): void
    {
        $stored = $this->setRepository->findBy(['name' => $collectionName]);

        if ($stored instanceof Set) {
            throw new AlreadyExistsException();
        }

        $collections = $this->getCollections();

        if (empty($collections['data'])) {
            throw new NoCardFoundException();
        }

        $collectionExists = false;

        foreach ($collections['data'] as $collection) {
            if ($collection['name'] === $collectionName) {
                $collectionExists = true;

                $icon = file_get_contents($collection['icon_svg_uri']);

                $pathToIcon = $this->projectDirectory . self::COLLECTION_IMAGE_BASE_URL . $collection['code'] . '.svg';

                file_put_contents($pathToIcon, $icon);

                $set = (new Set())
                    ->setCode($collection['code'])
                    ->setName($collectionName)
                    ->setIcon($collection['code']);

                if (!empty($collection['released_at'])) {
                    $set->setReleasedAt(new DateTime($collection['released_at']));
                }

                $this->entityManager->persist($set);

                $this->entityManager->flush();

                $this->addCardsToDatabase($set, $collection);
            }
        }

        if (!$collectionExists) {
            throw new SetNotExistingException();
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws NoSetFoundException
     */
    public function getAllCollectionsAvailable(): array
    {
        $collections = $this->getCollections();

        if (empty($collections['data'])) {
            throw new NoSetFoundException();
        }

        $collectionNames = [];

        foreach ($collections['data'] as $collection) {
            $collectionNames[] = $collection['name'];
        }

        return $collectionNames;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function getCollections(): array
    {
        $response = $this->httpClient->request('GET', $this->apiBaseUrl . self::SETS_ENDPOINT);

        return $response->toArray();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function addCardsToDatabase(Set $set, array $collection): void
    {
        $response = $this->httpClient->request('GET', $collection['search_uri']);

        $results = $response->toArray();

        foreach ($results['data'] as $result) {
            $card = (new Card())
                ->setName($result['name'])
                ->setSet($set)
                ->setImage($result['id'])
                ->setColor($this->parseColors($result['color_identity']))
                ->setType($result['type_line']);

            if (!empty($result['artist'])) {
                $card->setArtist($result['artist']);
            }

            if (!empty($result['flavor_text'])) {
                $card->setDescription($result['flavor_text']);
            }

            if (!empty($result['image_uris']['png'])) {
                $image = file_get_contents($result['image_uris']['png']);

                $pathToImage = $this->projectDirectory . self::CARD_IMAGE_BASE_URL . $card->getImage() . '.png';

                file_put_contents($pathToImage, $image);
            }

            $this->entityManager->persist($card);
        }

        $this->entityManager->flush();
    }

    private function parseColors(array $colors): string
    {
        $result = '';

        foreach ($colors as $color) {
            $result .= $color;
        }

        return $result;
    }
}