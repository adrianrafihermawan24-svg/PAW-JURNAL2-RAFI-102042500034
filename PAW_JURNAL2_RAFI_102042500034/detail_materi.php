<?php
require 'koneksi.php';

$id = id_valid($_GET['id'] ?? null);
if ($id === null) {
    flash('error', 'ID materi tidak valid.');
    alihkan('daftar_materi.php');
}
$m = query_satu(
    $conn,
    'SELECT m.id, m.judul, m.isi, m.tautan, mk.kode, mk.nama AS nama_mk
     FROM materi m JOIN mata_kuliah mk ON mk.id = m.mata_kuliah_id
     WHERE m.id = ?',
    'i',
    [$id]
);
if (!$m) {
    flash('error', 'Materi tidak ditemukan.');
    alihkan('daftar_materi.php');
}

$tautan_aman = !empty($m['tautan']) && preg_match('#^https?://#i', $m['tautan']);

header_halaman('Detail materi', 'materi');
?>
<h1>Materi</h1>
<h2><?= e($m['judul']) ?></h2>
<dl class="rincian">
    <dt>Mata kuliah</dt><dd><?= e($m['kode']) ?> - <?= e($m['nama_mk']) ?></dd>
    <dt>Judul materi</dt><dd><?= e($m['judul']) ?></dd>
    <dt>Isi materi</dt><dd><?= e($m['isi']) ?></dd>
    <dt>Tautan referensi</dt>
    <dd>
        <?php if ($tautan_aman): ?>
            <a href="<?= e($m['tautan']) ?>" target="_blank" rel="noopener noreferrer"><?= e($m['tautan']) ?></a>
        <?php else: ?>
            -
        <?php endif; ?>
    </dd>
</dl>
<p>
    <a class="tombol" href="edit_materi.php?id=<?= (int)$m['id'] ?>">Edit data</a>
    <a class="tombol batal" href="daftar_materi.php">Kembali</a>
</p>
<?php footer_halaman();
