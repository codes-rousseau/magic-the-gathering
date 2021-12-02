<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\Set;
use App\Service\ScryfallApiClient;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadCards extends Command
{
    protected static $defaultName = 'app:load-cards';
    private EntityManagerInterface $em;
    private string $imagesRootDir;

    public function __construct(EntityManagerInterface $em, $imagesRootDir)
    {
        parent::__construct();
        $this->em = $em;
        $this->imagesRootDir = $imagesRootDir;
    }

    protected function configure(): void
    {
        $this->setDescription('Récupérer les cartes d\'une collection en fonction de son nom');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Récupération d\'un set de cartes en fonction de la collection');

        // Récupération du nom de la collection recherchée
        $collectionName = $io->ask('Nom de la collection recherchée', null, function ($input) {
            if (is_null($input) || strlen($input) < 3) {
                throw new \RuntimeException('Veuillez saisir au moins 3 caractères');
            }

            return $input;
        });

        // Récupération de la liste des collections
        $sets = ScryfallApiClient::fetchSets();

        // Recherche de la collection demandée (par nom) dans la liste des collections
        $searchResults = [];
        foreach ($sets as $setData) {
            if (false !== strpos(strtoupper($setData['name']), strtoupper($collectionName))) {
                // TODO : Utiliser un deserializer
                $set = new Set();
                $set->setCode($setData['code'])
                    ->setName($setData['name'])
                    ->setReleasedAt(new DateTimeImmutable($setData['released_at']))
                    ->setIconUri($setData['icon_svg_uri']);
                array_unshift($searchResults, $set);
            }
        }

        // Aucune collection trouvée
        if (!$searchResults) {
            $io->error('Aucune collection n\'a été trouvée pour la recherche "'.$collectionName.'"');

            return 0;
        }

        // Une collection trouvée
        if (1 == count($searchResults)) {
            $selectedSet = $searchResults[0];
        }

        // Plusieurs collections trouvées
        if (count($searchResults) > 1) {
            $io->newLine();
            $question = new ChoiceQuestion(
                'Plusieurs collections ont été trouvées, veuillez en choisir une : ',
                $searchResults,
            );
            $question->setValidator(function ($answer) use ($searchResults) {
                if (!is_numeric($answer) || $answer > count($searchResults) - 1 || $answer < 0) {
                    throw new \RuntimeException('Veuillez sélectionner un résultat valide');
                }

                return $answer;
            });

            $responseId = $io->askQuestion($question);
            $selectedSet = $searchResults[$responseId];

        }

        // Récupération de la liste des cartes associées à la collection demandée
        $cards = ScryfallApiClient::fetchCardsBySetCode($selectedSet->getCode());

        $io->text('Téléchargement des cartes de la collection "'.$selectedSet->getName().'"...');
        $io->progressStart(count($cards));

        // Dossier de sauvegarde des images des cartes
        $cardImageDir = 'cards/';
        if (!is_dir($this->imagesRootDir.$cardImageDir)) {
            mkdir($this->imagesRootDir.$cardImageDir);
        }

        // Enregistrement des cartes et de leurs images
        foreach ($cards as $cardData) {
            // Sauvegarde de l'image
            $cardImagePath = $cardImageDir.$cardData['set'].'-'.uniqid().'.jpg';
            $imageDir = $this->imagesRootDir.$cardImagePath;
            file_put_contents($imageDir, file_get_contents($cardData['image_uris']['art_crop']));

            // TODO : Utiliser un deserializer
            $card = new Card();
            $card
                ->setName($cardData['name'])
                ->setTypeLine($cardData['type_line'])
                ->setColorIdentity($cardData['color_identity'])
                ->setOracleText($cardData['oracle_text'])
                ->setArtist($cardData['artist'])
                ->setImagePath($cardImagePath)
                ->setSet($selectedSet);

            $this->em->persist($card);
            $io->progressAdvance();
        }

        $this->em->flush();
        $io->progressFinish();

        $io->success([
            'La collection "'.$selectedSet->getName().'" a été sauvegardée en base.',
            'Les '.count($cards).' cartes associées ont été sauvegardées en base.',
        ]);

        return 1;
    }
}
