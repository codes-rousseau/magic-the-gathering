<?php

declare(strict_types=1);

namespace App\Dto\Scryfall\Transformer;

use App\Dto\Scryfall\Model\CardDto;
use App\Entity\Card;
use App\Entity\Color;
use App\Repository\ColorRepository;
use App\Service\DownloadFileService;
use Ramsey\Uuid\Uuid;
use RuntimeException;

final class CardDtoTransformer implements DtoTransformerInterface
{
    private DownloadFileService $downloadFile;
    private ColorRepository $colors;

    public function __construct(DownloadFileService $downloadFile, ColorRepository $colors)
    {
        $this->downloadFile = $downloadFile;
        $this->colors = $colors;
    }

    /**
     * @param CardDto $objectDto
     */
    public function transform($objectDto): Card
    {
        $uuid = Uuid::fromString($objectDto->id);

        $pathPublic = null;
        if (is_array($objectDto->image_uris)) {
            $imageUrl = $objectDto->image_uris['normal'] ?? null;
            $imageUrl = filter_var($imageUrl, FILTER_VALIDATE_URL);

            if (false !== $imageUrl) {
                $pathTemporaryFile = $this->downloadFile->downloadFileByHttpsUrl($imageUrl, $uuid->toString());
                $pathPublic = $this->downloadFile->moveFileInPublicDirectory($pathTemporaryFile, 'cards');
            }
        }

        $card = (new Card())
            ->setId($uuid)
            ->setName($objectDto->name)
            ->setType($objectDto->type_line)
            ->setDescription($objectDto->flavor_text)
            ->setImageUrl($pathPublic)
            ->setArtist($objectDto->artist)
        ;

        foreach ($objectDto->color_identity as $abbreviation) {
            $color = $this->colors->find($abbreviation);
            if (!$color instanceof Color) {
                throw new RuntimeException(sprintf('Abbreviation "%s" not found in your database.', $abbreviation));
            }

            $card->addColor($color);
        }

        return $card;
    }
}
