<?php
namespace App\Command;

use App\Exception\CardProviderException;
use App\Service\CardServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Dto\SetDto;

class ScryfallImportCommand extends Command
{
    // Constantes non définies (Command) dans SF 4.4
    private const SUCCESS = 0;
    private const FAILURE = 1;

    protected static $defaultName = 'app:scryfall:import';
    protected static $defaultDescription = 'Importe des cartes magiques du fournisseur Scryfall';
    protected TranslatorInterface $translator;
    protected CardServiceInterface $cardService;

    public function __construct(CardServiceInterface $cardService, TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->cardService = $cardService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
        $this->addArgument('set', InputArgument::REQUIRED, 'Nom de la collection');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $setName = $input->getArgument('set');
        try {
            // Récupération des collections qui contiennent la valeur saisie
            $sets = $this->cardService->searchSet($setName);
            if(!empty($sets)) {
                // Mise en forme d'une liste de choix
                $setNames = array_map(function ($set) {
                    return $set->getName();
                }, $sets);

                $answer = $io->choice($this->translator->trans('command.scryfall.import.setQuestion'), $setNames, 0);
                // Récupération de la collection choisie
                $setSelected = array_values(array_filter($sets, function (SetDto $set) use ($answer) {
                    return $answer === $set->getName();
                }))[0];
                // Enregistrement des cartes liées à cette collection
                $this->cardService->storeCards($setSelected);
                $io->success("Import des cartes de la collection $answer terminée.");
            } else {
                $io->note("Aucune collection trouvée avec ce terme : $setName");
            }
            return self::SUCCESS;
        } catch(CardProviderException $e) {
            $io->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
