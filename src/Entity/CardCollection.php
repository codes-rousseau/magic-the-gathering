<?php

namespace App\Entity;

use App\Repository\CardCollectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CardCollectionRepository::class)
 */
class CardCollection
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
    private $code;

    /**
     * @ORM\Column(type="date")
     */
    private $publishedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $svg;

    /**
     * @ORM\OneToMany(targetEntity=Card::class, mappedBy="collection")
     */
    private $Card;

    public function __construct()
    {
        $this->Card = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSvg(): ?string
    {
        return $this->svg;
    }

    public function setSvg(string $svg): self
    {
        $this->svg = $svg;

        return $this;
    }

    /**
     * @return CardCollection<int, Card>
     */
    public function getCard(): CardCollection
    {
        return $this->Card;
    }

    public function addCard(Card $card): self
    {
        if (!$this->Card->contains($card)) {
            $this->Card[] = $card;
            $card->setCollection($this);
        }

        return $this;
    }

    public function removeCard(Card $card): self
    {
        if ($this->Card->removeElement($card)) {
            // set the owning side to null (unless already changed)
            if ($card->getCollection() === $this) {
                $card->setCollection(null);
            }
        }

        return $this;
    }
}
