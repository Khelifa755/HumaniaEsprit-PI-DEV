<?php

namespace App\Service\Utilisateur;

use App\Entity\Candidature;
use Doctrine\ORM\EntityManagerInterface;

class CandidatureService
{
    private $repository;

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        $this->repository = $em->getRepository(Candidature::class);
    }

    public function getAcceptedCandidatures(): array
    {
        return $this->repository->findAcceptedWithConversionFlag();
    }

    public function isDejaConverti(int $candidatureId): bool
    {
        return $this->repository->isDejaConverti($candidatureId);
    }

    public function setEmployeId(int $candidatureId, int $employeId): void
    {
        $this->repository->setEmployeId($candidatureId, $employeId);
    }
}