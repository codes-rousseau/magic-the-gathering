<?php

declare(strict_types=1);

namespace App\Dto\Scryfall;

final class CardListDto extends AbstractListDto
{
    /**
     * Nombre total de carte pour toutes les pages.
     */
    public ?int $totalCards = null;

    /**
     * Liste des cartes.
     *
     * @var CardDto[]
     */
    public array $data = [];
}
