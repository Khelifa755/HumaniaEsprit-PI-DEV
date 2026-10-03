<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Espaces
{
    public function __construct()
    {
        $this->reservation_espacess = new ArrayCollection();
        $this->reunions = new ArrayCollection();
    }

    // FIX 1 — property.unusedType: $id is never assigned int directly by user code,
    // only by Doctrine. Keeping ?int is correct; PHPStan at high levels wants you
    // to acknowledge it stays null until persisted. No change needed to the declaration,
    // but the getter must reflect the nullable type explicitly.
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: 'string', length: 255)]
    private string $nom;

    #[Assert\Positive(message: 'La capacité doit être au moins 1.')]
    #[ORM\Column(type: 'integer')]
    private int $capacite;

    #[Assert\PositiveOrZero(message: "L'étage doit être positif ou zéro.")]
    #[ORM\Column(type: 'integer')]
    private int $etage;

    #[Assert\Length(max: 5000)]
    #[ORM\Column(name: 'listeEquipements', type: 'text')]
    private string $listeEquipements;

    #[Assert\Length(max: 2000)]
    #[ORM\Column(name: 'urlImage', type: 'text')]
    private string $urlImage;

    #[ORM\Column(type: 'boolean')]
    private bool $disponible;

    #[Assert\NotBlank(message: "Le type d'espace est obligatoire.")]
    #[Assert\Length(max: 255)]
    #[ORM\Column(name: 'typeEspace', type: 'string')]
    private string $typeEspace;

    // FIX 2 — missingType.return on getId(): return type is ?int (nullable)
    public function getId(): ?int
    {
        return $this->id;
    }

    // FIX 3 — missingType.return on getNom(): return type is string
    public function getNom(): string
    {
        return $this->nom;
    }

    // FIX 4 — missingType.return + missingType.parameter on setNom()
    public function setNom(string $value): void
    {
        $this->nom = $value;
    }

    // FIX 5 — missingType.return on getCapacite(): return type is int
    public function getCapacite(): int
    {
        return $this->capacite;
    }

    // FIX 6 — missingType.return + missingType.parameter on setCapacite()
    public function setCapacite(int $value): void
    {
        $this->capacite = $value;
    }

    // FIX 7 — missingType.return on getEtage(): return type is int
    public function getEtage(): int
    {
        return $this->etage;
    }

    // FIX 8 — missingType.return + missingType.parameter on setEtage()
    public function setEtage(int $value): void
    {
        $this->etage = $value;
    }

    // FIX 9 — missingType.return on getListeEquipements(): return type is string
    public function getListeEquipements(): string
    {
        return $this->listeEquipements;
    }

    // FIX 10 — missingType.return + missingType.parameter on setListeEquipements()
    public function setListeEquipements(string $value): void
    {
        $this->listeEquipements = $value;
    }

    // FIX 11 — missingType.return on getUrlImage(): return type is string
    public function getUrlImage(): string
    {
        return $this->urlImage;
    }

    // FIX 12 — missingType.return + missingType.parameter on setUrlImage()
    public function setUrlImage(string $value): void
    {
        $this->urlImage = $value;
    }

    // FIX 13 — missingType.return on getDisponible(): return type is bool
    public function getDisponible(): bool
    {
        return $this->disponible;
    }

    // FIX 14 — missingType.return + missingType.parameter on setDisponible()
    public function setDisponible(bool $value): void
    {
        $this->disponible = $value;
    }

    // FIX 15 — missingType.return on getTypeEspace(): return type is string
    public function getTypeEspace(): string
    {
        return $this->typeEspace;
    }

    // FIX 16 — missingType.return + missingType.parameter on setTypeEspace()
    public function setTypeEspace(string $value): void
    {
        $this->typeEspace = $value;
    }

    // FIX 17 — missingType.generics: Collection must declare its generic types <TKey, T>
    // TKey = int (Doctrine uses integer keys), T = Reservation_espaces
    #[ORM\OneToMany(mappedBy: 'idEspace', targetEntity: Reservation_espaces::class)]
    private Collection $reservation_espacess;

    /** @return Collection<int, Reservation_espaces> */
    public function getReservation_espacess(): Collection
    {
        return $this->reservation_espacess;
    }

    public function addReservation_espaces(Reservation_espaces $reservation_espaces): self
    {
        if (!$this->reservation_espacess->contains($reservation_espaces)) {
            $this->reservation_espacess->add($reservation_espaces);
            $reservation_espaces->setIdEspace($this);
        }

        return $this;
    }

    public function removeReservation_espaces(Reservation_espaces $reservation_espaces): self
    {
        if ($this->reservation_espacess->removeElement($reservation_espaces) && $reservation_espaces->getIdEspace() === $this) {
            $reservation_espaces->setIdEspace(null);
        }

        return $this;
    }

    // FIX 18 — missingType.generics: Collection<int, Reunion>
    #[ORM\OneToMany(mappedBy: 'idSalle', targetEntity: Reunion::class)]
    private Collection $reunions;

    /** @return Collection<int, Reunion> */
    public function getReunions(): Collection
    {
        return $this->reunions;
    }

    public function addReunion(Reunion $reunion): self
    {
        if (!$this->reunions->contains($reunion)) {
            $this->reunions->add($reunion);
            $reunion->setIdSalle($this);
        }

        return $this;
    }

    public function removeReunion(Reunion $reunion): self
    {
        if ($this->reunions->removeElement($reunion) && $reunion->getIdSalle() === $this) {
            $reunion->setIdSalle(null);
        }

        return $this;
    }
}