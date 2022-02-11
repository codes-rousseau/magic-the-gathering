<?php

declare(strict_types=1);

namespace App\Dto\Scryfall\Transformer;

interface DtoTransformerInterface
{
    /**
     * Transforme un objet de l'API Scryfall en un objet interne (entité Doctrine).
     * @return mixed
     */
    public function transform($objectDto);
}
