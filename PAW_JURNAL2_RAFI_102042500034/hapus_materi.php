<?php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        flash('error', 'Token tidak valid. Data tidak dihapus.');
        alihkan('daftar_materi.php');
    }
    $id = id_valid($_POST['id'] ?? null);
    if ($id === null) {
        flash('error', 'ID materi tidak valid. Data tidak dihapus.');
        alihkan('daftar_materi.php');
    }
    if (!query_satu($conn, 'SELECT id FROM materi WHERE id = ?', 'i', [$id])) {
        flash('error', 'Materi tidak ditemukan. Data tidak dihapus.');
        alihkan('daftar_materi.php');
    }
    query_ubah($conn, 'DELETE FROM materi WHERE id = ?', 'i', [$id]);
    flash('sukses', 'Materi berhasil dihapus.');
    alihkan('daftar_materi.php');
}

$id = id_valid($_GET['id'] ?? null);
if ($id === null) {
    flash('error', 'ID materi tidak valid.');
    alihkan('daftar_materi.php');
}
$data = query_satu($conn, 'SELECT id, judul FROM materi WHERE id = ?', 'i', [$id]);
if (!$data) {
    flash('error', 'Materi tidak ditemukan.');
    alihkan('daftar_materi.php');
}

header_halaman('Hapus materi', 'materi');
?>
<h1>Materi</h1>
<p>Hapus <strong><?= e($data['judul']) ?></strong>?</p>
<p>Data baru dihapus setelah tombol konfirmasi dikirim.</p>
<form method="post" action="hapus_materi.php" class="aksi-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">
    <button type="submit" style="background:#8f2d36;border-color:#8f2d36">Ya, hapus data</button>
    <a class="tombol batal" href="daftar_materi.php">Batal</a>
</form>
<?php footer_halaman();
