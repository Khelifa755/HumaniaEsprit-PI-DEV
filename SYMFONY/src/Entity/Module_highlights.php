<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Module;

#[ORM\Entity]
class Module_highlights
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: "module_highlightss")]
    #[ORM\JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Module $module_id;

    #[ORM\Column(type: "integer")]
    private int $inscription_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $text_key;

    #[ORM\Column(type: "string", length: 20)]
    private string $color;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

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

    public function getText_key()
    {
        return $this->text_key;
    }

    public function setText_key($value)
    {
        $this->text_key = $value;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($value)
    {
        $this->color = $value;
    }

    public function getCreated_at()
    {
        return $this->created_at;
    }

    public function setCreated_at($value)
    {
        $this->created_at = $value;
    }
}
