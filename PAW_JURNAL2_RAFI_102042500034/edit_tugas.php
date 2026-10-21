<?php
require 'koneksi.php';

$id = id_valid($_GET['id'] ?? null);
if ($id === null) {
    flash('error', 'ID tugas tidak valid.');
    alihkan('daftar_tugas.php');
}
$lama = query_satu($conn, 'SELECT mata_kuliah_id, judul, instruksi, tenggat, status FROM tugas WHERE id = ?', 'i', [$id]);
if (!$lama) {
    flash('error', 'Tugas tidak ditemukan.');
    alihkan('daftar_tugas.php');
}

$pilihan = daftar_pilihan_mk($conn);
$asli = [
    'mata_kuliah_id' => (string)$lama['mata_kuliah_id'],
    'judul'          => $lama['judul'],
    'instruksi'      => $lama['instruksi'],
    'tenggat'        => $lama['tenggat'],
    'status'         => $lama['status'],
];
$v = $asli;
$g = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $g['umum'] = 'Token form tidak valid. Muat ulang halaman lalu coba lagi. Data belum tersimpan.';
    } else {
        [$v, $g] = validasi_tugas($conn, $_POST);
        if (!$g) {
            if ($v === $asli) {
                flash('info', 'Tidak ada perubahan yang disimpan.');
                alihkan('edit_tugas.php?id=' . $id);
            }
            query_ubah(
                $conn,
                'UPDATE tugas SET mata_kuliah_id = ?, judul = ?, instruksi = ?, tenggat = ?, status = ? WHERE id = ?',
                'issssi',
                [(int)$v['mata_kuliah_id'], $v['judul'], $v['instruksi'], $v['tenggat'], $v['status'], $id]
            );
            flash('sukses', 'Tugas berhasil diperbarui.');
            alihkan('daftar_tugas.php');
        }
        $g['umum'] = 'Data belum tersimpan. Periksa isian yang ditandai.';
    }
}

$aksi = 'edit_tugas.php?id=' . $id;
header_halaman('Edit tugas', 'tugas');
?>
<h1>Edit tugas</h1>
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
