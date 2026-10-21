<?php
require 'koneksi.php';

$id = id_valid($_GET['id'] ?? null);
if ($id === null) {
    flash('error', 'ID mata kuliah tidak valid.');
    alihkan('daftar_mata_kuliah.php');
}
$mk = query_satu($conn, 'SELECT id, kode, nama, dosen, semester FROM mata_kuliah WHERE id = ?', 'i', [$id]);
if (!$mk) {
    flash('error', 'Mata kuliah tidak ditemukan.');
    alihkan('daftar_mata_kuliah.php');
}
$materi = query_semua($conn, 'SELECT id, judul FROM materi WHERE mata_kuliah_id = ? ORDER BY judul', 'i', [$id]);
$tugas  = query_semua($conn, 'SELECT id, judul, tenggat, status FROM tugas WHERE mata_kuliah_id = ? ORDER BY tenggat', 'i', [$id]);

header_halaman('Detail mata kuliah', 'mata_kuliah');
?>
<h1>Mata kuliah</h1>
<h2><?= e($mk['nama']) ?></h2>
<dl class="rincian">
    <dt>Kode</dt><dd><?= e($mk['kode']) ?></dd>
    <dt>Nama</dt><dd><?= e($mk['nama']) ?></dd>
    <dt>Dosen</dt><dd><?= e($mk['dosen']) ?></dd>
    <dt>Semester</dt><dd><?= (int)$mk['semester'] ?></dd>
</dl>
<p>
    <a class="tombol" href="edit_mata_kuliah.php?id=<?= (int)$mk['id'] ?>">Edit data</a>
    <a class="tombol batal" href="daftar_mata_kuliah.php">Kembali</a>
</p>

<h2>Materi (<?= count($materi) ?>)</h2>
<?php if (!$materi): ?>
    <div class="kosong">Belum ada materi untuk mata kuliah ini.</div>
<?php else: ?>
    <ul>
        <?php foreach ($materi as $m): ?>
            <li><a href="detail_materi.php?id=<?= (int)$m['id'] ?>"><?= e($m['judul']) ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h2>Tugas (<?= count($tugas) ?>)</h2>
<?php if (!$tugas): ?>
    <div class="kosong">Belum ada tugas untuk mata kuliah ini.</div>
<?php else: ?>
    <ul>
        <?php foreach ($tugas as $t): ?>
            <li><a href="detail_tugas.php?id=<?= (int)$t['id'] ?>"><?= e($t['judul']) ?></a>
                - tenggat <?= e(tgl_indo($t['tenggat'])) ?> (<?= e($t['status']) ?>)</li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php footer_halaman();
