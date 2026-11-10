abstract class Student
{
    private const BLOOD_TYPES = ['A', 'B', 'AB', 'O'];

    public function __construct(
        private string $studentId, private string $name,
        private string $major, private int $semester,
        private string $faculty, private string $previousSchool,
        private string $address, private string $bloodType
    ) {
        self::validateProfile(...);   // validasi dulu
        $this->bloodType = strtoupper(trim($bloodType));
    }

    // Kontrak yang wajib diimplementasi child class
    abstract public function getType(): string;
    abstract public function calculateTuition(): float;
    abstract public function getBaseTuition(): float;
    abstract public function getDiscountPercent(): float;
}