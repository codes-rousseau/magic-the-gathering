<?php

namespace App\Command;

use App\Entity\Set;
use App\Service\CardService;
use App\Service\MagicApiServiceInterface;
use App\Service\ScryfallApiService;
use App\Service\SetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande app:import-set
 * Permet de récupérer les cartes Magic d'une collection spécifique.
 */
class ImportSetCommand extends Command
{
    protected static $defaultName = 'app:import-set';

    private MagicApiServiceInterface $magicApiService;
    private SetService $setService;
    private CardService $cardService;
    private SymfonyStyle $io;
    private EntityManagerInterface $em;

    public function __construct(
        ScryfallApiService $scryfallApiService,
        SetService $setService,
        CardService $cardService,
        EntityManagerInterface $entityManagerInterface
    ) {
        $this->magicApiService = $scryfallApiService;
        $this->setService = $setService;
        $this->cardService = $cardService;
        $this->em = $entityManagerInterface;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Récupère les cartes Magic d\'une collection spécifique.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Récupération d\'un set de cartes');

        $question = new Question('Entrez le nom de la collection à importer');
        $answer = $this->io->askQuestion($question);

        if (!is_string($answer)) {
            $this->io->error("Nom de la collection invalide.");
            return 1;
        }

        $sets = $this->magicApiService->getSetsByName($answer);
        if (count($sets) === 1) {
            $this->importSet($sets[0], $output);
        } else if (count($sets) > 1) {
            $question = new ChoiceQuestion('Choisissez une collection', $sets);
            $answer = $this->io->askQuestion($question);
            $key = array_search($answer, $sets);
            $this->importSet($sets[$key], $output);
        } else {
            $this->io->error("Aucune collection correspondant à ce nom n'a été trouvée.");
            return 1;
        }

        return 0;
    }

    /**
     * Import du Set et de toutes les cartes qu'il contient.
     */
    private function importSet(Set $set, OutputInterface $output): void
    {
        $setCreated = $this->setService->createSet($set);

        if ($setCreated == null) {
            $this->io->error('Cette collection a déjà été importée.');
            return;
        }

        $this->io->writeln('Collection ' . $setCreated->getName() . ' créée.');

        $cards = $this->magicApiService->getCardsBySet($setCreated);
        $this->io->writeln('Import des ' . count($cards) . ' cartes de la collection...');

        $progressBar = new ProgressBar($output, count($cards));
        $progressBar->start();

        foreach($cards as $card)
        {
            $this->cardService->createCard($card, $set);
            $progressBar->advance();
        }

        $this->em->flush();
        $progressBar->finish();

        $this->io->success('Import de la collection ' . $setCreated->getName() . ' terminé.');
    }
}
