<?php
require 'koneksi.php';

$galat_hapus = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_valid()) {
        flash('error', 'Token tidak valid. Data tidak dihapus.');
        alihkan('daftar_mata_kuliah.php');
    }

    $id = id_valid($_POST['id'] ?? null);
    if ($id === null) {
        flash('error', 'ID mata kuliah tidak valid. Data tidak dihapus.');
        alihkan('daftar_mata_kuliah.php');
    }
    $mk = query_satu($conn, 'SELECT id, nama FROM mata_kuliah WHERE id = ?', 'i', [$id]);
    if (!$mk) {
        flash('error', 'Mata kuliah tidak ditemukan. Data tidak dihapus.');
        alihkan('daftar_mata_kuliah.php');
    }

    $pakai = query_satu(
        $conn,
        'SELECT (SELECT COUNT(*) FROM materi WHERE mata_kuliah_id = ?) AS jml_materi,
                (SELECT COUNT(*) FROM tugas WHERE mata_kuliah_id = ?) AS jml_tugas',
        'ii',
        [$id, $id]
    );
    if ((int)$pakai['jml_materi'] + (int)$pakai['jml_tugas'] > 0) {
        $galat_hapus = 'Data belum tersimpan. Mata kuliah masih memiliki materi atau tugas. Hapus data terkait terlebih dahulu.';
    } else {
        try {
            query_ubah($conn, 'DELETE FROM mata_kuliah WHERE id = ?', 'i', [$id]);
            flash('sukses', 'Mata kuliah berhasil dihapus.');
            alihkan('daftar_mata_kuliah.php');
        } catch (mysqli_sql_exception $ex) {

            if ((int)$ex->getCode() === 1451) {
                $galat_hapus = 'Data belum tersimpan. Mata kuliah masih memiliki materi atau tugas. Hapus data terkait terlebih dahulu.';
            } else {
                throw $ex;
            }
        }
    }
} else {
    $id = id_valid($_GET['id'] ?? null);
    if ($id === null) {
        flash('error', 'ID mata kuliah tidak valid.');
        alihkan('daftar_mata_kuliah.php');
    }
    $mk = query_satu($conn, 'SELECT id, nama FROM mata_kuliah WHERE id = ?', 'i', [$id]);
    if (!$mk) {
        flash('error', 'Mata kuliah tidak ditemukan.');
        alihkan('daftar_mata_kuliah.php');
    }
}

header_halaman('Hapus mata kuliah', 'mata_kuliah');
?>
<h1>Mata kuliah</h1>
<?php if ($galat_hapus !== ''): ?>
    <div class="pesan error"><?= e($galat_hapus) ?></div>
<?php endif; ?>
<p>Hapus <strong><?= e($mk['nama']) ?></strong>?</p>
<p>Data baru dihapus setelah tombol konfirmasi dikirim.</p>
<form method="post" action="hapus_mata_kuliah.php" class="aksi-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$mk['id'] ?>">
    <button type="submit" class="hapus" style="background:#8f2d36;border-color:#8f2d36">Ya, hapus data</button>
    <a class="tombol batal" href="daftar_mata_kuliah.php">Batal</a>
</form>
<?php footer_halaman();
