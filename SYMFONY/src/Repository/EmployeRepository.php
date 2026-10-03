<?php

namespace App\Repository;

use App\Entity\Employe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EmployeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employe::class);
    }

    public function findFresh(int $utilisateurId): ?Employe
    {
        $em = $this->getEntityManager();
        $employee = $this->find($utilisateurId);

        if ($employee !== null) {
            $em->refresh($employee);
        }

        return $employee;
    }
}

