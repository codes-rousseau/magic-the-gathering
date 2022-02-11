<?php

declare(strict_types=1);

namespace App\Dto\Scryfall\Model;

/**
 * Objet qui représente une carte au format de l'API Scryfall.
 */
final class CardDto
{
    /**
     * Identifiant UUID string.
     */
    public string $id;

    /**
     * Le nom de la carte.
     */
    public string $name;

    /**
     * Identité couleur de cette carte.
     */
    public array $color_identity = [];

    /**
     * La ligne de type de cette carte.
     */
    public ?string $type_line = null;

    /**
     * Le nom de l'illustrateur de cette carte.
     */
    public ?string $artist = null;

    /**
     * La description de cette carte.
     */
    public ?string $flavor_text = null;

    /**
     * Toutes les images disponible pour cette carte.
     */
    public ?array $image_uris = [];
}
