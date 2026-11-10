<?php
declare(strict_types=1);

abstract class Student
{
    private const BLOOD_TYPES = ['A', 'B', 'AB', 'O'];

    public function __construct(
        private string $studentId,
        private string $name,
        private string $major,
        private int $semester,
        private string $faculty,
        private string $previousSchool,
        private string $address,
        private string $bloodType
    ) {
        self::validateProfile($studentId, $name, $major, $semester, $faculty, $previousSchool, $address, $bloodType);
        $this->studentId = trim($studentId);
        $this->name = trim($name);
        $this->major = trim($major);
        $this->faculty = trim($faculty);
        $this->previousSchool = trim($previousSchool);
        $this->address = trim($address);
        $this->bloodType = strtoupper(trim($bloodType));
    }

    private static function validateProfile(
        string $id, string $name, string $major, int $semester,
        string $faculty, string $previousSchool, string $address, string $bloodType
    ): void {
        if (trim($id) === '' || trim($name) === '' || trim($major) === ''
            || trim($faculty) === '' || trim($previousSchool) === '' || trim($address) === '') {
            throw new InvalidArgumentException('NIM, nama, jurusan, fakultas, asal sekolah, dan alamat wajib diisi.');
        }
        if ($semester < 1 || $semester > 14) {
            throw new InvalidArgumentException('Semester harus antara 1 sampai 14.');
        }
        if (!in_array(strtoupper(trim($bloodType)), self::BLOOD_TYPES, true)) {
            throw new InvalidArgumentException('Golongan darah harus A, B, AB, atau O.');
        }
    }

    public function getStudentId(): string { return $this->studentId; }
    public function getName(): string { return $this->name; }
    public function getMajor(): string { return $this->major; }
    public function getSemester(): int { return $this->semester; }
    public function getFaculty(): string { return $this->faculty; }
    public function getPreviousSchool(): string { return $this->previousSchool; }
    public function getAddress(): string { return $this->address; }
    public function getBloodType(): string { return $this->bloodType; }

    public function updateProfile(
        string $name, string $major, int $semester,
        string $faculty, string $previousSchool, string $address, string $bloodType
    ): void {
        self::validateProfile($this->studentId, $name, $major, $semester, $faculty, $previousSchool, $address, $bloodType);
        $this->name = trim($name);
        $this->major = trim($major);
        $this->semester = $semester;
        $this->faculty = trim($faculty);
        $this->previousSchool = trim($previousSchool);
        $this->address = trim($address);
        $this->bloodType = strtoupper(trim($bloodType));
    }

    abstract public function getType(): string;
    abstract public function calculateTuition(): float;
    abstract public function getBaseTuition(): float;
    abstract public function getDiscountPercent(): float;
}