<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "formation_final_exam_question")]
class FormationFinalExamQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: "formation_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?Formation $formation = null;

    #[ORM\Column(type: "text")]
    private string $question;

    #[ORM\Column(length: 500)]
    private string $optionA;

    #[ORM\Column(length: 500)]
    private string $optionB;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $optionC = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $optionD = null;

    #[ORM\Column(length: 1)]
    private string $bonneReponse;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $explication = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $moduleRef = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }
}