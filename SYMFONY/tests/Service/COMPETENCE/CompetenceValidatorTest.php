<?php
namespace App\Tests\Service\COMPETENCE;

use App\Service\COMPETENCE\CompetenceValidator;
use PHPUnit\Framework\TestCase;

class CompetenceValidatorTest extends TestCase
{
    private CompetenceValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new CompetenceValidator();
    }

    // ── Niveau clamping ──────────────────────────────────
    public function testNiveauIsClampedToMax(): void
    {
        $this->assertEquals(10, $this->validator->clampNiveau(15));
    }

    public function testNiveauIsClampedToMin(): void
    {
        $this->assertEquals(0, $this->validator->clampNiveau(-3));
    }

    public function testNiveauValidIsUnchanged(): void
    {
        $this->assertEquals(7, $this->validator->clampNiveau(7));
    }

    // ── Détection de gaps ────────────────────────────────
    public function testIsGapWhenBelowSixtyPercent(): void
    {
        // 2 < 60% de 5 (= 3) → gap
        $this->assertTrue($this->validator->isGap(2, 5));
    }

    public function testIsNotGapWhenAboveSixtyPercent(): void
    {
        // 4 >= 60% de 5 (= 3) → pas de gap
        $this->assertFalse($this->validator->isGap(4, 5));
    }

    public function testIsNotGapWhenNiveauZero(): void
    {
        $this->assertFalse($this->validator->isGap(0, 5));
    }

    // ── Score global ──────────────────────────────────────
    public function testComputeScoreWithFullSkills(): void
    {
        $skills = [
            ['niveauActuel' => 5, 'niveauMax' => 5],
            ['niveauActuel' => 5, 'niveauMax' => 10],
        ];
        // (100 + 50) / 2 = 75
        $this->assertEquals(75, $this->validator->computeScore($skills));
    }

    public function testComputeScoreWithNoSkills(): void
    {
        $this->assertEquals(0, $this->validator->computeScore([]));
    }

    // ── Validation libellé ─────────────────────────────────
    public function testValidLibelle(): void
    {
        $this->assertTrue($this->validator->validateLibelle('PHP'));
    }

    public function testInvalidLibelleTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->validator->validateLibelle('A');
    }

    public function testInvalidLibelleEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->validator->validateLibelle('');
    }

    // ── Validation niveauMax ──────────────────────────────
    public function testValidNiveauMax(): void
    {
        $this->assertEquals(5, $this->validator->validateNiveauMax(5));
    }

    public function testNiveauMaxTooLow(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->validator->validateNiveauMax(0);
    }

    public function testNiveauMaxTooHigh(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->validator->validateNiveauMax(11);
    }
}