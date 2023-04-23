<?php
namespace App\Service;

use App\Entity\CardSet;
use App\Entity\Card;
use App\Entity\Type;
use App\Entity\Color;
use App\Service\Provider\CardProviderInterface;
use App\Dto\SetDto;
use App\Dto\CardDto;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CardService implements CardServiceInterface {
    // Définition des répertoires dans uploadDir [/public]
    // pour stocker les icônes des collections
    protected const SETS_PATH = 'sets';
    // pour stocker les images des cartes
    protected const CARDS_PATH = 'cards';
    // Les cartes sont insérées en base par lot, cette valeur définit le nombre de cartes dans un lot
    protected const CARDS_BULK_LIMIT = 50;

    protected CardProviderInterface $cardProvider;
    protected EntityManagerInterface $em;
    protected string $uploadDir;

    public function __construct(
        CardProviderInterface $cardProvider,
        EntityManagerInterface $em,
        string $kernelProjectDir) {
        $this->cardProvider = $cardProvider;
        $this->em = $em;
        $this->uploadDir = $kernelProjectDir.'/public';
    }

    /**
     * Recherche une collection de cartes parmi toutes les collections
     * @param string $setName
     * @return array<SetDto>
     */
    public function searchSet(string $setName): array {
       $sets = $this->cardProvider->getSets();
       $resultSets = [];
       foreach($sets as $set) {
           if(mb_strpos(mb_strtolower($set->getName()), mb_strtolower($setName)) !== false) {
               $resultSets[] = $set;
           }
       }
       return $resultSets;
    }

    /**
     * Enregistrement des cartes d'une collection spécifiée
     * @inheritDoc
     */
    public function storeCards(SetDto $setDto) {
        // Récupération de la collection et création le cas échant
        $set = $this->em->getRepository(CardSet::class)->findOneBy(['code' => $setDto->getCode()]);
        if(!$set) {
            $set = $this->createSet($setDto);
            $this->em->persist($set);
        }
        // Récupération et création des cartes de cette collection
        $cards = $this->cardProvider->getSetCards($setDto);
        $cardCounter = 0;
        foreach ($cards as $cardDto) {
            // Si la carte n'existe pas encore, on la crée
            $card = $this->em->getRepository(Card::class)->findOneBy(['providerId' => $cardDto->getId()]);
            if(!$card) {
                $card = $this->createCard($cardDto);
                $card->setSet($set);
                $this->em->persist($card);
            }
            $cardCounter++;
            if(($cardCounter % self::CARDS_BULK_LIMIT) === 0) {
                $this->em->flush();
            }
        }
        $this->em->flush();
    }

    /**
     * Création d'une collection
     * @throws Exception
     */
    private function createSet(SetDto $setDto): CardSet {
        $set = new CardSet();
        $set->setName($setDto->getName());
        if($setDto->getReleasedAt()??false) {
            $set->setReleasedAt(new \DateTimeImmutable($setDto->getReleasedAt()));
        }
        $set->setCode($setDto->getCode());
        if($setDto->getIconUri()) {
            $filePath = '/'.self::SETS_PATH.'/'.$setDto->getCode().'.svg';
            file_put_contents($this->uploadDir.$filePath, file_get_contents($setDto->getIconUri()));
            $set->setIcon($filePath);
        }
        return $set;
    }

    /**
     * Création d'une carte
     * @param CardDto $cardDto
     * @return Card
     * @throws Exception
     */
    private function createCard(CardDto $cardDto): Card {
        $card = new Card();
        $card->setName($cardDto->getName());
        $card->setArtist($cardDto->getArtist());
        $card->setDescription($cardDto->getDescription());
        $card->setProviderId($cardDto->getId());
        // Récupération de l'image
        $imageUri = $cardDto->getImageUris()['normal']??false;
        if($imageUri) {
            $filePath = '/'.self::CARDS_PATH.'/normal_'.basename(parse_url($imageUri, PHP_URL_PATH));
            file_put_contents($this->uploadDir.$filePath, file_get_contents($imageUri));
            $card->setImage($filePath);
        }
        // Création du type si besoin
        $this->setCardType($card, $cardDto);
        // Création des couleurs si besoin
        $this->setCardColors($card, $cardDto);
        return $card;
    }

    /**
     * Association d'un type à une carte
     * @param Card $card
     * @param CardDto $cardDto
     * @return void
     */
    private function setCardType(Card $card, CardDto $cardDto) {
        static $types = [];
        $type = $this->em->getRepository(Type::class)->findOneBy(['name' => $cardDto->getType()]);
        if(!$type) {
            $hash = md5($cardDto->getType());
            if(!($types[$hash] ?? false)) {
                $types[$hash] = $this->createTypeFromCard($cardDto);
            }
            $type = $types[$hash];
        }
        $card->setType($type);
    }

    /**
     * Création du type de carte
     * @param CardDto $cardDto
     * @return Type
     */
    private function createTypeFromCard(CardDto $cardDto): Type {
        $type = new Type();
        $type->setName($cardDto->getType());
        return $type;
    }

    /**
     * Association de couleurs à une carte
     * @param Card $card
     * @param CardDto $cardDto
     * @return void
     */
    private function setCardColors(Card $card, CardDto $cardDto) {
        static $colors = [];
        foreach($cardDto->getColors() as $colorCode) {
            $color = $this->em->getRepository(Color::class)->findOneBy(['code' => $colorCode]);
            if(!$color) {
                if (!($colors[$colorCode] ?? false)) {
                    $colors[$colorCode] = $this->createColor($colorCode);
                }
                $color = $colors[$colorCode];
            }
            $card->addColor($color);
        }
    }

    /**
     * Création d'un objet Color
     * @param string $colorCode
     * @return Color
     */
    private function createColor(string $colorCode): Color {
        $color = new Color();
        $color->setCode($colorCode);
        return $color;
    }

}