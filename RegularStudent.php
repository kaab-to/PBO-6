<?php
declare(strict_types=1);

abstract class Student
{
    public function __construct(
        private string $studentId,
        private string $name,
        private string $major,
        private int $semester,
        private string $faculty,
        private string $previousSchool,
        private string $address,
        private string $bloodType,
        private string $hobby,
        private string $religion
    ) {}

    public function getStudentId(): string { return $this->studentId; }
    public function getName(): string { return $this->name; }
    public function getMajor(): string { return $this->major; }
    public function getSemester(): int { return $this->semester; }
    public function getFaculty(): string { return $this->faculty; }
    public function getPreviousSchool(): string { return $this->previousSchool; }
    public function getAddress(): string { return $this->address; }
    public function getBloodType(): string { return $this->bloodType; }
    public function getHobby(): string { return $this->hobby; }
    public function getReligion(): string { return $this->religion; }

    abstract public function getType(): string;
    abstract public function getBaseTuition(): float;
    abstract public function getDiscountPercent(): float;
    abstract public function calculateTuition(): float;
}

final class RegularStudent extends Student
{
    public function __construct(
        string $studentId, string $name, string $major, int $semester,
        string $faculty, string $previousSchool, string $address, string $bloodType,
        string $hobby, string $religion,
        private float $tuition
    ) {
        self::validateTuition($tuition);
        parent::__construct(
            $studentId, $name, $major, $semester, $faculty,
            $previousSchool, $address, $bloodType, $hobby, $religion
        );
    }

    private static function validateTuition(float $amount): void
    {
        if (!is_finite($amount) || $amount < 0) {
            throw new InvalidArgumentException('UKT harus berupa angka non-negatif.');
        }
    }

    public function updateTuition(float $tuition): void
    {
        self::validateTuition($tuition);
        $this->tuition = $tuition;
    }

    public function getType(): string { return 'Reguler'; }
    public function getBaseTuition(): float { return $this->tuition; }
    public function getDiscountPercent(): float { return 0.0; }
    public function calculateTuition(): float { return $this->tuition; }
}

// Contoh penggunaan
$student = new RegularStudent(
    '2024001', 'Mas', 'Matematika', 3,
    'FMIPA', 'SMAN 1 Bandung', 'Bandung', 'O',
    'Membaca', 'Islam',
    3500000.0
);

echo $student->getName() . ' (' . $student->getType() . ')' . PHP_EOL;
echo 'Hobi   : ' . $student->getHobby() . PHP_EOL;
echo 'Agama  : ' . $student->getReligion() . PHP_EOL;
echo 'UKT    : Rp' . number_format($student->calculateTuition(), 0, ',', '.') . PHP_EOL;