<?php


namespace App\Service;


use App\Entity\Card;
use App\Entity\CollectionCard;
use App\Entity\Color;
use App\Entity\Type;
use App\Repository\CollectionRepository;
use App\Repository\ColorRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;

class GetCardCollectionService
{

    private $em;
    private $collectionName;
    private $client;
    private $typeRepository;
    private $colorRepository;
    private $collectionRepository;
    private $uploadImgService;

    public function __construct(EntityManagerInterface $em, TypeRepository $typeRepository, ColorRepository $colorRepository, CollectionRepository $collectionRepository, UploadImgService $uploadImgService)
    {
        $this->em = $em;
        $this->typeRepository = $typeRepository;
        $this->colorRepository = $colorRepository;
        $this->collectionRepository = $collectionRepository;
        $this->uploadImgService = $uploadImgService;

        if (empty($this->colorRepository->findAll())) {
            $this->SetColor();
        }

        $this->client = HttpClient::create();
    }

    /*
    * Insert the collection into the database, fill in at the command.
    */

    public function getCard($collectionName): string
    {
        $this->collectionName = $collectionName;

        $isCollectionInBDD = $this->collectionRepository->findBy(['name' => $collectionName]);

        if (!empty($isCollectionInBDD)) {
            return "The collection is already in the database";
        }


        $response = $this->client->request('GET', 'https://api.scryfall.com/sets');
        $collections = $response->toArray();

        $key = $this->searchForName($collectionName, $collections['data']);

        if ($key !== null) {
            $collection = new CollectionCard();
            $collection->setCode($collections['data'][$key]['code']);
            $collection->setCardCount($collections['data'][$key]['card_count']);
            $collection->setName($collections['data'][$key]['name']);
            $collection->setSearchUri($collections['data'][$key]['search_uri']);

            $stringDate = strtotime($collections['data'][$key]['released_at']);
            $date = new \DateTime();
            $collection->setReleaseDate($date->setTimestamp($stringDate));
            $collection->setSvg($collections['data'][$key]['icon_svg_uri']);
            $this->em->persist($collection);

            $this->insertCardBDD($collections['data'][$key]['search_uri'], $collection);
        } else {
            return "The collection doesnt exist";
        }

        $this->em->flush();
        return "OK";

    }

    /*
    * Insert the cards into the database according to the collection, fill in at the command.
    */

    private function insertCardBDD($searchUri, CollectionCard $collectionCard)
    {

        $response = $this->client->request('GET', $searchUri);
        $cards = $response->toArray();
        foreach ($cards['data'] as $value) {
            $card = new Card();
            $card->setName($value['name']);
            $card->setCollection($collectionCard);

            $card->setImage($this->uploadImgService->uploadAsset($value['image_uris']['png']));

            $card->setArtistName($value['artist']);

            if (!empty($value['flavor_text'])) {
                $card->setDescription($value['flavor_text']);
            }

            $type = $this->checkType($value['type_line']);
            $card->setTypeLine($type);

            foreach ($value['color_identity'] as $color) {
                $card->addColor($this->checkColor($color));
            }


            $this->em->persist($card);

        }
        $this->em->flush();
    }

    /*
    * Insert the type relation in database for filtering.
    */

    private function checkType($typeName)
    {
        if (!empty($this->typeRepository->findBy(["name" => $typeName]))) {
            $type = $this->typeRepository->findOneBy(["name" => $typeName]);
        } else {
            $type = new Type();
            $type->setName($typeName);
            $this->em->persist($type);
            $this->em->flush();
        }
        return $type;
    }

    /*
    * Insert the color relation in database for filtering.
    */

    private function checkColor($ColorName)
    {
        $color = $this->colorRepository->findOneBy(["code" => $ColorName]);
        return $color;
    }

    /*
     * Add the colors present in the different collection of cards in the database.
     */

    private function SetColor()
    {
        $colorWhite = new Color();
        $colorWhite->setName("White");
        $colorWhite->setCode('W');
        $this->em->persist($colorWhite);

        $colorBlue = new Color();
        $colorBlue->setName("Blue");
        $colorBlue->setCode('U');
        $this->em->persist($colorBlue);

        $colorBlack = new Color();
        $colorBlack->setName("Black");
        $colorBlack->setCode('B');
        $this->em->persist($colorBlack);

        $colorRed = new Color();
        $colorRed->setName("Red");
        $colorRed->setCode('R');
        $this->em->persist($colorRed);

        $colorGreen = new Color();
        $colorGreen->setName("Green");
        $colorGreen->setCode('G');
        $this->em->persist($colorGreen);

        $this->em->flush();
    }

    /*
    * Get all the collection names to display them in the command help list.
     */

    public function getCollection()
    {
        $response = $this->client->request('GET', 'https://api.scryfall.com/sets');
        $collections = $response->toArray();
        $tabCollection = [];
        foreach ($collections['data'] as $value) {
            array_push($tabCollection, $value['name']);
        }
        return $tabCollection;

    }

    public function searchForName($name, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['name'] === $name) {
                return $key;
            }
        }
        return null;
    }

}