<?php

namespace App\Service\Plannification;

use App\Entity\Espaces;

class EspacesManager
{
    public function validate(Espaces $e): bool
    {
        if (empty($e->getNom())) {
            throw new \InvalidArgumentException('Nom obligatoire');
        }

        if ($e->getCapacite() <= 0) {
            throw new \InvalidArgumentException('Capacité invalide');
        }

        if ($e->getEtage() < 0) {
            throw new \InvalidArgumentException('Étage invalide');
        }

        if (empty($e->getUrlImage())) {
            throw new \InvalidArgumentException('Image obligatoire');
        }

        if (empty($e->getTypeEspace())) {
            throw new \InvalidArgumentException('Type obligatoire');
        }

        return true;
    }
}