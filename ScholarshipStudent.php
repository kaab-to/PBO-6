<?php
declare(strict_types=1);
require_once __DIR__ . '/Student.php';

final class ScholarshipStudent extends Student
{
    public function __construct(
        string $studentId, string $name, string $major, int $semester,
        string $faculty, string $previousSchool, string $address, string $bloodType,
        private float $tuition, private float $discountPercent
    ) {
        self::validateFees($tuition, $discountPercent);
        parent::__construct($studentId, $name, $major, $semester, $faculty, $previousSchool, $address, $bloodType);
    }

    private static function validateFees(float $tuition, float $discount): void
    {
        if (!is_finite($tuition) || $tuition < 0 || !is_finite($discount) || $discount < 0 || $discount > 100) {
            throw new InvalidArgumentException('UKT harus non-negatif dan diskon harus 0–100%.');
        }
    }

    public function updateFees(float $tuition, float $discount): void
    {
        self::validateFees($tuition, $discount);
        $this->tuition = $tuition;
        $this->discountPercent = $discount;
    }

    public function getType(): string { return 'Beasiswa'; }
    public function getBaseTuition(): float { return $this->tuition; }
    public function getDiscountPercent(): float { return $this->discountPercent; }

    // Method overriding: perhitungan UKT berbeda dari RegularStudent.
    public function calculateTuition(): float
    {
        return $this->tuition * (1 - $this->discountPercent / 100);
    }
}