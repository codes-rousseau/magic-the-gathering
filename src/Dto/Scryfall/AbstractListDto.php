<?php

declare(strict_types=1);

namespace App\Dto\Scryfall;

/**
 * Objet qui représente une liste d'object au format de l'API Scryfall.
 * Voir documentation : https://scryfall.com/docs/api/lists.
 */
abstract class AbstractListDto
{
    /**
     * Type d'objet.
     */
    public string $object;

    /**
     * Vrai si la liste est paginée.
     */
    public ?bool $hasMore = null;

    /**
     * Lien vers la prochaine page si la liste est paginée.
     */
    public ?string $nextPage = null;
}
