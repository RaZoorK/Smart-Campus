<?php

namespace App\Entity;

use App\Repository\SeuilRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SeuilRepository::class)]
class Seuil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $type = null;

    #[ORM\Column(type: 'float')]
    #[Assert\GreaterThanOrEqual(
        propertyPath: "valeurMin",
        message: "La valeur maximale doit être supérieure ou égale à la valeur minimale."
    )]
    private ?float $valeurMax = null;

    #[ORM\Column(type: 'float')]
    #[Assert\LessThanOrEqual(
        propertyPath: "valeurMax",
        message: "La valeur minimale doit être inférieure ou égale à la valeur maximale."
    )]
    private ?float $valeurMin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getValeurMax(): ?float
    {
        return $this->valeurMax;
    }

    public function getValeurMin(): ?float
    {
        return $this->valeurMin;
    }

    public function setValeurMax(float $valeurMax): self
    {
        if ($this->valeurMin !== null && $valeurMax < $this->valeurMin) {
            throw new \InvalidArgumentException('La valeur maximale doit être supérieure ou égale à la valeur minimale.');
        }

        $this->valeurMax = $valeurMax;
        return $this;
    }

    public function setValeurMin(float $valeurMin): self
    {
        if ($this->valeurMax !== null && $valeurMin > $this->valeurMax) {
            throw new \InvalidArgumentException('La valeur minimale doit être inférieure ou égale à la valeur maximale.');
        }

        $this->valeurMin = $valeurMin;
        return $this;
    }

}
