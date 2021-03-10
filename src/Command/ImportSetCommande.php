<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\Color;
use App\Entity\Set;
use App\Entity\Type;
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
                array_map(fn($set) => $set['name'], $results)
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
        } else {
            //-- Création
            $io->comment('Création de la collection ' . $find['name']);
            $mySet = new Set();
        }

        $mySet = $this->_hydrateSet($mySet, $find);
        $this->entityManager->persist($mySet);

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

    private function _searchSetByName(Array $sets, string $name): array
    {
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
            // $image = file_get_contents($card['image_uris']['png']);
            // file_put_contents($this->imagePath . '/' . $card['id'] . '.png', $image);

            $card['image_uri'] = '/cards/' . $card['id'] . '.png';

            $myCard = $this->entityManager->getRepository(Card::class)->findOneBy(['uuid' => $card['id']]);

            if(!$myCard instanceof Card) {
                //-- Création
                $myCard = new Card();
            }

            $myCard = $this->_hydrateCard($myCard, $set, $card);
            $this->entityManager->persist($myCard);

            $this->progressBar->advance();

        }

        if($res['has_more']) {
            $this->_importCard($res['next_page'], $set);
        }
    }

    /**
     * Hydrate un objet Set
     *
     * @param Set $set
     * @param array $data
     * @return Set
     */
    private function _hydrateSet(Set $set, Array $data): Set
    {
        $set
            ->setUuid($data['id'])
            ->setCode($data['code'])
            ->setName($data['name'])
            ->setIconSvgUrl($data['icon_svg_uri'])
            ->setReleasedAt(new \DateTime($data['released_at']));

        return $set;
    }

    /**
     * Hydrate un objet Card
     *
     * @param Card $card
     * @param Set $set
     * @param array $data
     * @return Card
     */
    private function _hydrateCard(Card $card, Set $set, Array $data): Card
    {
        $card
            ->setUuid($data['id'])
            ->setName($data['name'])
            ->setArtist($data['artist'])
            ->setDescription($data['oracle_text'])
            ->setImageUrl($data['image_uri'])
            ->setSet($set);

        //-- Gestion des couleurs
        foreach ($data['colors'] as $colorCode) {
            $color = $this->entityManager->getRepository(Color::class)->findOneBy(['code' => $colorCode]);
            if($color instanceof Color) {
                $card->addColor($color);
            }
        }

        //-- Gestion des types
        $type = $this->entityManager->getRepository(Type::class)->findOneBy(['name' => $data['type_line']]);
        if(!$type instanceof Type) {
            //-- Nouveau type
            $type = new Type();
            $type->setName($data['type_line']);

            $this->entityManager->persist($type);
            $this->entityManager->flush();
        }

        $card->setType($type);

        return $card;
    }
}
