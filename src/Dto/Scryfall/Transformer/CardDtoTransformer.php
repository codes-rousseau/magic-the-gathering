<?php

declare(strict_types=1);

namespace App\Dto\Scryfall\Transformer;

use App\Dto\Scryfall\Model\CardDto;
use App\Entity\Card;
use App\Service\DownloadFileService;
use Ramsey\Uuid\Uuid;

final class CardDtoTransformer implements DtoTransformerInterface
{
    private DownloadFileService $downloadFile;

    public function __construct(DownloadFileService $downloadFile)
    {
        $this->downloadFile = $downloadFile;
    }

    /**
     * @param CardDto $objectDto
     */
    public function transform($objectDto): Card
    {
        $uuid = Uuid::fromString($objectDto->id);

        $pathPublic = null;
        if (is_array($objectDto->image_uris)) {
            $imageUrl = $objectDto->image_uris['png'] ?? null;
            $pathTemporaryFile = $this->downloadFile->downloadFileByHttpsUrl($imageUrl, $uuid->toString());
            $pathPublic = $this->downloadFile->moveFileInPublicDirectory($pathTemporaryFile, 'cards');
        }

        return (new Card())
            ->setId($uuid)
            ->setName($objectDto->name)
            ->setType($objectDto->type_line)
            ->setColors($objectDto->color_identity)
            ->setDescription($objectDto->flavor_text)
            ->setImageUrl($pathPublic)
            ->setArtist($objectDto->artist)
        ;
    }
}
