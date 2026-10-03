<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Module;

#[ORM\Entity]
class Quiz_question
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: "quiz_questions")]
    #[ORM\JoinColumn(name: 'module_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Module $module_id;

    #[ORM\Column(type: "text")]
    private string $question;

    #[ORM\Column(type: "string", length: 255)]
    private string $option_a;

    #[ORM\Column(type: "string", length: 255)]
    private string $option_b;

    #[ORM\Column(type: "string", length: 255)]
    private string $option_c;

    #[ORM\Column(type: "string", length: 255)]
    private string $option_d;

    #[ORM\Column(type: "string", length: 1)]
    private string $bonne_reponse;

    #[ORM\Column(type: "text")]
    private string $explication;

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

    public function getQuestion()
    {
        return $this->question;
    }

    public function setQuestion($value)
    {
        $this->question = $value;
    }

    public function getOption_a()
    {
        return $this->option_a;
    }

    public function setOption_a($value)
    {
        $this->option_a = $value;
    }

    public function getOption_b()
    {
        return $this->option_b;
    }

    public function setOption_b($value)
    {
        $this->option_b = $value;
    }

    public function getOption_c()
    {
        return $this->option_c;
    }

    public function setOption_c($value)
    {
        $this->option_c = $value;
    }

    public function getOption_d()
    {
        return $this->option_d;
    }

    public function setOption_d($value)
    {
        $this->option_d = $value;
    }

    public function getBonne_reponse()
    {
        return $this->bonne_reponse;
    }

    public function setBonne_reponse($value)
    {
        $this->bonne_reponse = $value;
    }

    public function getExplication()
    {
        return $this->explication;
    }

    public function setExplication($value)
    {
        $this->explication = $value;
    }
}
