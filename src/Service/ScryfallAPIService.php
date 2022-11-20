<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ScryfallAPIService
{
    private const BASE_API = 'https://api.scryfall.com';

    private HttpClientInterface $client;
    private EntityManager $em;

    public function __construct( ) {
        $this->client = HttpClient::create();
    }

    /**
     * @return Object
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getCollections(): Object {

        $request_collection = $this->client->request(
            'GET',
            self::BASE_API . '/sets'
        );

        if( $request_collection->getStatusCode() != 200 ) {
            return [];
        } else {
            return json_decode($request_collection->getContent());
        }
    }

    /**
     * @param String $code
     * @return Object
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getCards(String $code): Object {
        $request_cards = $this->client->request(
            'GET',
            self::BASE_API . '/cards/search?q=set:' . $code
        );

        if( $request_cards->getStatusCode() != 200 ) {
            return [];
        } else {
            return json_decode($request_cards->getContent());
        }
    }

}