<?php
declare(strict_types=1);

/* ============================================================
 * BAGIAN 1 — CLASS (parent, child, repository)
 * Didefinisikan SEBELUM session_start() agar objek di session
 * bisa di-unserialize dengan benar.
 * ============================================================ */

abstract class Student
{
    public const BLOOD_TYPES = ['A', 'B', 'AB', 'O'];
    public const RELIGIONS = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

    protected string $studentId;
    protected string $name;
    protected string $major;
    protected int $semester;
    protected string $faculty;
    protected string $previousSchool;
    protected string $address;
    protected string $bloodType;
    protected string $religion;
    protected string $hobby;
    protected float $baseTuition;

    public function __construct(
        string $studentId, string $name, string $major, int $semester,
        string $faculty, string $previousSchool, string $address,
        string $bloodType, string $religion, string $hobby, float $baseTuition
    ) {
        $this->studentId = trim($studentId);
        $this->name = trim($name);
        $this->major = trim($major);
        $this->semester = $semester;
        $this->faculty = trim($faculty);
        $this->previousSchool = trim($previousSchool);
        $this->address = trim($address);
        $this->bloodType = strtoupper(trim($bloodType));
        $this->religion = trim($religion);
        $this->hobby = trim($hobby);
        $this->baseTuition = $baseTuition;
        $this->validate();
    }

