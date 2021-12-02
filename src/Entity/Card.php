<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CardRepository::class)
 */
class Card
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $imagePath;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $typeLine;

    /**
     * @ORM\Column(type="array", length=10)
     */
    private array $colorIdentity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $oracleText;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $artist;

    /**
     * @ORM\ManyToOne(targetEntity=Set::class, inversedBy="cards", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $set;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return Card
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Card
    {
        $this->name = $name;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): Card
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getTypeLine(): string
    {
        return $this->typeLine;
    }

    public function setTypeLine(string $typeLine): Card
    {
        $this->typeLine = $typeLine;

        return $this;
    }

    /**
     * @return array
     */
    public function getColorIdentity(): array
    {
        return $this->colorIdentity;
    }

    /**
     * @param array $colorIdentity
     * @return Card
     */
    public function setColorIdentity(array $colorIdentity): Card
    {
        $this->colorIdentity = $colorIdentity;
        return $this;
    }


    public function getOracleText(): ?string
    {
        return $this->oracleText;
    }

    public function setOracleText(?string $oracleText): Card
    {
        $this->oracleText = $oracleText;

        return $this;
    }

    public function getArtist(): ?string
    {
        return $this->artist;
    }

    public function setArtist(?string $artist): Card
    {
        $this->artist = $artist;

        return $this;
    }

    public function getSet(): ?Set
    {
        return $this->set;
    }

    public function setSet(?Set $set): self
    {
        $this->set = $set;

        return $this;
    }
}
