<?php
require 'koneksi.php';

$id = id_valid($_GET['id'] ?? null);
if ($id === null) {
    flash('error', 'ID materi tidak valid.');
    alihkan('daftar_materi.php');
}
$lama = query_satu($conn, 'SELECT mata_kuliah_id, judul, isi, tautan FROM materi WHERE id = ?', 'i', [$id]);
if (!$lama) {
    flash('error', 'Materi tidak ditemukan.');
    alihkan('daftar_materi.php');
}

$pilihan = daftar_pilihan_mk($conn);
$v = [
    'mata_kuliah_id' => (string)$lama['mata_kuliah_id'],
    'judul'          => $lama['judul'],
    'isi'            => $lama['isi'],
    'tautan'         => (string)($lama['tautan'] ?? ''),
];
$g = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $g['umum'] = 'Token form tidak valid. Muat ulang halaman lalu coba lagi. Data belum tersimpan.';
    } else {
        [$v, $g] = validasi_materi($conn, $_POST);
        if (!$g) {

            if ($v === [
                'mata_kuliah_id' => (string)$lama['mata_kuliah_id'],
                'judul'          => $lama['judul'],
                'isi'            => $lama['isi'],
                'tautan'         => (string)($lama['tautan'] ?? ''),
            ]) {
                flash('info', 'Tidak ada perubahan yang disimpan.');
                alihkan('edit_materi.php?id=' . $id);
            }
            $tautan = $v['tautan'] === '' ? null : $v['tautan'];
            query_ubah(
                $conn,
                'UPDATE materi SET mata_kuliah_id = ?, judul = ?, isi = ?, tautan = ? WHERE id = ?',
                'isssi',
                [(int)$v['mata_kuliah_id'], $v['judul'], $v['isi'], $tautan, $id]
            );
            flash('sukses', 'Materi berhasil diperbarui.');
            alihkan('daftar_materi.php');
        }
        $g['umum'] = 'Data belum tersimpan. Periksa isian yang ditandai.';
    }
}

$aksi = 'edit_materi.php?id=' . $id;
header_halaman('Edit materi', 'materi');
?>
<h1>Edit materi</h1>
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
            <label for="judul">Judul materi</label>
            <input type="text" id="judul" name="judul" value="<?= e($v['judul']) ?>">
            <?= galat($g, 'judul') ?>
        </div>
        <div class="lebar">
            <label for="isi">Isi materi</label>
            <textarea id="isi" name="isi"><?= e($v['isi']) ?></textarea>
            <?= galat($g, 'isi') ?>
        </div>
        <div class="lebar">
            <label for="tautan">Tautan referensi (opsional)</label>
            <input type="text" id="tautan" name="tautan" value="<?= e($v['tautan']) ?>">
            <?= galat($g, 'tautan') ?>
            <div class="petunjuk">Diawali http:// atau https://, maksimal 255 karakter</div>
        </div>
    </div>
    <div class="aksi-form">
        <button type="submit">Simpan data</button>
        <a class="tombol batal" href="daftar_materi.php">Batal</a>
    </div>
</form>

<?php footer_halaman();
