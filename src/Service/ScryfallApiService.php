<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Scryfall\CardDto;
use App\Dto\Scryfall\CardListDto;
use App\Dto\Scryfall\SetDto;
use App\Dto\Scryfall\SetListDto;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception as HttpClientException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Transliterator;

class ScryfallApiService
{
    private HttpClientInterface $httpClient;
    private SerializerInterface $serializer;
    private string $apiBaseUrl;

    /**
     * Voir la section "Rate Limites" de la documentation : https://scryfall.com/docs/api
     * Nous devons attendre 100ms entre chaque requête.
     */
    private int $intervalTimeRequest;

    public function __construct(
        HttpClientInterface $httpClient,
        SerializerInterface $serializer,
        string $apiBaseUrl,
        int $intervalTimeRequest
    ) {
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
        $this->apiBaseUrl = $apiBaseUrl;
        $this->intervalTimeRequest = $intervalTimeRequest;
    }

    /**
     * Récupère la liste des collection sur l'API.
     *
     * @return iterable<SetDto>
     *
     * @throws HttpClientException\ServerExceptionInterface
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
                throw new RuntimeException('Response status code is not the expected.');
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

    /**
     * Supprime les caractères accentués et transforme la chaîne en majuscule.
     */
    private function cleanName(string $name): string
    {
        $transliterator = Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC; Upper');
        if ($transliterator instanceof Transliterator) {
            return $transliterator->transliterate($name);
        }

        throw new RuntimeException(sprintf('Unable to transliterate this word: %s', $name));
    }
}
