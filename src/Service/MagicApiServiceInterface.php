<?php

namespace App\Service;

use App\Entity\Set;

interface MagicApiServiceInterface
{
    /**
     * Récupère tous les sets correspondants au nom en paramètre.
     * @return \App\Entity\Set[]
     */
    public function getSetsByName(string $setName): array;
    /**
     * Récupère toutes les cartes d'un Set.
     * @return \App\Entity\Card[]
     */
    public function getCardsBySet(Set $set): array;

}
