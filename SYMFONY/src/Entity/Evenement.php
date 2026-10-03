<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
class Evenement
{
    public function __construct()
    {
        $this->participation_evenements = new ArrayCollection();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: 'string', length: 255)]
    private string $titre;

    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Assert\Length(max: 10000)]
    #[ORM\Column(type: 'text')]
    private string $description;

    #[Assert\NotNull]
    #[ORM\Column(name: 'dateEvenement', type: 'date_immutable')]
    private \DateTimeInterface $dateEvenement;

    #[Assert\NotNull]
    #[ORM\Column(name: 'dateHeureDebut', type: 'datetime_immutable')]
    private \DateTimeInterface $dateHeureDebut;

    #[Assert\NotNull]
    #[ORM\Column(name: 'dateHeureFin', type: 'datetime_immutable')]
    private \DateTimeInterface $dateHeureFin;

    #[Assert\NotBlank(message: 'Le lieu est obligatoire.')]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: 'string', length: 255)]
    private string $lieu;

    #[Assert\Positive(message: 'Le nombre de participants doit être au moins 1.')]
    #[ORM\Column(name: 'nbParticipantsMax', type: 'integer')]
    private int $nbParticipantsMax;

    #[Assert\Length(max: 5000)]
    #[ORM\Column(name: 'participantsInscrits', type: 'text')]
    private string $participantsInscrits;

    #[Assert\NotBlank(message: 'Indiquez qui crée l’événement.')]
    #[Assert\Length(max: 255)]
    #[ORM\Column(name: 'creePar', type: 'string', length: 255)]
    private string $creePar;

    #[Assert\NotNull]
    #[ORM\Column(name: 'creeLe', type: 'datetime_immutable')]
    private \DateTimeInterface $creeLe;

    #[ORM\OneToMany(mappedBy: 'idEvenement', targetEntity: Participation_evenement::class)]
    private Collection $participation_evenements;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $value): self
    {
        $this->titre = $value;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $value): self
    {
        $this->description = $value;

        return $this;
    }

    public function getDateEvenement(): \DateTimeInterface
    {
        return $this->dateEvenement;
    }

    public function setDateEvenement(\DateTimeInterface $value): self
    {
        $this->dateEvenement = $value;

        return $this;
    }

    public function getDateHeureDebut(): \DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $value): self
    {
        $this->dateHeureDebut = $value;

        return $this;
    }

    public function getDateHeureFin(): \DateTimeInterface
    {
        return $this->dateHeureFin;
    }

    public function setDateHeureFin(\DateTimeInterface $value): self
    {
        $this->dateHeureFin = $value;

        return $this;
    }

    public function getLieu(): string
    {
        return $this->lieu;
    }

    public function setLieu(string $value): self
    {
        $this->lieu = $value;

        return $this;
    }

    public function getNbParticipantsMax(): int
    {
        return $this->nbParticipantsMax;
    }

    public function setNbParticipantsMax(int $value): self
    {
        $this->nbParticipantsMax = $value;

        return $this;
    }

    public function getParticipantsInscrits(): string
    {
        return $this->participantsInscrits;
    }

    public function setParticipantsInscrits(string $value): self
    {
        $this->participantsInscrits = $value;

        return $this;
    }

    public function getCreePar(): string
    {
        return $this->creePar;
    }

    public function setCreePar(string $value): self
    {
        $this->creePar = $value;

        return $this;
    }

    public function getCreeLe(): \DateTimeInterface
    {
        return $this->creeLe;
    }

    public function setCreeLe(\DateTimeInterface $value): self
    {
        $this->creeLe = $value;

        return $this;
    }

    public function getParticipation_evenements(): Collection
    {
        return $this->participation_evenements;
    }

    public function addParticipation_evenement(Participation_evenement $participation_evenement): self
    {
        if (!$this->participation_evenements->contains($participation_evenement)) {
            $this->participation_evenements->add($participation_evenement);
            $participation_evenement->setIdEvenement($this);
        }

        return $this;
    }

    public function removeParticipation_evenement(Participation_evenement $participation_evenement): self
    {
        if ($this->participation_evenements->removeElement($participation_evenement) && $participation_evenement->getIdEvenement() === $this) {
            $participation_evenement->setIdEvenement(null);
        }

        return $this;
    }

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        if ($this->dateHeureFin <= $this->dateHeureDebut) {
            $context->buildViolation('La fin doit être après le début.')
                ->atPath('dateHeureFin')
                ->addViolation();
        }
    }
}
