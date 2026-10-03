<?php

namespace App\Entity;

use App\Enum\Role;
use App\Repository\Utilisateur\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[ORM\HasLifecycleCallbacks]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface, TotpTwoFactorInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 100, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(name: 'prenom', type: 'string', length: 100, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(name: 'email', type: 'string', length: 255, unique: true, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(name: 'username', type: 'string', length: 100, unique: true, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(name: 'numtel', type: 'string', length: 20, nullable: true)]
    private ?string $numtel = null;

    #[ORM\Column(name: 'pdp', type: 'string', length: 500, nullable: true)]
    private ?string $pdp = null;

    #[ORM\Column(name: 'mot_de_passe', type: 'string', length: 255, nullable: true)]
    private ?string $motDePasse = null;

    #[ORM\Column(name: 'role', type: 'string', length: 50, nullable: true)]
    private ?string $roleValue = null;

    #[ORM\Column(name: 'statut', type: 'string', length: 50, nullable: true, options: ['default' => 'Actif'])]
    private ?string $statut = 'Actif';

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(name: 'mfa_enabled', type: 'boolean', options: ['default' => false])]
    private bool $mfaEnabled = false;

    #[ORM\Column(name: 'mfa_secret', type: 'string', length: 255, nullable: true)]
    private ?string $mfaSecret = null;

    #[ORM\Column(name: 'donnees_faciales', type: 'string', length: 255, nullable: true)]
    private ?string $donneesFaciales = null;


    #[ORM\Column(name: 'posteActuel', type: 'string', length: 255, nullable: true)]
    private ?string $posteActuel = null;

    #[ORM\Column(name: 'manager_id', type: 'integer', nullable: true)]
    private ?int $managerId = null;

    #[ORM\Column(name: 'matricule', type: 'string', length: 100, nullable: true)]
    private ?string $matricule = null;

    #[ORM\Column(name: 'dateEmbauche', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateEmbauche = null;

    #[ORM\Column(name: 'departement', type: 'string', length: 255, nullable: true)]
    private ?string $departement = null;

    #[ORM\Column(name: 'is_online', type: 'boolean', options: ['default' => false])]
    private bool $isOnline = false;

    #[ORM\Column(name: 'last_seen', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastSeen = null;

    // ── Relations ORM ────────────────────────────────────────────────────────

    #[ORM\OneToMany(mappedBy: 'employe_id', targetEntity: Pdi::class)]
    private Collection $pdis;

    #[ORM\OneToMany(mappedBy: 'employe_id', targetEntity: Competenceemploye::class)]
    private Collection $competenceemployes;

    #[ORM\OneToMany(mappedBy: 'employe_id', targetEntity: Inscriptionformation::class)]
    private Collection $inscriptionformations;

    #[ORM\OneToMany(mappedBy: 'employe_id', targetEntity: Module_progression::class)]
    private Collection $module_progressions;

    #[ORM\OneToMany(mappedBy: 'employe_id', targetEntity: Resultatevaluation::class)]
    private Collection $resultatevaluations;

    #[ORM\OneToMany(mappedBy: 'employe_id', targetEntity: Exercice_soumission::class)]
    private Collection $exercice_soumissions;

    public function __construct()
    {
        $this->pdis = new ArrayCollection();
        $this->competenceemployes = new ArrayCollection();
        $this->inscriptionformations = new ArrayCollection();
        $this->module_progressions = new ArrayCollection();
        $this->resultatevaluations = new ArrayCollection();
        $this->exercice_soumissions = new ArrayCollection();
    }

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if ($this->dateCreation === null)
            $this->dateCreation = new \DateTime();
        if ($this->statut === null)
            $this->statut = 'Actif';
    }

    // ── UserInterface ─────────────────────────────────────────────────────────

    public function getUserIdentifier(): string
    {
        return $this->email ?? $this->username ?? '';
    }

    public function getRoles(): array
    {
        $role = $this->getRole();
        return ['ROLE_' . ($role?->value ?? 'EMPLOYE'), 'ROLE_USER'];
    }

    public function getPassword(): ?string
    {
        return $this->motDePasse;
    }
    public function eraseCredentials(): void
    {
    }

    // ── TwoFactorInterface (scheb/2fa-bundle) ─────────────────────────────────

    public function isTotpAuthenticationEnabled(): bool
    {
        return $this->mfaEnabled && $this->mfaSecret !== null;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->email ?? '';
    }

    public function getTotpAuthenticationConfiguration(): TotpConfigurationInterface
    {
        return new TotpConfiguration($this->mfaSecret, TotpConfiguration::ALGORITHM_SHA1, 30, 6);
    }

    // ── Role helpers ──────────────────────────────────────────────────────────

    public function getRole(): ?Role
    {
        if ($this->roleValue === null)
            return null;
        try {
            return Role::from($this->roleValue);
        } catch (\ValueError) {
            return Role::EMPLOYE;
        }
    }

    public function setRole(?Role $role): static
    {
        $this->roleValue = $role?->value;
        return $this;
    }

    public function getRoleValue(): ?string
    {
        return $this->roleValue;
    }
    public function setRoleValue(?string $v): static
    {
        $this->roleValue = $v;
        return $this;
    }

    // ── Display helpers ───────────────────────────────────────────────────────

    public function getFullName(): string
    {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
    }

    public function getInitials(): string
    {
        $i = '';
        if ($this->prenom)
            $i .= strtoupper($this->prenom[0]);
        if ($this->nom)
            $i .= strtoupper($this->nom[0]);
        return $i ?: '?';
    }

    public function isActive(): bool
    {
        return strcasecmp($this->statut ?? '', 'actif') === 0;
    }

    // ── Getters / Setters ─────────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }
    public function setNom(?string $v): static
    {
        $this->nom = $v;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }
    public function setPrenom(?string $v): static
    {
        $this->prenom = $v;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(?string $v): static
    {
        $this->email = $v;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
    public function setUsername(?string $v): static
    {
        $this->username = $v;
        return $this;
    }

    public function getNumtel(): ?string
    {
        return $this->numtel;
    }
    public function setNumtel(?string $v): static
    {
        $this->numtel = $v;
        return $this;
    }

    public function getPdp(): ?string
    {
        return $this->pdp;
    }
    public function setPdp(?string $v): static
    {
        $this->pdp = $v;
        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }
    public function setMotDePasse(?string $v): static
    {
        $this->motDePasse = $v;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }
    public function setStatut(?string $v): static
    {
        $this->statut = $v;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }
    public function setDateCreation(?\DateTimeInterface $v): static
    {
        $this->dateCreation = $v;
        return $this;
    }

    public function isMfaEnabled(): bool
    {
        return $this->mfaEnabled;
    }
    public function setMfaEnabled(bool $v): static
    {
        $this->mfaEnabled = $v;
        return $this;
    }

    public function getMfaSecret(): ?string { return $this->mfaSecret; }
    public function setMfaSecret(?string $v): static { $this->mfaSecret = $v; return $this; }

    public function getDonneesFaciales(): ?string { return $this->donneesFaciales; }
    public function setDonneesFaciales(?string $v): static { $this->donneesFaciales = $v; return $this; }


    public function getPosteActuel(): ?string
    {
        return $this->posteActuel;
    }
    public function setPosteActuel(?string $v): static
    {
        $this->posteActuel = $v;
        return $this;
    }

    public function getManagerId(): ?int
    {
        return $this->managerId;
    }
    public function setManagerId(?int $v): static
    {
        $this->managerId = $v;
        return $this;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }
    public function setMatricule(?string $v): static
    {
        $this->matricule = $v;
        return $this;
    }

    public function getDateEmbauche(): ?\DateTimeInterface
    {
        return $this->dateEmbauche;
    }
    public function setDateEmbauche(?\DateTimeInterface $v): static
    {
        $this->dateEmbauche = $v;
        return $this;
    }

    public function getDepartement(): ?string
    {
        return $this->departement;
    }
    public function setDepartement(?string $v): static
    {
        $this->departement = $v;
        return $this;
    }

    public function isOnline(): bool
    {
        return $this->isOnline;
    }
    public function setIsOnline(bool $v): static
    {
        $this->isOnline = $v;
        return $this;
    }

    public function getLastSeen(): ?\DateTimeInterface
    {
        return $this->lastSeen;
    }
    public function setLastSeen(?\DateTimeInterface $v): static
    {
        $this->lastSeen = $v;
        return $this;
    }

    // ── Collections ───────────────────────────────────────────────────────────

    public function getPdis(): Collection
    {
        return $this->pdis;
    }
    public function getCompetenceemployes(): Collection
    {
        return $this->competenceemployes;
    }
    public function getInscriptionformations(): Collection
    {
        return $this->inscriptionformations;
    }
    public function getModule_progressions(): Collection
    {
        return $this->module_progressions;
    }
    public function getResultatevaluations(): Collection
    {
        return $this->resultatevaluations;
    }
    public function getExercice_soumissions(): Collection
    {
        return $this->exercice_soumissions;
    }
}