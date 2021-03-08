<?php
namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ScryfallApi
{

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var array
     */
    private $config;

    public function __construct(HttpClientInterface $client, ContainerInterface $container)
    {
        $this->client = $client;
        $this->config = $container->getParameter('scryfall-api');
    }

    /**
     * Appel l'API
     *
     * @param string $route
     * @param string $methode
     *
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    private function _call(string $route, $methode = 'GET') {
        // Compose l'url de l'api en fonction de si on utilise une route relative ou une url absolue
        if(strpos($route,'://')!==false){
            $url = $route;
        } else {
            $url = $this->config['host'] . $route;
        }

        $response = $this->client->request($methode, $url, [
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \Exception('...');
        }

        return json_decode($response->getContent(), true);
    }

    /**
     * Récupère la liste des collections
     *
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function getSets() {
        return $this->_call('/sets', 'GET');
    }

    /**
     * Récupère une liste de carte selon une url de recherche
     *
     * @param $search_uri
     */
    public function searchCards($search_uri) {
        return $this->_call($search_uri, 'GET');
    }
}
