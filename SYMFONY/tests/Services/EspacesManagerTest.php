<?php

namespace App\Tests\Service;

use App\Entity\Espaces;
use App\Service\Plannification\EspacesManager;
use PHPUnit\Framework\TestCase;

class EspacesManagerTest extends TestCase
{
    public function getValidEspaces(): Espaces
    {
        $e = new Espaces();
        $e->setNom('Salle A');
        $e->setCapacite(10);
        $e->setEtage(1);
        $e->setUrlImage('image.jpg');
        $e->setTypeEspace('Bureau');
        $e->setDisponible(true);

        return $e;
    }

    public function testValidEspaces()
    {
        $manager = new EspacesManager();
        $this->assertTrue($manager->validate($this->getValidEspaces()));
    }

    public function testNomVide()
    {
        $this->expectException(\InvalidArgumentException::class);

        $e = $this->getValidEspaces();
        $e->setNom('');

        (new EspacesManager())->validate($e);
    }

    public function testCapaciteNegative()
    {
        $this->expectException(\InvalidArgumentException::class);

        $e = $this->getValidEspaces();
        $e->setCapacite(-5);

        (new EspacesManager())->validate($e);
    }

    public function testEtageNegatif()
    {
        $this->expectException(\InvalidArgumentException::class);

        $e = $this->getValidEspaces();
        $e->setEtage(-1);

        (new EspacesManager())->validate($e);
    }

    public function testImageVide()
    {
        $this->expectException(\InvalidArgumentException::class);

        $e = $this->getValidEspaces();
        $e->setUrlImage('');

        (new EspacesManager())->validate($e);
    }

    public function testTypeVide()
    {
        $this->expectException(\InvalidArgumentException::class);

        $e = $this->getValidEspaces();
        $e->setTypeEspace('');

        (new EspacesManager())->validate($e);
    }
}