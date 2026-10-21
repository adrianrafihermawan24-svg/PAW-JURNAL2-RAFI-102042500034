<?php
require 'koneksi.php';

$pilihan = daftar_pilihan_mk($conn);
$awal = id_valid($_GET['mk'] ?? null);

$v = [
    'mata_kuliah_id' => $awal !== null ? (string)$awal : '',
    'judul' => '', 'instruksi' => '', 'tenggat' => '', 'status' => 'Draf',
];
$g = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $g['umum'] = 'Token form tidak valid. Muat ulang halaman lalu coba lagi. Data belum tersimpan.';
    } else {
        [$v, $g] = validasi_tugas($conn, $_POST);
        if (!$g) {
            query_ubah(
                $conn,
                'INSERT INTO tugas (mata_kuliah_id, judul, instruksi, tenggat, status) VALUES (?, ?, ?, ?, ?)',
                'issss',
                [(int)$v['mata_kuliah_id'], $v['judul'], $v['instruksi'], $v['tenggat'], $v['status']]
            );
            flash('sukses', 'Tugas berhasil ditambahkan.');
            alihkan('daftar_tugas.php');
        }
        $g['umum'] = 'Data belum tersimpan. Periksa isian yang ditandai.';
    }
}

$aksi = 'tambah_tugas.php';
header_halaman('Tambah tugas', 'tugas');
?>
<h1>Tambah tugas</h1>
<?php if (!$pilihan): ?>
    <div class="pesan info">Belum ada mata kuliah. <a href="tambah_mata_kuliah.php">Buat mata kuliah</a> lebih dulu.</div>
<?php endif; ?>
<form method="post" action="<?= e($aksi) ?>" novalidate>
    <?= csrf_field() ?>
    <?php if (!empty($g['umum'])): ?>
        <div class="pesan error"><?= e($g['umum']) ?></div>
    <?php endif; ?>
    <div class="grid">
        <div>
            <label for="mata_kuliah_id">Mata kuliah</label>
            <select id="mata_kuliah_id" name="mata_kuliah_id">
                <?= opsi_mk($pilihan, $v['mata_kuliah_id'], 'Pilih Mata kuliah') ?>
            </select>
            <?= galat($g, 'mata_kuliah_id') ?>
        </div>
        <div>
            <label for="judul">Judul tugas</label>
            <input type="text" id="judul" name="judul" value="<?= e($v['judul']) ?>">
            <?= galat($g, 'judul') ?>
        </div>
        <div class="lebar">
            <label for="instruksi">Instruksi tugas</label>
            <textarea id="instruksi" name="instruksi"><?= e($v['instruksi']) ?></textarea>
            <?= galat($g, 'instruksi') ?>
        </div>
        <div>
            <label for="tenggat">Tenggat</label>
            <input type="date" id="tenggat" name="tenggat" value="<?= e($v['tenggat']) ?>">
            <?= galat($g, 'tenggat') ?>
        </div>
        <div>
            <label for="status">Status</label>
            <select id="status" name="status">
                <?php foreach (STATUS_TUGAS as $s): ?>
                    <option value="<?= e($s) ?>"<?= $v['status'] === $s ? ' selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
            <?= galat($g, 'status') ?>
        </div>
    </div>
    <div class="aksi-form">
        <button type="submit">Simpan data</button>
        <a class="tombol batal" href="daftar_tugas.php">Batal</a>
    </div>
</form>

<?php footer_halaman();
