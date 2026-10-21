<?php
require 'koneksi.php';

$q = mb_substr(bersihkan($_GET['q'] ?? null), 0, 150, 'UTF-8');
$filter = id_valid($_GET['mk'] ?? null);

$sql = 'SELECT t.id, t.judul, t.tenggat, t.status, mk.kode, mk.nama AS nama_mk
        FROM tugas t JOIN mata_kuliah mk ON mk.id = t.mata_kuliah_id
        WHERE 1 = 1';
$tipe = '';
$param = [];
if ($q !== '') {
    $sql .= ' AND t.judul LIKE ?';
    $tipe .= 's';
    $param[] = '%' . addcslashes($q, '%_\\') . '%';
}
if ($filter !== null) {
    $sql .= ' AND t.mata_kuliah_id = ?';
    $tipe .= 'i';
    $param[] = $filter;
}
$sql .= ' ORDER BY t.tenggat, t.judul';
$baris = query_semua($conn, $sql, $tipe, $param);
$pilihan = daftar_pilihan_mk($conn);
$ada_filter = ($q !== '' || $filter !== null);

header_halaman('Tugas', 'tugas');
?>
<div class="judul-baris">
    <h1>Tugas</h1>
    <a class="tombol" href="tambah_tugas.php">Tambah tugas</a>
</div>
<p class="sub">Menampilkan <?= count($baris) ?> data.</p>

<form method="get" class="cari">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari judul tugas">
    <select name="mk"><?= opsi_mk($pilihan, $filter, 'Semua mata kuliah') ?></select>
    <button type="submit">Cari</button>
    <a class="tombol batal" href="daftar_tugas.php">Reset</a>
</form>

<?php if (!$baris): ?>
    <div class="kosong"><?= $ada_filter ? 'Data tidak ditemukan.' : 'Belum ada data tugas.' ?></div>
<?php else: ?>
    <table>
        <tr><th>Judul</th><th>Mata kuliah</th><th>Tenggat</th><th>Status</th><th>Aksi</th></tr>
        <?php foreach ($baris as $b): ?>
            <tr>
                <td><?= e($b['judul']) ?></td>
                <td><?= e($b['kode']) ?> - <?= e($b['nama_mk']) ?></td>
                <td><?= e(tgl_indo($b['tenggat'])) ?></td>
                <td><?= e($b['status']) ?></td>
                <td class="aksi">
                    <a class="tombol kecil" href="detail_tugas.php?id=<?= (int)$b['id'] ?>">Detail</a>
                    <a class="tombol kecil" href="edit_tugas.php?id=<?= (int)$b['id'] ?>">Edit</a>
                    <a class="tombol kecil hapus" href="hapus_tugas.php?id=<?= (int)$b['id'] ?>">Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php footer_halaman();
