<?php

namespace App\Event;

use App\Entity\Employe;

final class EmployeeActivatedEvent
{
    public function __construct(
        private readonly Employe $employee,
    ) {}

    public function getEmployee(): Employe
    {
        return $this->employee;
    }
}

