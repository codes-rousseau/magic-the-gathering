<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\Set;
use App\Service\ScryfallApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ImportSetCommande extends Command
{
    protected static $defaultName = 'app:import:collection';

    /**
     * @var ScryfallApi
     */
    private $api;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    public function __construct(ScryfallApi $api, EntityManagerInterface $em)
    {
        parent::__construct();

        $this->api = $api;
        $this->entityManager = $em;
    }

    protected function configure()
    {
        $this->setDescription('Importe une collection de carte');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        //-- On récupère toutes les collection en appellant l'API
        $io->title('Récupération de la liste des collection');
        $res = $this->api->getSets();
        $sets = $res['data'];

        if(count($sets) === 0) {
            $io->error('Aucune collection récupérée :-(');
            return 0;
        }

        $io->success(count($sets) . ' collections récupérées');

        //-- Filtre par nom de collection
        $question = new Question('Quelle collection voulez vous importer ?');
        $name = $io->askQuestion($question);

        $results = $this->_searchSetByName($sets, $name);

        if(count($results) === 0) {
            $io->error('Aucune collection ne correspond à ce nom :-(');
            return 0;
        }

        if(count($results) > 1) {
            $question = new ChoiceQuestion(
                'Plusieurs collections existent pour ce nom, veuillez préciser',
                array_map(fn($set) => $set['name'], $results),
                0
            );
            $name = $io->askQuestion($question);

            $find = $this->_searchSetByName($results, $name)[0];
        } else {
            $find = $results[0];
        }

        //-- On a trouvé la collection, on la met en base
        //@todo : Tester la duplication

        $mySet = new Set();
        $mySet->setCode($find['code'])
            ->setName($find['name'])
            ->setIconSvgUrl($find['icon_svg_uri'])
            ->setReleasedAt(new \DateTime($find['released_at']));

        $this->entityManager->persist($mySet);

        //-- On récupère les cartes de la collection
        $io->title('Récupération des carte de la collection ' . $mySet->getName());
        $res = $this->api->searchCards($find['search_uri']);

        if($res['total_cards'] === 0) {
            $io->error('Aucune carte récupérée pour cette collection :-(');
            return 0;
        }

        $io->success($res['total_cards'] . ' cartes récupérées');

        //-- Sauvegarde des cartes trouvées
        foreach ($res['data'] as $card) {
            $myCard = new Card();
            $myCard->setName($card['name'])
                ->setArtist($card['artist'])
                ->setDescription($card['oracle_text'])
                ->setImageUrl($card['image_uris']['png'])
                ->setSet($mySet)
                ->setType($card['type_line']);

            dump($myCard);

            $this->entityManager->persist($myCard);
        }

        return 0;

    }

    private function _searchSetByName(Array $sets, string $name) {
        $results = [];
        foreach ($sets as $set) {
            if (stripos($set['name'], $name) !== FALSE) {
                $results[] = $set;
            }
        }

        return $results;
    }
}
