<?php
require 'koneksi.php';

$jml_mk     = (int)query_satu($conn, 'SELECT COUNT(*) AS jml FROM mata_kuliah')['jml'];
$jml_materi = (int)query_satu($conn, 'SELECT COUNT(*) AS jml FROM materi')['jml'];
$jml_tugas  = (int)query_satu($conn, 'SELECT COUNT(*) AS jml FROM tugas')['jml'];
$daftar     = query_semua($conn, 'SELECT id, kode, nama FROM mata_kuliah ORDER BY kode');

header_halaman('Beranda', 'beranda');
?>
<h1>Learning Management System</h1>
<p class="sub">Pengelolaan mata kuliah, materi pembelajaran, dan tugas.</p>
<hr>
<div class="ringkasan">
    <div>Mata kuliah<strong><?= $jml_mk ?></strong></div>
    <div>Materi<strong><?= $jml_materi ?></strong></div>
    <div>Tugas<strong><?= $jml_tugas ?></strong></div>
</div>
<hr>
<h2>Mata kuliah tersedia</h2>
<?php if (!$daftar): ?>
    <div class="kosong">Belum ada mata kuliah. <a href="tambah_mata_kuliah.php">Tambah mata kuliah</a> lebih dulu.</div>
<?php else: ?>
    <ul>
        <?php foreach ($daftar as $mk): ?>
            <li><a href="detail_mata_kuliah.php?id=<?= (int)$mk['id'] ?>"><?= e($mk['kode']) ?> - <?= e($mk['nama']) ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<hr>
<p>
    <a class="tombol" href="daftar_mata_kuliah.php">Kelola mata kuliah</a>
    <a class="tombol" href="daftar_materi.php">Kelola materi</a>
    <a class="tombol" href="daftar_tugas.php">Kelola tugas</a>
</p>
<?php footer_halaman();
