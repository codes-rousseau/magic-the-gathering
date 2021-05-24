<?php


namespace App\Service;


use App\Entity\Card;
use App\Entity\CollectionCard;
use App\Entity\Color;
use App\Entity\Type;
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

    public function __construct(EntityManagerInterface $em, TypeRepository $typeRepository, ColorRepository $colorRepository)
    {
        $this->em = $em;
        $this->typeRepository = $typeRepository;
        $this->colorRepository = $colorRepository;

        if (empty($this->colorRepository->findAll())) {
            $this->SetColor();
        }

        $this->client = HttpClient::create();
    }

    /*
    * Insert the collection into the database, fill in at the command.
    */

    public function getCard($collectionName)
    {
        $this->collectionName = $collectionName;


        $response = $this->client->request('GET', 'https://api.scryfall.com/sets');
        $collections = $response->toArray();
        foreach ($collections['data'] as $value) {
            if ($value['name'] === $collectionName) {
                $collection = new CollectionCard();
                $collection->setCode($value['code']);
                $collection->setCardCount($value['card_count']);
                $collection->setName($value['name']);
                $collection->setSearchUri($value['search_uri']);
                $this->em->persist($collection);

                $this->insertCardBDD($value['search_uri'], $collection);
            }
        }
        $this->em->flush();

    }

    /*
    * Insert the cards into the database according to the collection, fill in at the command.
    */

    private function insertCardBDD($searchUri, CollectionCard $collection)
    {
        $response = $this->client->request('GET', $searchUri);
        $cards = $response->toArray();
        foreach ($cards['data'] as $value) {
            $card = new Card();
            $card->setName($value['name']);
            $card->setCollection($collection);

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

        if ($this->typeRepository->findBy(["name" => $typeName])) {
            $type = $this->typeRepository->findBy(["name" => $typeName]);
        } else {
            $type = new Type();
            $type->setName($typeName);
            $this->em->persist($type);
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

}