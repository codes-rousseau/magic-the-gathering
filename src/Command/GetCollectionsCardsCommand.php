<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\Collections;
use App\Entity\Color;
use App\Entity\Type;
use App\Repository\CardRepository;
use App\Repository\CollectionsRepository;
use App\Repository\ColorRepository;
use App\Repository\TypeRepository;
use App\Services\ScryFall;
use App\Services\UploadImage;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GetCollectionsCardsCommand extends Command
{
    protected static $defaultName = 'app:get-collections-cards';
    protected static string $defaultDescription = 'Add a collection and her cards in the database if they do not already exist';
    private ScryFall $scryfall;
    private CollectionsRepository $collectionRepository;
    private ColorRepository $colorRepository;
    private TypeRepository $typeRepository;
    private CardRepository $cardRepository;
    private EntityManagerInterface $em;
    private UploadImage $upload;

    public function __construct(
        ScryFall $scryfall,
        CollectionsRepository $collectionsRepository,
        CardRepository $cardRepository,
        ColorRepository $colorRepository,
        TypeRepository $typeRepository,
        EntityManagerInterface $entityManager,
        UploadImage $upload)
    {
        parent::__construct();
        $this->scryfall = $scryfall;
        $this->collectionRepository = $collectionsRepository;
        $this->colorRepository = $colorRepository;
        $this->typeRepository = $typeRepository;
        $this->cardRepository = $cardRepository;
        $this->em = $entityManager;
        $this->upload = $upload;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('collection', InputArgument::OPTIONAL, 'The collection to recover')
        ;
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $collection = $input->getArgument('collection');
        if (!$collection || empty(trim($collection))) {
            $helper = $this->getHelper('question');
            $question = new Question('Write the name of the collection or write a "?" to see the list of collections (default: "?")', '?');
            $answer = $helper->ask($input, $output, $question);

            if ('?' === $answer || empty(trim($answer))) {
                // Get list of all collections
                $collections = [];
                foreach ($this->scryfall->getCollections() as $collection) {
                    $collections[] = $collection['name'];
                }

                return $output->writeln($collections);
            }

            $collection = $answer;
        }

        // Get list of cards by collection and insert on database
        $collection_load = $this->scryfall->getCollections($collection);
        $cards = $this->scryfall->getCards($collection_load);

        $new_collection = new Collections();
        $collection_exist = $this->collectionRepository->findOneBy(['name' => $collection]);
        if ($collection_exist) {
            $new_collection = $collection_exist;
        }

        $upload_dir = $this->upload->upload('collection', $collection_load['code'].'.svg', $collection_load['icon_svg_uri']);

        $new_collection->setCode($collection_load['code'])
            ->setName($collection_load['name'])
            ->setReleaseAt(new DateTime($collection_load['released_at']))
            ->setIcon($upload_dir);

        foreach ($cards as $card) {
            $new_card = new Card();
            $card_exist = $this->cardRepository->findOneBy(['name' => $collection]);
            if ($card_exist) {
                $new_card = $card_exist;
            }

            $upload_dir = $this->upload->upload('card', $card['id'].'.png', $card['image_uris']['png']);

            $new_card->setName($card['name'])
                ->setDescription($card['oracle_text'])
                ->setImage($upload_dir)
                ->setArtistName($card['artist'])
                ->setType($this->createType($card['type_line']));

            foreach ($card['colors'] as $color) {
                $new_card->addColor($this->createColors($color));
            }

            $new_collection->addCard($new_card);

            $this->em->persist($new_card);
        }

        $this->em->persist($new_collection);
        $this->em->flush();

        return 0;
    }

    private function createColors($name): Color
    {
        $color = new Color();
        $colorExist = $this->colorRepository->findOneBy(['label' => $name]);
        if ($colorExist) {
            $color = $colorExist;
        }
        $color->setLabel($name);

        $this->em->persist($color);
        $this->em->flush();

        return $color;
    }

    private function createType($name): Type
    {
        $type = new Type();
        $typeExist = $this->typeRepository->findOneBy(['label' => $name]);
        if ($typeExist) {
            $type = $typeExist;
        }
        $type->setLabel($name);

        $this->em->persist($type);
        $this->em->flush();

        return $type;
    }
}
