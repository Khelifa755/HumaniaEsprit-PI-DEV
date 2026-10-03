<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Module_sous_section;

#[ORM\Entity]
class Sous_section_progression
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Employe_competence::class, inversedBy: "sous_section_progressions")]
    #[ORM\JoinColumn(name: 'employe_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Employe_competence $employe_id;

        #[ORM\ManyToOne(targetEntity: Module_sous_section::class, inversedBy: "sous_section_progressions")]
    #[ORM\JoinColumn(name: 'sous_section_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Module_sous_section $sous_section_id;

    #[ORM\Column(type: "string")]
    private string $statut;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_completion;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getEmploye_id()
    {
        return $this->employe_id;
    }

    public function setEmploye_id($value)
    {
        $this->employe_id = $value;
    }

    public function getSous_section_id()
    {
        return $this->sous_section_id;
    }

    public function setSous_section_id($value)
    {
        $this->sous_section_id = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getDate_completion()
    {
        return $this->date_completion;
    }

    public function setDate_completion($value)
    {
        $this->date_completion = $value;
    }
}
