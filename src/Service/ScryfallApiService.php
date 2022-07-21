<?php

namespace App\Service;

use App\Entity\Card;
use App\Entity\Color;
use App\Entity\Set;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class ScryfallApiService implements MagicApiServiceInterface
{
    private const API_URL = 'https://api.scryfall.com/';

    private HttpClientInterface $httpClient;
    private EntityManagerInterface $em;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $entityManagerInterface)
    {
        $this->httpClient = $httpClient;
        $this->em = $entityManagerInterface;
    }

    /**
     * Récupère tous les sets correspondants au nom en paramètre.
     * @return \App\Entity\Set[]
     */
    public function getSetsByName(string $setName): array
    {
        $result = [];
        $allSetsData = $this->getAllSets();
        foreach ($allSetsData['data'] as $data) {
            if (strpos(strtolower($data['name']), strtolower($setName)) !== false) {
                $set = new Set();
                $set->setName($data['name']);
                $set->setCode($data['code']);
                $set->setReleasedAt(new DateTimeImmutable($data['released_at']));
                $set->setIcon($data['code'] . '.svg');
                $set->setIconSvgURI($data['icon_svg_uri']);
                $result[] = $set;
            }
        }
        return $result;
    }

    /**
     * Récupère toutes les cartes d'un Set.
     * @return \App\Entity\Card[]
     */
    public function getCardsBySet(Set $set): array
    {
        $response = $this->GetRequest('/cards/search?q=set:' . $set->getCode());
        $cardArr = $response->toArray();

        $result = [];
        foreach ($cardArr['data'] as $c) {
            $card = new Card();
            $card->setName($c['name']);
            $card->setArtist($c['artist']);
            $card->setType($c['type_line']);
            if (array_key_exists('flavor_text', $c)) {
                $card->setDescription($c['flavor_text']);
            }
            $card->setImage($c['id'] . '.png');
            $card->setPngURI($c['image_uris']['png']);

            // Attribution des couleurs
            $colorRepository = $this->em->getRepository(Color::class);
            foreach ($c['colors'] as $color) {
                $dbColor = $colorRepository->findOneBy(['abbr' => $color]);
                if ($dbColor != null) {
                    $card->addColor($dbColor);
                }
            }

            $result[] = $card;
        }
        return $result;
    }

    /**
     * Récupère tous les sets.
     * @return array
     */
    public function getAllSets(): array
    {
        $response = $this->GetRequest('sets');
        return $response->toArray();
    }

    /**
     * Effectue une requête GET via l'API Scryfall.
     */
    private function GetRequest(string $endpoint): ResponseInterface
    {
        $response = $this->httpClient->request('GET', self::API_URL . $endpoint, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        if ($response->getStatusCode() != Response::HTTP_OK) {
            throw new RuntimeException('Impossible d\'accéder à l\'API Scryfall.');
        }
        return $response;
    }
}
