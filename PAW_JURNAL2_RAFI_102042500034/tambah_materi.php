<?php
require 'koneksi.php';

$pilihan = daftar_pilihan_mk($conn);

$awal = id_valid($_GET['mk'] ?? null);
$v = ['mata_kuliah_id' => $awal !== null ? (string)$awal : '', 'judul' => '', 'isi' => '', 'tautan' => ''];
$g = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $g['umum'] = 'Token form tidak valid. Muat ulang halaman lalu coba lagi. Data belum tersimpan.';
    } else {
        [$v, $g] = validasi_materi($conn, $_POST);
        if (!$g) {

            $tautan = $v['tautan'] === '' ? null : $v['tautan'];
            query_ubah(
                $conn,
                'INSERT INTO materi (mata_kuliah_id, judul, isi, tautan) VALUES (?, ?, ?, ?)',
                'isss',
                [(int)$v['mata_kuliah_id'], $v['judul'], $v['isi'], $tautan]
            );
            flash('sukses', 'Materi berhasil ditambahkan.');
            alihkan('daftar_materi.php');
        }
        $g['umum'] = 'Data belum tersimpan. Periksa isian yang ditandai.';
    }
}

$aksi = 'tambah_materi.php';
header_halaman('Tambah materi', 'materi');
?>
<h1>Tambah materi</h1>
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
