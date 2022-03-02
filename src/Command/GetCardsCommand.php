<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\CardCollection;
use App\Service\ScryfallService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class GetCardsCommand extends Command
{
    protected static $defaultName = 'app:get-cards';
    protected static $defaultDescription = 'Get collections and cards';

    private $scryfallService;
    private $entityManager;

    public function __construct(string $name = null, ScryfallService $scryfallService, EntityManagerInterface $entityManager)
    {
        parent::__construct($name);
        $this->scryfallService = $scryfallService;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->
        setName('app:get-cards')
            ->addArgument('arg1', InputArgument::REQUIRED, 'Nom de la collection');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        $filesystem = new Filesystem();

        $collectionCardName = $this->scryfallService->getCollectionsByName($arg1);


        $tabCollectionName = $collectionCardName->data;


        if (count($tabCollectionName) > 1) {


            $arrayNames = array_column($tabCollectionName, 'name');
            $arrayCode = array_column($tabCollectionName, 'code');


            $helper = $this->getHelper('question');

            $question = new ChoiceQuestion(
                'Plusieurs SETS correspondent, merci de saisir le nom du SET',
                $arrayNames,
                0
            );

            $collectionName = $helper->ask($input, $output, $question);

            $index = array_search($collectionName, array_column($tabCollectionName, 'name'));

            $collectionCard = $this->scryfallService->getCollectionsByCode($tabCollectionName[$index]->code);

            $tabCollection = $collectionCard;
        } else {
            $tabCollection = $collectionCardName;
        }


        //Création de la collection
        $oCollection = new CardCollection();
        $oCollection->setCode($tabCollection->code);
        $date = new \DateTime($tabCollection->released_at);
        $oCollection->setPublishedAt($date);
        $oCollection->setName($tabCollection->name);
        $oCollection->setSvg($tabCollection->icon_svg_uri);


        //recherche de toutes les cartes de chaque collection
        $cards = $this->scryfallService->getCardsByCollection($tabCollection->code);

        //Cas où une collection n'a pas de carte
        if ($cards->object != 'error') {

            $tabCards = $cards->data;

            for ($p = 0; $p < count($tabCards); $p++) {

                $oCard = new Card();


                if (isset($tabCards[$p]->card_faces)) {

                    //Type de cartes carte face contenant plusieurs faces
                    foreach ($tabCards[$p]->card_faces as $faces) {

                        echo "name face = " . $faces->name . "\n";


                        if (isset($faces->image_uris)) {

                            $oCard->setName($faces->name);
                            $oCard->setCollection($oCollection);

                            if (isset($faces->artist)) {
                                $oCard->setArtist($faces->artist);
                            }

                            if (isset($faces->colors) && count($faces->colors) > 0) {
                                $oCard->setColor($faces->colors[0]);
                            }

                            $oCard->setDescription($faces->oracle_text);

                            $oCard->setImage($faces->image_uris->normal);


                            $cardName = str_replace(" ", "_", $faces->name);

                            $oCard->setTarget('/cards/' . $cardName . '.jpg');


                            //copy
                            $filesystem->copy($faces->image_uris->normal, 'public/cards/' . $cardName . '.jpg');

                            $oCollection->addCard($oCard);
                        }

                    }


                } else {

                    echo "name = " . $tabCards[$p]->name . "\n";

                    if (isset($tabCards[$p]->image_uris)) {

                        $oCard->setName($tabCards[$p]->name);
                        $oCard->setCollection($oCollection);
                        $oCard->setArtist($tabCards[$p]->artist);
                        $oCard->setColor($tabCards[$p]->border_color);
                        $oCard->setDescription($tabCards[$p]->oracle_text);
                        $oCard->setImage($tabCards[$p]->image_uris->normal);

                        $cardName = str_replace(" ", "_", $tabCards[$p]->name);

                        $oCard->setTarget('/cards/' . $cardName . '.jpg');

                        //copy
                        $filesystem->copy($tabCards[$p]->image_uris->normal, 'public/cards/' . $cardName . '.jpg');

                        $oCollection->addCard($oCard);
                    }

                }

                //enregistrement objet card
                $this->entityManager->persist($oCard);
                $this->entityManager->flush();

            }
        }


        //enregistrement objet collection
        $this->entityManager->persist($oCollection);
        $this->entityManager->flush();

        $io->success('Finish.');

        return 0;
    }
}
