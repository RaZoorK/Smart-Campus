<?php

namespace App\Entity;

use App\Model\Fonction;
use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Notification;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_IDENTIFIANT', fields: ['identifiant'])]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $identifiant = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $mail = null;

    #[ORM\Column(length: 255)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    private ?string $fonction = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbNotif = null;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'expediteur')]
    private Collection $notificationsEnvoyees;

    private ?string $plainPassword = null;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'destinataire', orphanRemoval: true)]
    private Collection $notificationsReçues;

    public function __construct()
    {
        $this->notificationsEnvoyees = new ArrayCollection();
        $this->notificationsReçues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifiant(): ?string
    {
        return $this->identifiant;
    }

    public function setIdentifiant(string $identifiant): static
    {
        $this->identifiant = $identifiant;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->identifiant;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        if ($plainPassword) {
            $this->password = null; // Prevent hashing the password if set to plain password
        }

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        if ($this->plainPassword === null) {
            // Si plainPassword est null, utiliser le mot de passe passé comme argument et le hacher
            $this->password = $password;
        } else {
            // Si plainPassword est défini, le hacher
            $this->password = $password;
        }
        return $this;
    }


    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $Nom): static
    {
        $this->nom = $Nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $Prenom): static
    {
        $this->prenom = $Prenom;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $Mail): static
    {
        $this->mail = $Mail;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $Telephone): static
    {
        $this->telephone = $Telephone;

        return $this;
    }
    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(?string $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getNbNotif(): ?int
    {
        return $this->nbNotif;
    }

    public function setNbNotif(?int $nbNotif): static
    {
        $this->nbNotif = $nbNotif;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotificationsEnvoyees(): Collection
    {
        return $this->notificationsEnvoyees;
    }

    public function addNotificationsEnvoyee(Notification $notificationsEnvoyee): static
    {
        if (!$this->notificationsEnvoyees->contains($notificationsEnvoyee)) {
            $this->notificationsEnvoyees->add($notificationsEnvoyee);
            $notificationsEnvoyee->setExpediteur($this);
        }

        return $this;
    }

    public function removeNotificationsEnvoyee(Notification $notificationsEnvoyee): static
    {
        if ($this->notificationsEnvoyees->removeElement($notificationsEnvoyee)) {
            // set the owning side to null (unless already changed)
            if ($notificationsEnvoyee->getExpediteur() === $this) {
                $notificationsEnvoyee->setExpediteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotificationsReçues(): Collection
    {
        return $this->notificationsReçues;
    }

    public function addNotificationsRecue(Notification $notificationsRecue): static
    {
        if (!$this->notificationsReçues->contains($notificationsRecue)) {
            $this->notificationsReçues->add($notificationsRecue);
            $notificationsRecue->setDestinataire($this);
        }

        return $this;
    }

    public function removeNotificationsRecue(Notification $notificationsRecue): static
    {
        if ($this->notificationsReçues->removeElement($notificationsRecue)) {
            // set the owning side to null (unless already changed)
            if ($notificationsRecue->getDestinataire() === $this) {
                $notificationsRecue->setDestinataire(null);
            }
        }

        return $this;
    }
}
