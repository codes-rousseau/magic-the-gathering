<?php

namespace App\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Service\ScryfallAPIService;
use App\Service\UploadService;
use App\Entity\Collections;
use App\Entity\Card;
use App\Entity\Type;
use App\Entity\Color;
use App\Repository\CollectionsRepository;
use App\Repository\CardRepository;
use App\Repository\TypeRepository;
use App\Repository\ColorRepository;


class ScryfallCommand extends Command
{
    protected static $defaultName = 'scryfall';
    protected static $defaultDescription = 'Import a collection of cards from scryfall';

    private ScryfallAPIService $scryfall;
    private UploadService $uploadService;

    private EntityManagerInterface $em;
    private CollectionsRepository $collectionsRepository;
    private CardRepository $cardRepository;
    private TypeRepository $typeRepository;
    private ColorRepository $colorRepository;


    public function __construct(
        string $name = null,
        ScryfallAPIService $scryfallAPIService,
        CollectionsRepository $collectionsRepository,
        CardRepository $cardRepository,
        TypeRepository $typeRepository,
        ColorRepository $colorRepository,
        UploadService $uploadService
    )
    {
        parent::__construct($name);

        $this->scryfall = $scryfallAPIService;
        $this->uploadService = $uploadService;

        $this->collectionsRepository = $collectionsRepository;
        $this->cardRepository = $cardRepository;
        $this->typeRepository = $typeRepository;
        $this->colorRepository = $colorRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of collection')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg_name = $input->getArgument('name');

        if( !is_null($arg_name) ) {
            $collections = $this->scryfall->getCollections();

            $collections_possibilities = [];
            $collections_possibilities_names = [];

            foreach ($collections->data as $collec) {
                if( strpos($collec->name, $arg_name) !== false ) {
                    $collections_possibilities[$collec->name] = $collec;
                    $collections_possibilities_names[] = $collec->name;
                }
            }

            $collec = null;

            // Test if we have one or multiple possibilities
            if (count($collections_possibilities) == 1) {
                $collec = $collections_possibilities[$collections_possibilities_names[0]];

                $io->comment("Collection found");
            } elseif(count($collections_possibilities) > 1) {
                $choice = $io->choice('Multiple collection contain this name, choice one', $collections_possibilities_names);

                $collec = $collections_possibilities[$choice];
            }

            if( !is_null($collec)) {

                $collection = $this->createCollection($collec);

                if( !is_null($collection)) {

                    $io->success('Collection created');

                    $count_cards_possible = 0;
                    $count_cards_add = 0;

                    $datas_cards = $this->scryfall->getCards($collection->getCode())->data;

                    $io->writeln("Cards creation...\r\n");
                    $progress = $io->createProgressBar(count($datas_cards));
                    $progress->start();

                    foreach($datas_cards as $c) {
                        $count_cards_possible++;
                        $progress->advance();

                        if(!is_null($this->createCard($c, $collection))) {
                            $count_cards_add++;
                        }
                    }

                    $io->writeln("\r\n\r\n$count_cards_add cards added on $count_cards_possible possible \r\n");

                } else {
                    $io->error('Error during creation');
                }

            } else {
                $io->error('No collection');
            }
        } else {
            $io->error("No name given");
        }

        return 0;
    }

    /**
     * @param Object $collec
     * @return Collections|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function createCollection(Object $collec) {

        $collection = $this->collectionsRepository->findOneBy(['code' => $collec->code]);

        if($collection == null) {
            $collection = new Collections();
            $collection->setCode($collec->code);
            $collection->setName($collec->name);
            $collection->setReleasedAt(new \DateTimeImmutable($collec->released_at));
        }

        $collection->setIcon($this->uploadService->uploadFile($collec->icon_svg_uri, 'collections', $collec->code. '.svg'));

        if(!empty($collection->getIcon())) {
            $this->collectionsRepository->add($collection, true);


            return $collection;
        } else {
            return null;
        }
    }

    /**
     * @param \stdClass $c
     * @param Collections $collection
     * @return Card|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function createCard(\stdClass $c, Collections $collection) {
        $card = $this->cardRepository->findOneBy(['name' => $c->name]);

        if($card == null) {
            $card = new Card();
            $card->setName($c->name);

            if(isset($c->oracle_text)) {
                $card->setDescription($c->oracle_text);
            } else {
                $card->setDescription('');
            }

            $card->setArtistName($c->artist);
            $card->setType($this->createType($c->type_line));
            $card->setCollection($collection);

            if(count($c->colors) > 0) {
                foreach($c->colors as $color) {
                    $card->addColor($this->createColor($color));
                }
            }

            $card->setImage($this->uploadService->uploadFile($c->image_uris->png, 'cards', $c->id . '.png'));

            if(!empty($card->getImage())) {
                $this->cardRepository->add($card);
            } else {
                $card = null;
            }
        }

        return $card;
    }

    /**
     * @param String $type_name
     * @return Type
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function createType(String $type_name): Type {
        $type = $this->typeRepository->findOneBy(['name' => $type_name]);

        if($type == null) {
            $type = new Type();
            $type->setName($type_name);

            $this->typeRepository->add($type);
        }

        return $type;
    }

    /**
     * @param String $color_name
     * @return Color
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function createColor(String $color_name): Color {
        $color = $this->colorRepository->findOneBy(['name' => $color_name]);

        if($color == null) {
            $color = new Color();
            $color->setName($color_name);

            $this->colorRepository->add($color);
        }

        return $color;
    }

}
