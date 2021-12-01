<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Client permettant de récupérer des données depuis l'API Scryfall.
 */
class ScryfallApiClient
{
    /**
     * Récupération de la liste des collections sous forme de tableau.
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public static function listSets(): array
    {
        $client = HttpClient::create();

        $response = $client->request('GET', 'https://api.scryfall.com/sets/');
        $responseContent = $response->toArray();
        $sets = $responseContent['data'];

        return $sets;
    }

    /**
     * Récupération de la liste des cartes associées à une collection à partir de son code.
     *
     * @param $setCode
     *
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public static function listCardsBySetCode($setCode): array
    {
        $client = HttpClient::create();

        $response = $client->request('GET', 'https://api.scryfall.com/cards/search', [
            'query' => [
                'q' => 'set:'.$setCode,
                ],
            ]
        );

        $responseContent = $response->toArray();
        $cards = $responseContent['data'];

        // Chargement des cartes suivantes
        while ($responseContent['has_more']) {
            $response = $client->request('GET', $responseContent['next_page']);
            $responseContent = $response->toArray();
            $cards = array_merge($cards, $responseContent['data']);
        }

        // Note : pas de gestion des cartes avec plusieurs faces (ex : https://api.scryfall.com/cards/ca605a6c-a709-4c4d-98e6-81dbfcef12e2)
        foreach ($cards as $key => $card) {
            if (isset($card['card_faces'])) {
                unset($cards[$key]);
            }
        }

        return $cards;
    }
}
