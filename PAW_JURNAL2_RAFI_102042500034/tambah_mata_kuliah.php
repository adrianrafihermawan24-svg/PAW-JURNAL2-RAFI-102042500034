<?php
require 'koneksi.php';

$v = ['kode' => '', 'nama' => '', 'dosen' => '', 'semester' => ''];
$g = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {

        $g['umum'] = 'Token form tidak valid. Muat ulang halaman lalu coba lagi. Data belum tersimpan.';
    } else {
        [$v, $g] = validasi_mata_kuliah($conn, $_POST);
        if (!$g) {
            try {
                query_ubah(
                    $conn,
                    'INSERT INTO mata_kuliah (kode, nama, dosen, semester) VALUES (?, ?, ?, ?)',
                    'sssi',
                    [$v['kode'], $v['nama'], $v['dosen'], (int)$v['semester']]
                );
                flash('sukses', 'Mata kuliah berhasil ditambahkan.');
                alihkan('daftar_mata_kuliah.php');
            } catch (mysqli_sql_exception $ex) {

                if ((int)$ex->getCode() === 1062) {
                    $g['kode'] = 'Kode mata kuliah sudah digunakan.';
                } else {
                    throw $ex;
                }
            }
        }
        if ($g) {
            $g['umum'] = 'Data belum tersimpan. Periksa isian yang ditandai.';
        }
    }
}

$aksi = 'tambah_mata_kuliah.php';
header_halaman('Tambah mata kuliah', 'mata_kuliah');
?>
<h1>Tambah mata kuliah</h1>
<form method="post" action="<?= e($aksi) ?>" novalidate>
    <?= csrf_field() ?>
    <?php if (!empty($g['umum'])): ?>
        <div class="pesan error"><?= e($g['umum']) ?></div>
    <?php endif; ?>
    <div class="grid">
        <div>
            <label for="kode">Kode mata kuliah</label>
            <input type="text" id="kode" name="kode" value="<?= e($v['kode']) ?>">
            <?= galat($g, 'kode') ?>
            <div class="petunjuk">3-20 karakter: huruf kapital, angka, atau tanda -</div>
        </div>
        <div>
            <label for="nama">Nama mata kuliah</label>
            <input type="text" id="nama" name="nama" value="<?= e($v['nama']) ?>">
            <?= galat($g, 'nama') ?>
        </div>
        <div>
            <label for="dosen">Dosen pengampu</label>
            <input type="text" id="dosen" name="dosen" value="<?= e($v['dosen']) ?>">
            <?= galat($g, 'dosen') ?>
        </div>
        <div>
            <label for="semester">Semester</label>
            <input type="text" id="semester" name="semester" value="<?= e($v['semester']) ?>">
            <?= galat($g, 'semester') ?>
            <div class="petunjuk">Bilangan bulat 1-14</div>
        </div>
    </div>
    <div class="aksi-form">
        <button type="submit">Simpan data</button>
        <a class="tombol batal" href="daftar_mata_kuliah.php">Batal</a>
    </div>
</form>

<?php footer_halaman();
