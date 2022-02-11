<?php

declare(strict_types=1);

namespace App\Dto\Scryfall\Transformer;

use App\Dto\Scryfall\Model\SetDto;
use App\Entity\Set;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final class SetDtoTransformer implements DtoTransformerInterface
{
    /**
     * @param SetDto $objectDto
     */
    public function transform($objectDto): Set
    {
        $uuid = Uuid::fromString($objectDto->id);
        $releasedAt = (null !== $objectDto->released_at)
            ? DateTimeImmutable::createFromFormat('Y-m-d', $objectDto->released_at)
            : null;

        return (new Set())
            ->setId($uuid)
            ->setName($objectDto->name)
            ->setCode($objectDto->code)
            ->setIconUrl($objectDto->icon_svg_uri)
            ->setReleasedAt($releasedAt)
        ;
    }
}
