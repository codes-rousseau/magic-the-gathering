<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Scryfall\Model\CardDto;
use App\Dto\Scryfall\Model\CardListDto;
use App\Dto\Scryfall\Model\SetDto;
use App\Dto\Scryfall\Model\SetListDto;
use App\Dto\Scryfall\Transformer\CardDtoTransformer;
use App\Dto\Scryfall\Transformer\SetDtoTransformer;
use App\Entity\Card;
use App\Entity\Set;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception as HttpClientException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Transliterator;

class ScryfallService
{
    private HttpClientInterface $httpClient;
    private SerializerInterface $serializer;
    private EntityManagerInterface $entityManager;
    private SetDtoTransformer $setDtoTransformer;
    private CardDtoTransformer $cardDtoTransformer;
    private string $apiBaseUrl;

    /**
     * Voir la section "Rate Limites" de la documentation : https://scryfall.com/docs/api
     * Nous devons attendre 100ms entre chaque requête.
     */
    private int $intervalTimeRequest;

    public function __construct(
        HttpClientInterface $httpClient,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        SetDtoTransformer $setDtoTransformer,
        CardDtoTransformer $cardDtoTransformer,
        string $apiBaseUrl,
        int $intervalTimeRequest
    ) {
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->setDtoTransformer = $setDtoTransformer;
        $this->cardDtoTransformer = $cardDtoTransformer;
        $this->apiBaseUrl = $apiBaseUrl;
        $this->intervalTimeRequest = $intervalTimeRequest;
    }

    /**
     * Récupère la liste des collection sur l'API.
     *
     * @return iterable<SetDto>
     *
     *@throws HttpClientException\ServerExceptionInterface
     * @throws HttpClientException\RedirectionExceptionInterface
     * @throws HttpClientException\ClientExceptionInterface
     * @throws HttpClientException\TransportExceptionInterface
     */
    public function getSets(): iterable
    {
        $path = $this->apiBaseUrl.'/sets';
        do {
            $response = $this->httpClient->request('GET', $path, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            if (Response::HTTP_OK !== $response->getStatusCode()) {
                throw new \RuntimeException('Response status code is not the expected.');
            }

            /** @var SetListDto $listSet */
            $listSet = $this->serializer->deserialize(
                $response->getContent(),
                SetListDto::class,
                'json'
            );

            foreach ($listSet->data as $dataSet) {
                yield $dataSet;
            }

            // Il est possible qu'il y ait plusieurs pages d'après la documentation.
            // Si c'est le cas, alors nous continuons sur la page suivante.
            $path = $listSet->nextPage ?? null;

            // Pour respecter les règles "Rate Limites" de l'API
            usleep($this->intervalTimeRequest);
        } while (true === $listSet->hasMore && null !== $path);
    }

    /**
     * Récupère une liste de collection correspondant au nom passé en paramètre.
     *
     * @throws HttpClientException\TransportExceptionInterface
     * @throws HttpClientException\ServerExceptionInterface
     * @throws HttpClientException\RedirectionExceptionInterface
     * @throws HttpClientException\ClientExceptionInterface
     *
     * @return iterable<SetDto>
     */
    public function getSetsByName(string $setName): iterable
    {
        $setName = $this->cleanName($setName);
        $list = [];

        foreach ($this->getSets() as $setInput) {
            if (false !== strpos($this->cleanName($setInput->name), $setName)) {
                $list[] = $setInput;
            }
        }

        return $list;
    }

    /**
     * Récupère une liste de carte appartenant à une collection à partir du code de collection.
     *
     * @throws HttpClientException\ServerExceptionInterface
     * @throws HttpClientException\RedirectionExceptionInterface
     * @throws HttpClientException\ClientExceptionInterface
     * @throws HttpClientException\TransportExceptionInterface
     *
     * @return iterable<CardDto>
     */
    public function getCardsBySetCode(string $setCode): iterable
    {
        $path = $this->apiBaseUrl.sprintf('/cards/search?q=set:%s', $setCode);

        do {
            $response = $this->httpClient->request('GET', $path, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            if (Response::HTTP_OK !== $response->getStatusCode()) {
                throw new RuntimeException('Response status code is not the expected.');
            }

            /** @var CardListDto $listCard */
            $listCard = $this->serializer->deserialize(
                $response->getContent(),
                CardListDto::class,
                'json'
            );

            foreach ($listCard->data as $dataSet) {
                yield $dataSet;
            }

            // Il est possible qu'il y ait plusieurs pages d'après la documentation.
            // Si c'est le cas, alors nous continuons sur la page suivante.
            $path = $listCard->nextPage ?? null;

            // Pour respecter les règles "Rate Limites" de l'API
            usleep($this->intervalTimeRequest);
        } while (true === $listCard->hasMore && null !== $path);
    }

    public function createSet(SetDto $setDto)
    {
        $cardRepository = $this->entityManager->getRepository(Card::class);
        $setRepository = $this->entityManager->getRepository(Set::class);
        $set = $setRepository->find($setDto->id);

        if (!$set instanceof Set) {
            // Collection absente de la BDD, nous créons la collection
            $set = $this->setDtoTransformer->transform($setDto);
            $this->entityManager->persist($set);
        }

        // Alimente un tableau qui servira à déterminer s'il faut supprimer
        // des cartes qui ne sont plus présente dans la collection.
        $toDelete = [];
        foreach ($set->getCards() as $card) {
            $toDelete[$card->getId()->toString()] = $card;
        }

        // Récupère les cartes de la collection
        foreach ($this->getCardsBySetCode($set->getCode()) as $cardDto) {
            if (array_key_exists($cardDto->id, $toDelete)) {
                // Carte présente
                unset($toDelete[$cardDto->id]);
            }

            // Création de la carte et ajout de celle-ci
            $card = $cardRepository->find($cardDto->id);
            if (!$card instanceof Card) {
                // Carte absente de la BDD, nous créons la carte
                $card = $this->cardDtoTransformer->transform($cardDto);
            }

            $set->addCard($card);
        }

        foreach ($toDelete as $cardToDelete) {
            // Suppression de la carte de la collection
            $set->removeCard($cardToDelete);
        }

        $this->entityManager->flush();
    }

    private function cleanName(string $name): string
    {
        $transliterator = Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC; Upper');
        if ($transliterator instanceof Transliterator) {
            return $transliterator->transliterate($name);
        }

        throw new RuntimeException(sprintf('Unable to transliterate this word: %s', $name));
    }
}
