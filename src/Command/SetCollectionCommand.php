<?php

namespace App\Command;

use App\Service\GetCardCollectionService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetCollectionCommand extends Command
{
    protected static $defaultName = 'SetCollectionCommand';
    protected static $defaultDescription = 'Allows you to retrieve cards from a collection and store them in a database';
    private $cardCollectionService;

    public function __construct(GetCardCollectionService $cardCollectionService)
    {
        $this->cardCollectionService = $cardCollectionService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $helper = $this->getHelper('question');
        $question = new Question('Enter the name of a magic card collection or leave blank to see the collection list : ');
        $answer = $helper->ask($input, $output, $question);


        if ($answer) {
            try {
                $this->cardCollectionService->getCard($answer);
            }catch (\Exception $e){
                $output->writeln($this->cardCollectionService->getCollection());
            }
            $io = new SymfonyStyle($input, $output);
            $io->success('The cards of the collection have been saved in the database.');
        }else{
            $output->writeln($this->cardCollectionService->getCollection());
        }

        return 0;
    }
}
