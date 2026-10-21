<?php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash('error', 'Token tidak valid. Data tidak dihapus.');
        alihkan('daftar_tugas.php');
    }
    $id = id_valid($_POST['id'] ?? null);
    if ($id === null) {
        flash('error', 'ID tugas tidak valid. Data tidak dihapus.');
        alihkan('daftar_tugas.php');
    }
    if (!query_satu($conn, 'SELECT id FROM tugas WHERE id = ?', 'i', [$id])) {
        flash('error', 'Tugas tidak ditemukan. Data tidak dihapus.');
        alihkan('daftar_tugas.php');
    }
    query_ubah($conn, 'DELETE FROM tugas WHERE id = ?', 'i', [$id]);
    flash('sukses', 'Tugas berhasil dihapus.');
    alihkan('daftar_tugas.php');
}

$id = id_valid($_GET['id'] ?? null);
if ($id === null) {
    flash('error', 'ID tugas tidak valid.');
    alihkan('daftar_tugas.php');
}
$data = query_satu($conn, 'SELECT id, judul FROM tugas WHERE id = ?', 'i', [$id]);
if (!$data) {
    flash('error', 'Tugas tidak ditemukan.');
    alihkan('daftar_tugas.php');
}

header_halaman('Hapus tugas', 'tugas');
?>
<h1>Tugas</h1>
<p>Hapus <strong><?= e($data['judul']) ?></strong>?</p>
<p>Data baru dihapus setelah tombol konfirmasi dikirim.</p>
<form method="post" action="hapus_tugas.php" class="aksi-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">
    <button type="submit" style="background:#8f2d36;border-color:#8f2d36">Ya, hapus data</button>
    <a class="tombol batal" href="daftar_tugas.php">Batal</a>
</form>
<?php footer_halaman();
