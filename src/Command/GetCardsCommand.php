<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\CardCollection;
use App\Service\ScryfallService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GetCardsCommand extends Command
{
    protected static $defaultName = 'app:get-cards';
    protected static $defaultDescription = 'Get collections and cards';

    private $scryfallService;

    public function __construct(string $name = null,ScryfallService $scryfallService, EntityManagerInterface $entityManager)
    {
        parent::__construct($name);
        $this->scryfallService = $scryfallService;
    }

    protected function configure(): void
    {
        $this->
            setName('app:get-cards')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $collectionCard = $this->scryfallService->getCollections();

        $tabCollection = $collectionCard->data;

dump($tabCollection[1]);

        //recherche de toutes les collections
        for ($i=0; $i<count($tabCollection); $i++) {
            //pages

            $oCollection = new CardCollection();
            $oCollection->setCode($tabCollection[$i]->code);
            $date = new \DateTime($tabCollection[$i]->released_at);
            $oCollection->setPublishedAt($date);
            $oCollection->setName($tabCollection[$i]->name);
            $oCollection->setSvg($tabCollection[$i]->icon_svg_uri);


            //recherche de toutes les cartes de chaque collection

            $cards = $this->scryfallService->getCardsByCollection($tabCollection[$i]->code);
            //pages

            $tabCards = $cards->data;

            for ($p=0; $p<count($tabCards); $p++) {

                $oCard = new Card();
                $oCard->setName($tabCards[$p]->name);
                $oCard->setCollection($oCollection);
                $oCard->setArtist($tabCards[$p]->artist);
                $oCard->setColor($tabCards[$p]->border_color);


                //copy

                copy($tabCards[$p]->image_uris->normal, 'cards');

                $oCollection->addCard($oCard);
                //persist + flush collec

                //presist + flush card
            }

            dd($cards);



        }


        $io->success('Finish.');

        return 0;
    }
}
