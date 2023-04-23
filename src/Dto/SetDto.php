<?php
namespace App\Dto;

class SetDto {

    public string $id;
    public string $code;
    public ?string $releasedAt;
    public string $name;
    public string $searchUri;
    public ?string $iconUri;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return SetDto
     */
    public function setId(string $id): SetDto
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return SetDto
     */
    public function setCode(string $code): SetDto
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReleasedAt(): ?string
    {
        return $this->releasedAt;
    }

    /**
     * @param string|null $releasedAt
     * @return SetDto
     */
    public function setReleasedAt(?string $releasedAt): SetDto
    {
        $this->releasedAt = $releasedAt;
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
     * @return SetDto
     */
    public function setName(string $name): SetDto
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearchUri(): string
    {
        return $this->searchUri;
    }

    /**
     * @param string $searchUri
     * @return SetDto
     */
    public function setSearchUri(string $searchUri): SetDto
    {
        $this->searchUri = $searchUri;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIconUri(): ?string
    {
        return $this->iconUri;
    }

    /**
     * @param string|null $iconUri
     * @return SetDto
     */
    public function setIconUri(?string $iconUri): SetDto
    {
        $this->iconUri = $iconUri;
        return $this;
    }


}