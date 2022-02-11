<?php

declare(strict_types=1);

namespace App\Dto\Scryfall\Model;

/**
 * Objet qui représente une collection au format de l'API Scryfall.
 */
final class SetDto
{
    /**
     * Identifiant UUID string.
     */
    public string $id;

    /**
     * Code unique sur 5 caractères.
     */
    public string $code;

    /**
     * Nom de la collection en Anglais.
     */
    public string $name;

    /**
     * Date de 1ère publication (string: Y-m-d).
     */
    public ?string $released_at = null;

    /**
     * URL vers le SVG de l'îcone de la collection.
     */
    public string $icon_svg_uri;
}
