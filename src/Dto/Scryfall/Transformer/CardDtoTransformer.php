<?php

declare(strict_types=1);

namespace App\Dto\Scryfall\Transformer;

use App\Dto\Scryfall\Model\CardDto;
use App\Entity\Card;
use Ramsey\Uuid\Uuid;

final class CardDtoTransformer implements DtoTransformerInterface
{
    /**
     * @param CardDto $objectDto
     */
    public function transform($objectDto): Card
    {
        $uuid = Uuid::fromString($objectDto->id);
        $imageUrl = null;
        if (is_array($objectDto->image_uris)) {
            $imageUrl = $objectDto->image_uris['png'] ?? null;
        }

        return (new Card())
            ->setId($uuid)
            ->setName($objectDto->name)
            ->setType($objectDto->type_line)
            ->setColors($objectDto->color_identity)
            ->setDescription($objectDto->flavor_text)
            ->setImageUrl($imageUrl)
            ->setArtist($objectDto->artist)
        ;
    }
}
