<?php
require 'koneksi.php';

$q = mb_substr(bersihkan($_GET['q'] ?? null), 0, 100, 'UTF-8');

$sql = 'SELECT id, kode, nama, dosen, semester FROM mata_kuliah';
$tipe = '';
$param = [];
if ($q !== '') {

    $sql .= ' WHERE kode LIKE ? OR nama LIKE ?';
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $tipe = 'ss';
    $param = [$like, $like];
}
$sql .= ' ORDER BY nama';
$baris = query_semua($conn, $sql, $tipe, $param);

header_halaman('Mata kuliah', 'mata_kuliah');
?>
<div class="judul-baris">
    <h1>Mata kuliah</h1>
    <a class="tombol" href="tambah_mata_kuliah.php">Tambah mata kuliah</a>
</div>
<p class="sub">Menampilkan <?= count($baris) ?> data.</p>

<form method="get" class="cari">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari kode atau nama">
    <button type="submit">Cari</button>
    <a class="tombol batal" href="daftar_mata_kuliah.php">Reset</a>
</form>

<?php if (!$baris): ?>
    <div class="kosong"><?= $q !== '' ? 'Data tidak ditemukan.' : 'Belum ada data mata kuliah.' ?></div>
<?php else: ?>
    <table>
        <tr><th>Mata kuliah</th><th>Dosen / semester</th><th>Aksi</th></tr>
        <?php foreach ($baris as $b): ?>
            <tr>
                <td><?= e($b['kode']) ?> - <?= e($b['nama']) ?></td>
                <td><?= e($b['dosen']) ?> / Semester <?= (int)$b['semester'] ?></td>
                <td class="aksi">
                    <a class="tombol kecil" href="detail_mata_kuliah.php?id=<?= (int)$b['id'] ?>">Detail</a>
                    <a class="tombol kecil" href="edit_mata_kuliah.php?id=<?= (int)$b['id'] ?>">Edit</a>
                    <a class="tombol kecil hapus" href="hapus_mata_kuliah.php?id=<?= (int)$b['id'] ?>">Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php footer_halaman();
