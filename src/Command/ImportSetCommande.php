<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\Set;
use App\Service\ScryfallApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

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
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;
    /**
     * @var ProgressBar
     */
    private ProgressBar $progressBar;
    private string $imagePath;


    public function __construct(ScryfallApi $api, EntityManagerInterface $em, ParameterBagInterface $parameterBag)
    {
        parent::__construct();

        $this->api = $api;
        $this->entityManager = $em;
        $this->parameterBag = $parameterBag;
    }

    protected function configure()
    {
        $this->setDescription('Importe une collection de carte');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->progressBar = new ProgressBar($output);

        $filesystem = new Filesystem();
        $this->imagePath = $this->parameterBag->get('kernel.project_dir') . '/public/cards';
        $filesystem->mkdir($this->imagePath);

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
        $mySet = $this->entityManager->getRepository(Set::class)->findOneBy(['uuid' => $find['id']]);

        if($mySet instanceof Set) {
            //-- Mise à jour
            $io->comment('Mise à jour de la collection ' . $find['name']);
            $mySet
                ->setUuid($find['id'])
                ->setCode($find['code'])
                ->setName($find['name'])
                ->setIconSvgUrl($find['icon_svg_uri'])
                ->setReleasedAt(new \DateTime($find['released_at']));
        } else {
            //-- Création
            $io->comment('Création de la collection ' . $find['name']);
            $mySet = new Set();
            $mySet
                ->setUuid($find['id'])
                ->setCode($find['code'])
                ->setName($find['name'])
                ->setIconSvgUrl($find['icon_svg_uri'])
                ->setReleasedAt(new \DateTime($find['released_at']));

            $this->entityManager->persist($mySet);
        }



        //-- On récupère les cartes de la collection
        $io->title('Récupération des carte de la collection ' . $mySet->getName());
        $res = $this->api->searchCards($find['search_uri']);

        if($res['total_cards'] === 0) {
            $io->error('Aucune carte récupérée pour cette collection :-(');
            return 0;
        }

        $io->success($res['total_cards'] . ' cartes trouvées');

        $this->progressBar->setMaxSteps($res['total_cards']);
        $this->progressBar->start();

        $this->_importCard($find['search_uri'], $mySet);

        $this->progressBar->finish();
        $this->entityManager->flush();

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

    private function _importCard(string $route, Set $set) {
        $res = $this->api->searchCards($route);

        //-- Sauvegarde des cartes trouvées
        foreach ($res['data'] as $card) {

            //-- Téléchargement de l'image
            $image = file_get_contents($card['image_uris']['png']);
            file_put_contents($this->imagePath . '/' . $card['id'] . '.png', $image);

            $myCard = $this->entityManager->getRepository(Card::class)->findOneBy(['uuid' => $card['id']]);

            if($myCard instanceof Card) {
                //-- Mise à jour
                $myCard
                    ->setUuid($card['id'])
                    ->setName($card['name'])
                    ->setArtist($card['artist'])
                    ->setDescription($card['oracle_text'])
                    ->setImageUrl($card['image_uris']['png'])
                    ->setSet($set)
                    ->setType($card['type_line']);
            } else {
                //-- Création
                $myCard = new Card();
                $myCard
                    ->setUuid($card['id'])
                    ->setName($card['name'])
                    ->setArtist($card['artist'])
                    ->setDescription($card['oracle_text'])
                    ->setImageUrl($card['image_uris']['png'])
                    ->setSet($set)
                    ->setType($card['type_line']);

                $this->entityManager->persist($myCard);
            }

            $this->progressBar->advance();

        }

        dump($res['has_more']);
        if($res['has_more']) {
            $this->_importCard($res['next_page'], $set);
        }
    }
}
