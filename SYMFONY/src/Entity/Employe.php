<?php

namespace App\Entity;

use App\Repository\EmployeRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: EmployeRepository::class)]
class Employe
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $utilisateur_id;

    #[ORM\Column(type: "string", length: 50)]
    private string $matricule;

    #[ORM\Column(type: "string", length: 100)]
    private string $poste_actuel;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_embauche;

    #[ORM\Column(type: "string", length: 100)]
    private string $departement;

    #[ORM\Column(type: "integer")]
    private int $manager_id;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'applied';

    public function getUtilisateur_id()
    {
        return $this->utilisateur_id;
    }

    public function setUtilisateur_id($value)
    {
        $this->utilisateur_id = $value;
    }

    public function getMatricule()
    {
        return $this->matricule;
    }

    public function setMatricule($value)
    {
        $this->matricule = $value;
    }

    public function getPoste_actuel()
    {
        return $this->poste_actuel;
    }

    public function setPoste_actuel($value)
    {
        $this->poste_actuel = $value;
    }

    public function getDate_embauche()
    {
        return $this->date_embauche;
    }

    public function setDate_embauche($value)
    {
        $this->date_embauche = $value;
    }

    public function getDepartement()
    {
        return $this->departement;
    }

    public function setDepartement($value)
    {
        $this->departement = $value;
    }

    public function getManager_id()
    {
        return $this->manager_id;
    }

    public function setManager_id($value)
    {
        $this->manager_id = $value;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
