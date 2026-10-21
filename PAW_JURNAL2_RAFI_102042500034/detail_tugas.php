<?php
require 'koneksi.php';

$id = id_valid($_GET['id'] ?? null);
if ($id === null) {
    flash('error', 'ID tugas tidak valid.');
    alihkan('daftar_tugas.php');
}
$t = query_satu(
    $conn,
    'SELECT t.id, t.judul, t.instruksi, t.tenggat, t.status, mk.kode, mk.nama AS nama_mk
     FROM tugas t JOIN mata_kuliah mk ON mk.id = t.mata_kuliah_id
     WHERE t.id = ?',
    'i',
    [$id]
);
if (!$t) {
    flash('error', 'Tugas tidak ditemukan.');
    alihkan('daftar_tugas.php');
}

header_halaman('Detail tugas', 'tugas');
?>
<h1>Tugas</h1>
<h2><?= e($t['judul']) ?></h2>
<dl class="rincian">
    <dt>Mata kuliah</dt><dd><?= e($t['kode']) ?> - <?= e($t['nama_mk']) ?></dd>
    <dt>Judul tugas</dt><dd><?= e($t['judul']) ?></dd>
    <dt>Instruksi</dt><dd><?= e($t['instruksi']) ?></dd>
    <dt>Tenggat</dt><dd><?= e(tgl_indo($t['tenggat'])) ?></dd>
    <dt>Status</dt><dd><?= e($t['status']) ?></dd>
</dl>
<p>
    <a class="tombol" href="edit_tugas.php?id=<?= (int)$t['id'] ?>">Edit data</a>
    <a class="tombol batal" href="daftar_tugas.php">Kembali</a>
</p>
<?php footer_halaman();
