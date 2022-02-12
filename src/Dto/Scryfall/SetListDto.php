<?php

declare(strict_types=1);

namespace App\Dto\Scryfall;

/**
 * Objet qui représente une liste de collection au format de l'API Scryfall.
 */
final class SetListDto extends AbstractListDto
{
    /**
     * Liste des collections.
     *
     * @var SetDto[]
     */
    public array $data = [];
}