    private function validate(): void
    {
        $required = [
            'NIM' => $this->studentId, 'Nama' => $this->name, 'Jurusan' => $this->major,
            'Fakultas' => $this->faculty, 'Asal sekolah' => $this->previousSchool,
            'Alamat' => $this->address, 'Hobi' => $this->hobby,
        ];
        foreach ($required as $label => $value) {
            if ($value === '') throw new InvalidArgumentException("$label wajib diisi.");
        }
        if ($this->semester < 1 || $this->semester > 14) {
            throw new InvalidArgumentException('Semester harus antara 1 sampai 14.');
        }
        if ($this->baseTuition < 0) {
            throw new InvalidArgumentException('UKT tidak boleh negatif.');
        }
        if (!in_array($this->bloodType, self::BLOOD_TYPES, true)) {
            throw new InvalidArgumentException('Golongan darah tidak valid.');
        }
        if (!in_array($this->religion, self::RELIGIONS, true)) {
            throw new InvalidArgumentException('Agama tidak valid.');
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
    public function getReligion(): string { return $this->religion; }
    public function getHobby(): string { return $this->hobby; }
    public function getBaseTuition(): float { return $this->baseTuition; }

    public function getDiscountPercent(): float { return 0.0; }

    abstract public function getType(): string;
    abstract public function calculateTuition(): float; // polymorphism
}

class RegularStudent extends Student
{
    public function getType(): string { return 'Reguler'; }
    public function calculateTuition(): float { return $this->baseTuition; }
}

class ScholarshipStudent extends Student
{
    private float $discountPercent;

    public function __construct(
        string $studentId, string $name, string $major, int $semester,
        string $faculty, string $previousSchool, string $address,
        string $bloodType, string $religion, string $hobby,
        float $baseTuition, float $discountPercent
    ) {
        if ($discountPercent < 0 || $discountPercent > 100) {
            throw new InvalidArgumentException('Diskon harus antara 0 sampai 100.');
        }
        parent::__construct($studentId, $name, $major, $semester, $faculty,
            $previousSchool, $address, $bloodType, $religion, $hobby, $baseTuition);
        $this->discountPercent = $discountPercent;
    }

    public function getType(): string { return 'Beasiswa'; }
    public function getDiscountPercent(): float { return $this->discountPercent; }
    public function calculateTuition(): float
    {
        return $this->baseTuition * (1 - $this->discountPercent / 100); // override
    }
}

class StudentRepository
{
    /** @var array<string, Student> */
    private array $students = [];

    public function add(Student $student): bool
    {
        if (isset($this->students[$student->getStudentId()])) return false;
        $this->students[$student->getStudentId()] = $student;
        return true;
    }

    public function update(string $id, Student $student): bool
    {
        if (!isset($this->students[$id])) return false;
        $this->students[$id] = $student;
        return true;
    }

    public function delete(string $id): bool
    {
        if (!isset($this->students[$id])) return false;
        unset($this->students[$id]);
        return true;
    }

    public function find(string $id): ?Student { return $this->students[$id] ?? null; }

    /** @return Student[] */
    public function all(): array { return array_values($this->students); }
}

/* ============================================================
 * BAGIAN 2 — LOGIKA HALAMAN (controller)
 * ============================================================ */

session_start();

if (!isset($_SESSION['repository']) || !($_SESSION['repository'] instanceof StudentRepository)) {
    $_SESSION['repository'] = new StudentRepository();
}
/** @var StudentRepository $repository */
$repository = $_SESSION['repository'];
$error = '';
$success = '';
$selected = null;

function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function money(float $value): string { return 'Rp ' . number_format($value, 0, ',', '.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'delete') {
            $id = trim((string) ($_POST['student_id'] ?? ''));
            if (!$repository->delete($id)) throw new InvalidArgumentException('Mahasiswa tidak ditemukan.');
            $success = 'Mahasiswa berhasil dihapus.';
        } elseif ($action === 'save') {
            $id = trim((string) ($_POST['student_id'] ?? ''));
            $name = (string) ($_POST['name'] ?? '');
            $major = (string) ($_POST['major'] ?? '');
            $faculty = (string) ($_POST['faculty'] ?? '');
            $previousSchool = (string) ($_POST['previous_school'] ?? '');
            $address = (string) ($_POST['address'] ?? '');
            $bloodType = (string) ($_POST['blood_type'] ?? '');
            $religion = (string) ($_POST['religion'] ?? '');
            $hobby = (string) ($_POST['hobby'] ?? '');
            $semester = filter_var($_POST['semester'] ?? null, FILTER_VALIDATE_INT);
            $tuition = filter_var($_POST['tuition'] ?? null, FILTER_VALIDATE_FLOAT);
            $discount = filter_var($_POST['discount'] ?? '0', FILTER_VALIDATE_FLOAT);
            if ($semester === false || $tuition === false || $discount === false) {
                throw new InvalidArgumentException('Semester, UKT, dan diskon harus berupa angka yang valid.');
            }
            $type = (string) ($_POST['type'] ?? '');
            if ($type === 'regular') {
                $student = new RegularStudent($id, $name, $major, $semester, $faculty, $previousSchool, $address, $bloodType, $religion, $hobby, $tuition);
            } elseif ($type === 'scholarship') {
                $student = new ScholarshipStudent($id, $name, $major, $semester, $faculty, $previousSchool, $address, $bloodType, $religion, $hobby, $tuition, $discount);
            } else {
                throw new InvalidArgumentException('Pilih tipe mahasiswa yang valid.');
            }
            $mode = (string) ($_POST['mode'] ?? 'add');
            if ($mode === 'edit') {
                if (!$repository->update($id, $student)) throw new InvalidArgumentException('Mahasiswa tidak ditemukan.');
                $success = 'Data mahasiswa berhasil diperbarui.';
            } elseif ($mode === 'add') {
                if (!$repository->add($student)) throw new InvalidArgumentException('NIM sudah terdaftar.');
                $success = 'Mahasiswa berhasil ditambahkan.';
            } else {
                throw new InvalidArgumentException('Mode form tidak valid.');
            }
        } else {
            throw new InvalidArgumentException('Aksi tidak dikenal.');
        }
        $_SESSION['repository'] = $repository;
        $_SESSION['flash'] = $success;
        header('Location: index.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    }
}

if (isset($_SESSION['flash'])) {
    $success = (string) $_SESSION['flash'];
    unset($_SESSION['flash']);
}
if (isset($_GET['edit'])) $selected = $repository->find((string) $_GET['edit']);
$search = trim((string) ($_GET['q'] ?? ''));
$rows = array_filter($repository->all(), static function (Student $s) use ($search): bool {
    return $search === '' || stripos(
        $s->getStudentId() . ' ' . $s->getName() . ' ' . $s->getMajor() . ' ' . $s->getFaculty()
        . ' ' . $s->getReligion() . ' ' . $s->getHobby(), $search
    ) !== false;
});
$bloodTypes = Student::BLOOD_TYPES;
$religions = Student::RELIGIONS;
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>CRUD Mahasiswa — Inheritance</title>
<style>
:root{font-family:system-ui,Arial,sans-serif;color:#172c4c;background:#f3f7fc}body{max-width:1120px;margin:35px auto;padding:0 16px}h1{margin-bottom:4px}p{color:#5b6980}.panel{background:#fff;border-radius:14px;padding:22px;margin:20px 0;box-shadow:0 6px 24px #102b5010}.fields{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px}label{display:block;font-size:13px;font-weight:650;margin-bottom:5px}input,select,textarea{box-sizing:border-box;width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:7px;font-family:inherit}textarea{resize:vertical;min-height:70px}button,.btn{background:#1764b5;color:white;padding:10px 15px;border:0;border-radius:7px;cursor:pointer;text-decoration:none;display:inline-block}.muted{background:#e8eff7;color:#183a61}.danger{background:#c33849}.actions{display:flex;gap:7px;align-items:center}.alert{padding:12px;border-radius:8px;background:#e4f8e9}.error{background:#ffe7e7}table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:10px;border-bottom:1px solid #e4eaf2}th{background:#e9f2fd}small{color:#65758b}.scroll{overflow-x:auto}.wide{grid-column:1/-1}
</style></head><body>
<h1>🎓 CRUD Mahasiswa — Inheritance</h1><p>Sesi OOP 06 · Parent class, child class, overriding, dan polymorphism</p>
<?php if ($success !== ''): ?><div class="alert"><?=h($success)?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert error"><?=h($error)?></div><?php endif; ?>
<section class="panel"><h2><?= $selected ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' ?></h2>
<form method="post"><input type="hidden" name="action" value="save"><input type="hidden" name="mode" value="<?= $selected ? 'edit' : 'add' ?>">
<div class="fields">
<div><label>NIM (tidak dapat diubah)</label><input name="student_id" required value="<?=h($selected?->getStudentId() ?? '')?>" <?= $selected ? 'readonly' : '' ?>></div>
<div><label>Nama</label><input name="name" required value="<?=h($selected?->getName() ?? '')?>"></div>
<div><label>Fakultas</label><input name="faculty" required value="<?=h($selected?->getFaculty() ?? '')?>"></div>
<div><label>Jurusan</label><input name="major" required value="<?=h($selected?->getMajor() ?? '')?>"></div>
<div><label>Semester (1–14)</label><input name="semester" type="number" min="1" max="14" required value="<?=h((string)($selected?->getSemester() ?? 1))?>"></div>
<div><label>Asal Sekolah</label><input name="previous_school" required value="<?=h($selected?->getPreviousSchool() ?? '')?>"></div>
<div><label>Golongan Darah</label>
<select name="blood_type" required>
<?php foreach ($bloodTypes as $bt): ?><option value="<?=$bt?>" <?= ($selected?->getBloodType() ?? 'A') === $bt ? 'selected' : '' ?>><?=$bt?></option><?php endforeach; ?>
</select></div>
<div><label>Agama</label>
<select name="religion" required>
<?php foreach ($religions as $r): ?><option value="<?=h($r)?>" <?= ($selected?->getReligion() ?? 'Islam') === $r ? 'selected' : '' ?>><?=h($r)?></option><?php endforeach; ?>
</select></div>
<div><label>Hobi</label><input name="hobby" required value="<?=h($selected?->getHobby() ?? '')?>"></div>
<div><label>Tipe mahasiswa</label><select name="type" id="type"><option value="regular" <?=!($selected instanceof ScholarshipStudent)?'selected':''?>>Mahasiswa Reguler</option><option value="scholarship" <?=$selected instanceof ScholarshipStudent?'selected':''?>>Mahasiswa Beasiswa</option></select></div>
<div><label>UKT dasar (IDR)</label><input name="tuition" type="number" min="0" step="0.01" required value="<?=h((string)($selected?->getBaseTuition() ?? 4000000))?>"></div>
<div id="discount-field"><label>Diskon beasiswa (%)</label><input name="discount" type="number" min="0" max="100" step="0.01" value="<?=h((string)($selected?->getDiscountPercent() ?: 25))?>"></div>
<div class="wide"><label>Alamat</label><textarea name="address" required><?=h($selected?->getAddress() ?? '')?></textarea></div>
</div><p><button type="submit"><?= $selected ? 'Simpan Perubahan' : 'Tambah Mahasiswa' ?></button> <a class="btn muted" href="index.php">Reset</a></p></form></section>
<section class="panel"><h2>Daftar Mahasiswa (<?=count($rows)?>)</h2>
<form method="get" class="actions" style="margin-bottom:16px"><input name="q" placeholder="Cari NIM, nama, jurusan, fakultas, agama, atau hobi" value="<?=h($search)?>"><button>Cari</button><a class="btn muted" href="index.php">Semua</a></form>
<div class="scroll"><table><thead><tr><th>NIM</th><th>Nama</th><th>Fakultas</th><th>Jurusan</th><th>Semester</th><th>Asal Sekolah</th><th>Alamat</th><th>Gol. Darah</th><th>Agama</th><th>Hobi</th><th>Tipe</th><th>UKT Dasar</th><th>Diskon</th><th>UKT Akhir</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($rows as $student): ?>
<tr><td><?=h($student->getStudentId())?></td><td><?=h($student->getName())?></td><td><?=h($student->getFaculty())?></td><td><?=h($student->getMajor())?></td><td><?=$student->getSemester()?></td><td><?=h($student->getPreviousSchool())?></td><td><?=h($student->getAddress())?></td><td><?=h($student->getBloodType())?></td><td><?=h($student->getReligion())?></td><td><?=h($student->getHobby())?></td><td><?=h($student->getType())?></td><td><?=money($student->getBaseTuition())?></td><td><?=number_format($student->getDiscountPercent(), 1)?>%</td><td><strong><?=money($student->calculateTuition())?></strong></td><td><div class="actions"><a class="btn muted" href="?edit=<?=urlencode($student->getStudentId())?>">Edit</a><form method="post" onsubmit="return confirm('Hapus mahasiswa ini?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="student_id" value="<?=h($student->getStudentId())?>"><button class="danger">Hapus</button></form></div></td></tr>
<?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="15">Mahasiswa tidak ditemukan.</td></tr><?php endif; ?>
</tbody></table></div><p><small>Data disimpan di PHP Session untuk latihan di kelas, bukan di database.</small></p></section>
<script>const type=document.getElementById('type'),field=document.getElementById('discount-field');function sync(){field.style.display=type.value==='scholarship'?'block':'none'}type.addEventListener('change',sync);sync();</script>
</body></html>