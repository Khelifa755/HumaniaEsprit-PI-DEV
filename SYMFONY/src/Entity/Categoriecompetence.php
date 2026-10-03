<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Categoriecompetence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le libellé est obligatoire.")]
    #[Assert\Length(
        min: 2, max: 255,
        minMessage: "Le libellé doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le libellé ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $libelle;

    #[ORM\Column(type: "string", length: 50)]
    #[Assert\NotBlank(message: "La couleur est obligatoire.")]
    #[Assert\Regex(
        pattern: "/^#[0-9A-Fa-f]{6}$/",
        message: "La couleur doit être un code hexadécimal valide (ex: #FF5733)."
    )]
    private string $couleur;

    #[ORM\OneToMany(mappedBy: "categorie_id", targetEntity: Competence::class)]
    private Collection $competences;

    public function __construct()
    {
        $this->competences = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getLibelle(): ?string { return $this->libelle; }
    public function setLibelle(string $value): self { $this->libelle = $value; return $this; }
    public function getCouleur(): ?string { return $this->couleur; }
    public function setCouleur(string $value): self { $this->couleur = $value; return $this; }
    public function getCompetences(): Collection { return $this->competences; }
}