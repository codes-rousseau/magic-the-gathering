<?php

namespace App\Command;

use App\Controller\CollectionCardController;
use App\Entity\Card;
use App\Entity\CollectionCard;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Flex\Path;
use function Doctrine\Common\Cache\Psr6\set;

class CollectionCommand extends Command
{
    protected static $defaultName = 'collection';
    protected static $defaultDescription = 'Add a short description for your command';
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        // 3. Update the value of the private entityManager variable through injection
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }
        $collections = json_decode($this->CallAPI('GET', "https://api.scryfall.com/sets", false))->data;
        $io->note(sprintf(''));
        $choices = [];
        $i = 1;
        foreach ($collections as $collection) {
            $choices[$collection->code] = $collection->name;
        }
        $code = $io->choice('veuillez choisir une collection', $choices);
        $choice = json_decode($this->CallAPI('GET', "https://api.scryfall.com/sets/$code", false));


        $em = $this->entityManager;
        $newCollection = new CollectionCard();
        $newCollection->setCode($code)
            ->setName($choice->name)
            ->setIcon("/public/cards/$code.svg")
            ->setReleaseDate(new \DateTime($choice->released_at));

        copy($choice->icon_svg_uri, dirname(__FILE__) . "\..\..\public\cards\\$code.svg");
        $em->persist($newCollection);
        $em->flush();

        $cards = json_decode($this->CallAPI('GET', $choice->search_uri, false))->data;
        foreach ($cards as $card) {
            $newCard = new Card();
            $name = $card->name;
            $newCard->setName($name)
                ->setArtistName($card->artist)
                ->setCollection($newCollection)
                ->setColor($card->colors[0])
                ->setDescription($card->oracle_text)
                ->setPicture("/public/cards/$name.png")
                ->setType($card->type_line);
            copy($card->image_uris->png, dirname(__FILE__) . "\..\..\public\cards\\$name.png");
            $em->persist($newCard);

            $em->flush();
            $newCollection->addCard($newCard);
            $em->persist($newCollection);
            $em->flush();
            dump("ajout de la carte $name a la collection ". $newCollection->getName());
        }
        $output->writeln($code);
        $io->success('Cartes et collection mis a jour');

        return 0;

    }

    function CallAPI($method, $url, $data = false)
    {
        $curl = curl_init();

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // Optional Authentication:
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }
}
