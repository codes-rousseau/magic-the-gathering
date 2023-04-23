<?php
namespace App\Service;
use App\Dto\SetDto;
use App\Entity\CardSet;
use App\Entity\Card;
use App\Exception\CardProviderException;

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

}