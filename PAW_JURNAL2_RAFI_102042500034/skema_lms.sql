CREATE DATABASE IF NOT EXISTS db_lms
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_lms;

DROP TABLE IF EXISTS tugas;
DROP TABLE IF EXISTS materi;
DROP TABLE IF EXISTS mata_kuliah;

CREATE TABLE mata_kuliah (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(20) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  dosen VARCHAR(100) NOT NULL,
  semester TINYINT UNSIGNED NOT NULL
) ENGINE=InnoDB;

CREATE TABLE materi (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mata_kuliah_id INT UNSIGNED NOT NULL,
  judul VARCHAR(150) NOT NULL,
  isi TEXT NOT NULL,
  tautan VARCHAR(255) NULL,
  FOREIGN KEY (mata_kuliah_id)
    REFERENCES mata_kuliah(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE tugas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mata_kuliah_id INT UNSIGNED NOT NULL,
  judul VARCHAR(150) NOT NULL,
  instruksi TEXT NOT NULL,
  tenggat DATE NOT NULL,
  status VARCHAR(20) NOT NULL,
  FOREIGN KEY (mata_kuliah_id)
    REFERENCES mata_kuliah(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO mata_kuliah (kode, nama, dosen, semester) VALUES
  ('ALP-101', 'Algoritma dan Pemrograman',      'Dosen ALP', 1),
  ('PAW-101', 'Pengembangan Aplikasi Website',  'Dosen PAW', 3);

INSERT INTO materi (mata_kuliah_id, judul, isi, tautan) VALUES
  (2, 'Dasar PHP',
      'Pengenalan sintaks PHP, variabel, dan pengolahan data dari form.',
      'https://www.php.net/manual/en/langref.php'),
  (1, 'Flowchart dan Pseudocode',
      'Cara menyusun alur penyelesaian masalah sebelum menulis kode.',
      NULL);

INSERT INTO tugas (mata_kuliah_id, judul, instruksi, tenggat, status) VALUES
  (2, 'Latihan CRUD',
      'Buat aplikasi CRUD sederhana dengan PHP dan MySQL sesuai contoh modul.',
      '2026-11-15', 'Aktif'),
  (1, 'Latihan Flowchart',
      'Gambarkan flowchart untuk program penghitung nilai rata-rata.',
      '2026-11-08', 'Draf');
