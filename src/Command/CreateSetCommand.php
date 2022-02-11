<?php

declare(strict_types=1);

namespace App\Command;

use App\Dto\Scryfall\Model\SetDto;
use App\Service\ScryfallService;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateSetCommand extends Command
{
    private const SUCCESS = 0;
    private const ERROR = 1;

    private ScryfallService $scryfall;

    public function __construct(
        ScryfallService $scryfall,
        string $name = null
    ) {
        $this->scryfall = $scryfall;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('app:create-set')
            ->setDescription('Create the cards of a Magic set in the database.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Create a Magic set');

        $question = new Question('Enter the name of set (3 characters min)');
        $question->setValidator(function ($setName) {
            if (strlen($setName) < 3) {
                throw new RuntimeException('Please enter 3 characters or more.');
            }

            return $setName;
        });

        $setName = $io->askQuestion($question);
        $sets = $this->scryfall->getSetsByName($setName);
        if (0 === count($sets)) {
            $io->error(sprintf('No set found for the following search: %s', $setName));

            return self::ERROR;
        } elseif (1 === count($sets)) {
            $setSelected = array_unshift($sets);
        } else {
            $io->warning('Several sets were found for this search.');

            $setNames = array_map(function (SetDto $setDto) { return $setDto->name; }, $sets);
            $question = new ChoiceQuestion('Please choose the set in this list', $setNames);

            $selectedName = $io->askQuestion($question);
            $index = array_search($selectedName, $setNames, true);
            $setSelected = $sets[$index];
            unset($sets);
        }

        if ($setSelected instanceof SetDto) {
            $this->scryfall->createSet($setSelected);
            $io->success('Set has been created successfully !');

            return self::SUCCESS;
        }

        $io->error('An error has occurred.');

        return self::ERROR;
    }
}
