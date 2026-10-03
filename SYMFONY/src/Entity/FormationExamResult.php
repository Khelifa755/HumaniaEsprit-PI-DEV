<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "formation_exam_result")]
class FormationExamResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: "formation_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?Formation $formation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: "employe_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?Utilisateur $employe = null;

    #[ORM\Column]
    private int $scorePct;

    #[ORM\Column]
    private int $nbCorrect = 0;

    #[ORM\Column]
    private int $nbTotal = 0;

    #[ORM\Column(type: "boolean")]
    private bool $passed = false;

    #[ORM\Column]
    private int $attemptNumber = 1;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $datePassage;

    #[ORM\Column(type: "boolean", nullable: true)]
    private ?bool $competenceUpdated = false;

    public function __construct()
    {
        $this->datePassage = new \DateTime();
    }
}