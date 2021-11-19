<?php

namespace App\Api;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CarteApi
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCartesCollection(string $codeCollection): array
    {
        $response = $this->client->request(
            'GET',
            'https://api.scryfall.com/cards/search?q=set%3D' . $codeCollection
        );

        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = $response->toArray();

        return $content["data"];
    }
}