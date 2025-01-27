<?php

namespace App\Entity;

use App\Model\Etat;
use App\Repository\SARepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SARepository::class)]
class SA
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $nomSA = null;

    #[ORM\Column(nullable: true)]
    private ?Etat $etat = null;

    #[ORM\OneToOne(mappedBy: 'SA', cascade: ['persist'])]
    private ?Salle $salle = null;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'saConcerne', orphanRemoval: true)]
    private Collection $notifications;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSA(): ?string
    {
        return $this->nomSA;
    }

    public function setNomSA(string $nomSA): static
    {
        $this->nomSA = $nomSA;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function getEtatValue(): string
    {
        return $this->etat->value;
    }

    public function setEtat(?Etat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): static
    {
        // unset the owning side of the relation if necessary
        if ($salle === null && $this->salle !== null) {
            $this->salle->setSA(null);
        }

        // set the owning side of the relation if necessary
        if ($salle !== null && $salle->getSA() !== $this) {
            $salle->setSA($this);
        }

        $this->salle = $salle;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setSaConcerne($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getSaConcerne() === $this) {
                $notification->setSaConcerne(null);
            }
        }

        return $this;
    }
}
