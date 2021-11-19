<?php

namespace App\Api;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CollectionApi
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCollections(): array
    {
        $response = $this->client->request(
            'GET',
            'https://api.scryfall.com/sets'
        );

        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = $response->toArray();

        return $content["data"];
    }
}