<?php
// src/Entity/Type_conge.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'type_conge')]
class Type_conge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $libelle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    // ══════════════════════════════════════════════════════════════
    // ✅ MÉTHODE MAGIQUE __toString() - OBLIGATOIRE POUR FORMULAIRES
    // ══════════════════════════════════════════════════════════════
    public function __toString(): string
    {
        return $this->libelle ?? 'Type inconnu';
    }

    // ══════════════════════════════════════════════════════════════
    // GETTERS ET SETTERS
    // ══════════════════════════════════════════════════════════════

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
}