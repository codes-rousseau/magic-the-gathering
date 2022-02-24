<?php

namespace App\Services;

use Exception;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ScryFall
{
    private HttpClientInterface $client;

    public function __construct()
    {
        $this->client = HttpClient::create();
    }

    /**
     * Get Collection(s).
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function getData($uri): array
    {
        $response = $this->client->request(
            'GET',
            $uri
        );

        return $response->toArray()['data'];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getCollections(string $collection_name = null)
    {
        $collections = $this->getData('https://api.scryfall.com/sets');

        if (!empty($collections)) {
            foreach ($collections as $collection) {
                if ($collection['name'] === $collection_name) {
                    return $collection;
                }
            }
        }

        return $collections;
    }

    /**
     * Get Card(s).
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function getCards(array $collection, $card_name = null)
    {
        if (!isset($collection['search_uri'])) {
            throw new Exception('Invalid Collection');
        }

        $cards = $this->getData($collection['search_uri']);

        if (!empty($card)) {
            foreach ($cards as $card) {
                if ($card['name'] === $card_name) {
                    return $card;
                }
            }
        }

        return $cards;
    }
}
