<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Module_sous_section;

#[ORM\Entity]
class Exercice_soumission
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "exercice_soumissions")]
    #[ORM\JoinColumn(name: 'employe_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $employe_id;

        #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: "exercice_soumissions")]
    #[ORM\JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Module $module_id;

        #[ORM\ManyToOne(targetEntity: Module_sous_section::class, inversedBy: "exercice_soumissions")]
    #[ORM\JoinColumn(name: 'sous_section_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Module_sous_section $sous_section_id;

    #[ORM\Column(type: "text")]
    private string $contenu_rendu;

    #[ORM\Column(type: "integer")]
    private int $note_ia;

    #[ORM\Column(type: "text")]
    private string $feedback_ia;

    #[ORM\Column(type: "string")]
    private string $statut;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_soumission;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_correction;

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

    public function getSous_section_id()
    {
        return $this->sous_section_id;
    }

    public function setSous_section_id($value)
    {
        $this->sous_section_id = $value;
    }

    public function getContenu_rendu()
    {
        return $this->contenu_rendu;
    }

    public function setContenu_rendu($value)
    {
        $this->contenu_rendu = $value;
    }

    public function getNote_ia()
    {
        return $this->note_ia;
    }

    public function setNote_ia($value)
    {
        $this->note_ia = $value;
    }

    public function getFeedback_ia()
    {
        return $this->feedback_ia;
    }

    public function setFeedback_ia($value)
    {
        $this->feedback_ia = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getDate_soumission()
    {
        return $this->date_soumission;
    }

    public function setDate_soumission($value)
    {
        $this->date_soumission = $value;
    }

    public function getDate_correction()
    {
        return $this->date_correction;
    }

    public function setDate_correction($value)
    {
        $this->date_correction = $value;
    }
}
