<?php

namespace App\Command;

use App\Exception\AlreadyExistsException;
use App\Exception\NoCardFoundException;
use App\Exception\NoSetFoundException;
use App\Exception\SetNotExistingException;
use App\Service\GetCollectionService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GetCollectionCommand extends Command
{
    protected static $defaultName = 'app:get-collection';
    protected static $defaultDescription = 'Get a collection of cards from scryfall api';

    private GetCollectionService $collectionService;

    public function __construct(GetCollectionService $collectionService)
    {
        $this->collectionService = $collectionService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $question = new Question('Please enter the name of the set you want to retrieve or press return to see all the sets available');
        $answer = $io->askQuestion($question);

        if (!is_string($answer)) {
            try {
                $setsAvailable = $this->collectionService->getAllCollectionsAvailable();
            } catch (ClientExceptionInterface | DecodingExceptionInterface | ServerExceptionInterface | TransportExceptionInterface | RedirectionExceptionInterface $e) {
                $io->error('Oooops sorry an error occurred while trying to get the sets available');
                return 1;
            } catch (NoSetFoundException $e) {
                $io->error('Not your lucky day it seems impossible to reach the collections');
                return 1;
            }

            $io->comment('Here are all the sets available: ');

            foreach ($setsAvailable as $setName) {
                $output->writeln($setName);
            }

            $question = new Question('Now please enter the name of the set you want to retrieve');
            $answer = $io->askQuestion($question);
        }

        while (!is_string($answer)) {
            $question = new Question('Please enter a valid name');
            $answer = $io->askQuestion($question);
        }

        try {
            $this->collectionService->getCollectionByName($answer);
        } catch (AlreadyExistsException $e) {
            $io->warning(sprintf('The collection \'%s\' is already stored in the database', $answer));
            return 0;
        } catch (NoCardFoundException $e) {
            $io->error('Not your lucky day it seems impossible to reach the cards');
            return 1;
        } catch (SetNotExistingException $e) {
            $io->error(sprintf('The set \'%s\' does not exist', $answer));
            return 1;
        } catch (ClientExceptionInterface | DecodingExceptionInterface | ServerExceptionInterface | TransportExceptionInterface | RedirectionExceptionInterface | Exception $e) {
            $io->error(sprintf('Oooops sorry an error occurred while trying to get the \'%s\' set', $answer));
            return 1;
        }

        $io->success(sprintf('Successfully added cards from \'%s\' collection to database', $answer));

        return 0;
    }
}
