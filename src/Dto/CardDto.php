<?php
namespace App\Dto;

class CardDto {
    public string $id;
    public string $name;
    public array $imageUris;
    public string $type;
    public array $colors;
    public string $description;
    public string $artist;
    public string $setId;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return CardDto
     */
    public function setId(string $id): CardDto
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return CardDto
     */
    public function setName(string $name): CardDto
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function getImageUris(): array
    {
        return $this->imageUris;
    }

    /**
     * @param array $imageUris
     * @return CardDto
     */
    public function setImageUris(array $imageUris): CardDto
    {
        $this->imageUris = $imageUris;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return CardDto
     */
    public function setType(string $type): CardDto
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public function getColors(): array
    {
        return $this->colors;
    }

    /**
     * @param array $colors
     * @return CardDto
     */
    public function setColors(array $colors): CardDto
    {
        $this->colors = $colors;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return CardDto
     */
    public function setDescription(string $description): CardDto
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getArtist(): string
    {
        return $this->artist;
    }

    /**
     * @param string $artist
     * @return CardDto
     */
    public function setArtist(string $artist): CardDto
    {
        $this->artist = $artist;
        return $this;
    }

    /**
     * @return string
     */
    public function getSetId(): string
    {
        return $this->setId;
    }

    /**
     * @param string $setId
     * @return CardDto
     */
    public function setSetId(string $setId): CardDto
    {
        $this->setId = $setId;
        return $this;
    }







}