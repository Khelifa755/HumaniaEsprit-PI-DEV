<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Module;

#[ORM\Entity]
class Module_progression
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "module_progressions")]
    #[ORM\JoinColumn(name: 'employe_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $employe_id;

        #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: "module_progressions")]
    #[ORM\JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Module $module_id;

    #[ORM\Column(type: "string")]
    private string $statut;

    #[ORM\Column(type: "integer")]
    private int $score_quiz;

    #[ORM\Column(type: "date")]
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

    public function getModule_id()
    {
        return $this->module_id;
    }

    public function setModule_id($value)
    {
        $this->module_id = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getScore_quiz()
    {
        return $this->score_quiz;
    }

    public function setScore_quiz($value)
    {
        $this->score_quiz = $value;
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
