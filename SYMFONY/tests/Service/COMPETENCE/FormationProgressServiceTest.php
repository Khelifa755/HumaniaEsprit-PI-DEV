<?php
namespace App\Tests\Service\COMPETENCE;

use App\Service\COMPETENCE\FormationProgressService;
use PHPUnit\Framework\TestCase;

class FormationProgressServiceTest extends TestCase
{
    private FormationProgressService $service;

    protected function setUp(): void
    {
        $this->service = new FormationProgressService();
    }

    // ── Calcul de progression ────────────────────────────
    public function testProgressionIsHundredWhenAllDone(): void
    {
        $this->assertEquals(100, $this->service->computeProgression(5, 5));
    }

    public function testProgressionIsZeroWhenNoDone(): void
    {
        $this->assertEquals(0, $this->service->computeProgression(0, 5));
    }

    public function testProgressionIsZeroWhenNoModules(): void
    {
        $this->assertEquals(0, $this->service->computeProgression(0, 0));
    }

    public function testProgressionIsFiftyPercent(): void
    {
        $this->assertEquals(50, $this->service->computeProgression(2, 4));
    }

    public function testProgressionIsRounded(): void
    {
        // 1/3 * 100 = 33.33 → 33
        $this->assertEquals(33, $this->service->computeProgression(1, 3));
    }

    // ── Statut formation ─────────────────────────────────
    public function testStatutIsCompletedAt100(): void
    {
        $this->assertEquals('Completed', $this->service->computeStatut(100));
    }

    public function testStatutIsInProgressBelow100(): void
    {
        $this->assertEquals('In Progress', $this->service->computeStatut(99));
    }

    public function testStatutIsInProgressAtZero(): void
    {
        $this->assertEquals('In Progress', $this->service->computeStatut(0));
    }

    // ── Certificat ───────────────────────────────────────
    public function testCertificateEarnedAt70(): void
    {
        $this->assertTrue($this->service->isCertificateEarned(70.0));
    }

    public function testCertificateEarnedAbove70(): void
    {
        $this->assertTrue($this->service->isCertificateEarned(95.5));
    }

    public function testCertificateNotEarnedBelow70(): void
    {
        $this->assertFalse($this->service->isCertificateEarned(69.9));
    }

    public function testCertificateNotEarnedWithNull(): void
    {
        $this->assertFalse($this->service->isCertificateEarned(null));
    }
}