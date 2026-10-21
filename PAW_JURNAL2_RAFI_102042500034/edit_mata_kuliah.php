<?php
require 'koneksi.php';

$id = id_valid($_GET['id'] ?? null);
if ($id === null) {
    flash('error', 'ID mata kuliah tidak valid.');
    alihkan('daftar_mata_kuliah.php');
}
$lama = query_satu($conn, 'SELECT kode, nama, dosen, semester FROM mata_kuliah WHERE id = ?', 'i', [$id]);
if (!$lama) {
    flash('error', 'Mata kuliah tidak ditemukan.');
    alihkan('daftar_mata_kuliah.php');
}

$v = [
    'kode' => $lama['kode'], 'nama' => $lama['nama'],
    'dosen' => $lama['dosen'], 'semester' => (string)$lama['semester'],
];
$g = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $g['umum'] = 'Token form tidak valid. Muat ulang halaman lalu coba lagi. Data belum tersimpan.';
    } else {

        [$v, $g] = validasi_mata_kuliah($conn, $_POST, $id);
        if (!$g) {

            $sama = $v['kode'] === $lama['kode'] && $v['nama'] === $lama['nama']
                && $v['dosen'] === $lama['dosen'] && $v['semester'] === (string)$lama['semester'];
            if ($sama) {
                flash('info', 'Tidak ada perubahan yang disimpan.');
                alihkan('edit_mata_kuliah.php?id=' . $id);
            }
            try {

                query_ubah(
                    $conn,
                    'UPDATE mata_kuliah SET kode = ?, nama = ?, dosen = ?, semester = ? WHERE id = ?',
                    'sssii',
                    [$v['kode'], $v['nama'], $v['dosen'], (int)$v['semester'], $id]
                );
                flash('sukses', 'Mata kuliah berhasil diperbarui.');
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

$aksi = 'edit_mata_kuliah.php?id=' . $id;
header_halaman('Edit mata kuliah', 'mata_kuliah');
?>
<h1>Edit mata kuliah</h1>
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
