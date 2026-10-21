<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($teks): string
{
    return htmlspecialchars((string)($teks ?? ''), ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $kirim = $_POST['csrf'] ?? '';
    return is_string($kirim)
        && isset($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $kirim);
}

function flash(string $tipe, string $pesan): void
{
    $_SESSION['flash'][] = [$tipe, $pesan];
}

function tampil_flash(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    foreach ($_SESSION['flash'] as [$tipe, $pesan]) {
        echo '<div class="pesan ' . e($tipe) . '">' . e($pesan) . '</div>';
    }
    unset($_SESSION['flash']);
}

function alihkan(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function bersihkan($nilai): string
{
    if (!is_string($nilai)) {
        return '';
    }
    return trim(str_replace("\r\n", "\n", $nilai));
}

function id_valid($nilai): ?int
{
    if (!is_string($nilai) || !ctype_digit($nilai) || strlen($nilai) > 10) {
        return null;
    }
    $id = (int)$nilai;
    return $id >= 1 ? $id : null;
}

function cek_panjang(string $teks, int $min, int $maks): bool
{
    $n = mb_strlen($teks, 'UTF-8');
    return $n >= $min && $n <= $maks;
}

function galat(array $g, string $kolom): string
{
    return isset($g[$kolom]) ? '<div class="galat">' . e($g[$kolom]) . '</div>' : '';
}

function query_semua(mysqli $conn, string $sql, string $tipe = '', array $param = []): array
{
    $stmt = $conn->prepare($sql);
    if ($tipe !== '') {
        $stmt->bind_param($tipe, ...$param);
    }
    $stmt->execute();
    $baris = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $baris;
}

function query_satu(mysqli $conn, string $sql, string $tipe = '', array $param = []): ?array
{
    $baris = query_semua($conn, $sql, $tipe, $param);
    return $baris[0] ?? null;
}

function query_ubah(mysqli $conn, string $sql, string $tipe, array $param): int
{
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($tipe, ...$param);
    $stmt->execute();
    $n = $stmt->affected_rows;
    $stmt->close();
    return $n;
}

function daftar_pilihan_mk(mysqli $conn): array
{
    return query_semua($conn, 'SELECT id, kode, nama FROM mata_kuliah ORDER BY nama');
}

function opsi_mk(array $pilihan, $terpilih, string $label_kosong): string
{
    $html = '<option value="">' . e($label_kosong) . '</option>';
    foreach ($pilihan as $p) {
        $sel = ((string)$p['id'] === (string)$terpilih) ? ' selected' : '';
        $html .= '<option value="' . e($p['id']) . '"' . $sel . '>' . e($p['kode'] . ' - ' . $p['nama']) . '</option>';
    }
    return $html;
}

function tgl_indo(string $ymd): string
{
    $d = DateTime::createFromFormat('Y-m-d', $ymd);
    return $d ? $d->format('d-m-Y') : $ymd;
}

function validasi_mata_kuliah(mysqli $conn, array $in, ?int $id_sendiri = null): array
{
    $v = [
        'kode'     => bersihkan($in['kode'] ?? null),
        'nama'     => bersihkan($in['nama'] ?? null),
        'dosen'    => bersihkan($in['dosen'] ?? null),
        'semester' => bersihkan($in['semester'] ?? null),
    ];
    $g = [];

    if (!preg_match('/^[A-Z0-9-]{3,20}\z/', $v['kode'])) {
        $g['kode'] = 'Kode 3-20 karakter: huruf kapital, angka, atau tanda -.';
    } else {

        if ($id_sendiri === null) {
            $ada = query_satu($conn, 'SELECT id FROM mata_kuliah WHERE kode = ?', 's', [$v['kode']]);
        } else {
            $ada = query_satu($conn, 'SELECT id FROM mata_kuliah WHERE kode = ? AND id <> ?', 'si', [$v['kode'], $id_sendiri]);
        }
        if ($ada) {
            $g['kode'] = 'Kode mata kuliah sudah digunakan.';
        }
    }
    if (!cek_panjang($v['nama'], 3, 100)) {
        $g['nama'] = 'Nama mata kuliah harus 3-100 karakter.';
    }
    if (!cek_panjang($v['dosen'], 3, 100)) {
        $g['dosen'] = 'Nama dosen harus 3-100 karakter.';
    }

    if (!ctype_digit($v['semester']) || (int)$v['semester'] < 1 || (int)$v['semester'] > 14) {
        $g['semester'] = 'Semester harus bilangan bulat 1-14.';
    }
    return [$v, $g];
}

function cek_mata_kuliah_ada(mysqli $conn, string $nilai, array &$g): void
{
    $id = id_valid($nilai);
    if ($id === null || !query_satu($conn, 'SELECT id FROM mata_kuliah WHERE id = ?', 'i', [$id])) {
        $g['mata_kuliah_id'] = 'Pilih mata kuliah yang tersedia.';
    }
}

function validasi_materi(mysqli $conn, array $in): array
{
    $v = [
        'mata_kuliah_id' => bersihkan($in['mata_kuliah_id'] ?? null),
        'judul'          => bersihkan($in['judul'] ?? null),
        'isi'            => bersihkan($in['isi'] ?? null),
        'tautan'         => bersihkan($in['tautan'] ?? null),
    ];
    $g = [];

    cek_mata_kuliah_ada($conn, $v['mata_kuliah_id'], $g);
    if (!cek_panjang($v['judul'], 3, 150)) {
        $g['judul'] = 'Judul materi harus 3-150 karakter.';
    }
    if (!cek_panjang($v['isi'], 3, 2000)) {
        $g['isi'] = 'Isi materi harus 3-2000 karakter.';
    }

    if ($v['tautan'] !== '') {
        $bagian = parse_url($v['tautan']);
        $ok = strlen($v['tautan']) <= 255
            && preg_match('#^https?://#i', $v['tautan'])
            && filter_var($v['tautan'], FILTER_VALIDATE_URL) !== false
            && !empty($bagian['host']);
        if (!$ok) {
            $g['tautan'] = 'Gunakan tautan http/https yang valid (maksimal 255 karakter).';
        }
    }
    return [$v, $g];
}

const STATUS_TUGAS = ['Draf', 'Aktif', 'Selesai'];

function validasi_tugas(mysqli $conn, array $in): array
{
    $v = [
        'mata_kuliah_id' => bersihkan($in['mata_kuliah_id'] ?? null),
        'judul'          => bersihkan($in['judul'] ?? null),
        'instruksi'      => bersihkan($in['instruksi'] ?? null),
        'tenggat'        => bersihkan($in['tenggat'] ?? null),
        'status'         => bersihkan($in['status'] ?? null),
    ];
    $g = [];

    cek_mata_kuliah_ada($conn, $v['mata_kuliah_id'], $g);
    if (!cek_panjang($v['judul'], 3, 150)) {
        $g['judul'] = 'Judul tugas harus 3-150 karakter.';
    }
    if (!cek_panjang($v['instruksi'], 3, 2000)) {
        $g['instruksi'] = 'Instruksi tugas harus 3-2000 karakter.';
    }

    $d = DateTime::createFromFormat('!Y-m-d', $v['tenggat']);
    $tahun = $d ? (int)$d->format('Y') : 0;
    if (!$d || $d->format('Y-m-d') !== $v['tenggat'] || $tahun < 1000 || $tahun > 9999) {
        $g['tenggat'] = 'Tanggal tenggat tidak valid.';
    }

    if (!in_array($v['status'], STATUS_TUGAS, true)) {
        $g['status'] = 'Status hanya boleh Draf, Aktif, atau Selesai.';
    }
    return [$v, $g];
}

function header_halaman(string $judul, string $menu = ''): void
{
    $item = [
        'beranda'     => ['index.php', 'Beranda'],
        'mata_kuliah' => ['daftar_mata_kuliah.php', 'Mata kuliah'],
        'materi'      => ['daftar_materi.php', 'Materi'],
        'tugas'       => ['daftar_tugas.php', 'Tugas'],
    ];
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($judul) . ' - LMS</title>';
    echo '<link rel="stylesheet" href="style.css"></head><body><div class="halaman">';
    echo '<nav class="menu">';
    foreach ($item as $kunci => [$url, $label]) {
        $kelas = $kunci === $menu ? ' class="aktif"' : '';
        echo '<a' . $kelas . ' href="' . e($url) . '">' . e($label) . '</a>';
    }
    echo '</nav>';
    tampil_flash();
}

function footer_halaman(): void
{
    echo '</div></body></html>';
}
