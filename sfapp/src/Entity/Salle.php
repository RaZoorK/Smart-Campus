<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Nom = null;

    #[ORM\OneToOne(inversedBy: 'salle', cascade: ['persist'])]
    private ?SA $SA = null;

    #[ORM\Column(length: 255)]
    private ?string $batiment = null;

    #[ORM\Column(nullable: false)]
    private ?int $etage = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $nom): static
    {
        $this->Nom = $nom;

        return $this;
    }

    public function getSA(): ?SA
    {
        return $this->SA;
    }

    public function setSA(?SA $SA): static
    {
        if($SA == null){
            $this->SA = NULL;
        }
        else{
            $this->SA = $SA;
        }


        return $this;
    }

    public function hasSA(): bool
    {
        return $this->SA !== null;
    }

    public function getBatiment(): ?string
    {
        return $this->batiment;
    }

    public function setBatiment(?string $batiment): self
    {
        $this->batiment = $batiment;

        return $this;
    }


    public function setEtage(?int $etage): static
    {
        $this->etage = $etage;

        return $this;
    }

    public function getEtage(): ?int
    {
        return $this->etage;
    }


}
