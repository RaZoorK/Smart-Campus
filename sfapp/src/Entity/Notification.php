<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use App\Model\TypeNotif;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notificationsEnvoyees')]
    private ?Utilisateur $expediteur = null;

    #[ORM\ManyToOne(inversedBy: 'notificationsReçues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $destinataire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $expediteurNom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $expediteurPrenom = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column(length: 255)]
    private ?string $sujet = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SA $saConcerne = null;

    #[ORM\Column]
    private ?bool $vue = null;

    #[ORM\Column(nullable: false)]
    private ?TypeNotif $type = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpediteur(): ?Utilisateur
    {
        return $this->expediteur;
    }

    public function setExpediteur(?Utilisateur $expediteur): static
    {
        $this->expediteur = $expediteur;

        return $this;
    }

    public function getDestinataire(): ?Utilisateur
    {
        return $this->destinataire;
    }

    public function setDestinataire(?Utilisateur $destinataire): static
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    public function getExpediteurNom(): ?string
    {
        return $this->expediteurNom;
    }

    public function setExpediteurNom(?string $expediteurNom): static
    {
        $this->expediteurNom = $expediteurNom;

        return $this;
    }

    public function getExpediteurPrenom(): ?string
    {
        return $this->expediteurPrenom;
    }

    public function setExpediteurPrenom(?string $expediteurPrenom): static
    {
        $this->expediteurPrenom = $expediteurPrenom;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    public function setSujet(string $sujet): static
    {
        $this->sujet = $sujet;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getSaConcerne(): ?SA
    {
        return $this->saConcerne;
    }

    public function setSaConcerne(?SA $saConcerne): static
    {
        $this->saConcerne = $saConcerne;

        return $this;
    }

    public function isVue(): ?bool
    {
        return $this->vue;
    }

    public function setVue(bool $vue): static
    {
        $this->vue = $vue;

        return $this;
    }

    public function getTypeNotif(): ?TypeNotif
    {
        return $this->type;
    }

    public function getTypeNotifValue(): string
    {
        return $this->type->value;
    }

    public function setTypeNotif(?TypeNotif $type): static
    {
        $this->type = $type;

        return $this;
    }
}
