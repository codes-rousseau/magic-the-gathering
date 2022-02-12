<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ColorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ColorRepository::class)
 */
class Color
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     */
    private string $abbreviation;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $basicLand;

    public function getAbbreviation(): string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(string $abbreviation): self
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBasicLand(): string
    {
        return $this->basicLand;
    }

    public function setBasicLand(string $basicLand): self
    {
        $this->basicLand = $basicLand;

        return $this;
    }
}
