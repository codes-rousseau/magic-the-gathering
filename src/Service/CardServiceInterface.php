<?php
namespace App\Service;
use App\Dto\SetDto;
use App\Entity\CardSet;
use App\Entity\Card;
use App\Entity\Color;
use App\Exception\CardProviderException;
use Doctrine\Common\Collections\Collection;

interface CardServiceInterface {

    /**
     * Recherche une collection de cartes parmi toutes les collections
     * @param string $setName
     * @return SetDto[]
     * @throws CardProviderException
     */
    public function searchSet(string $setName): array;

    /**
     * Enregistrement des cartes d'une collection spécifiée
     * @param SetDto $setDto
     * @return mixed
     * @throws CardProviderException
     */
    public function storeCards(SetDto $setDto);

    /**
     * Récupération de toutes les collections
     * @return CardSet[]
     */
    public function getAllCardSets(): array;

    /**
     * Récupération des cartes d'une collection
     * Possibilité de filtrer par nom, type et couleur
     * @return Card[]
     */
    public function getAllCards(CardSet $set, ?array $filters): array;

    /**
     * Récupération de toutes les couleurs disponibles
     * @return Color[]
     */
    public function getColors(): array;

    /**
     * Récupération de tous les types de carte possible pour une collection spécifiée
     * @param CardSet $set
     * @return array
     */
    public function getTypesForSet(CardSet $set): array;
}