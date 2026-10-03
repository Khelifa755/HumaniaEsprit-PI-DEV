<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Module;

#[ORM\Entity]
class Module_notes
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: "module_notess")]
    #[ORM\JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Module $module_id;

    #[ORM\Column(type: "integer")]
    private int $inscription_id;

    #[ORM\Column(type: "text")]
    private string $note_text;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $updated_at;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getModule_id()
    {
        return $this->module_id;
    }

    public function setModule_id($value)
    {
        $this->module_id = $value;
    }

    public function getInscription_id()
    {
        return $this->inscription_id;
    }

    public function setInscription_id($value)
    {
        $this->inscription_id = $value;
    }

    public function getNote_text()
    {
        return $this->note_text;
    }

    public function setNote_text($value)
    {
        $this->note_text = $value;
    }

    public function getCreated_at()
    {
        return $this->created_at;
    }

    public function setCreated_at($value)
    {
        $this->created_at = $value;
    }

    public function getUpdated_at()
    {
        return $this->updated_at;
    }

    public function setUpdated_at($value)
    {
        $this->updated_at = $value;
    }
}
