-- PACIRA - Explore Destinasi Wisata
-- Import lewat phpMyAdmin (tab Import) atau jalankan di tab SQL.
CREATE DATABASE IF NOT EXISTS pacira_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pacira_db;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS ulasan, kunjungan, destinasi, pengaturan, users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('wisatawan','manager','admin') NOT NULL DEFAULT 'wisatawan',
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE destinasi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(120) NOT NULL,
  kategori VARCHAR(40) NOT NULL,
  lokasi VARCHAR(120) NOT NULL,
  deskripsi TEXT,
  harga_tiket INT NOT NULL DEFAULT 0,
  gambar VARCHAR(255) NULL,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE kunjungan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  destinasi_id INT NOT NULL,
  tanggal DATE NOT NULL,
  jumlah_orang INT NOT NULL DEFAULT 1,
  status ENUM('dijadwalkan','selesai','dibatalkan') NOT NULL DEFAULT 'dijadwalkan',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (destinasi_id) REFERENCES destinasi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ulasan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  destinasi_id INT NOT NULL,
  rating TINYINT NOT NULL,
  komentar TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_ulasan (user_id, destinasi_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (destinasi_id) REFERENCES destinasi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pengaturan (
  kunci VARCHAR(50) PRIMARY KEY,
  nilai TEXT
) ENGINE=InnoDB;

-- Semua akun demo berpassword: password
INSERT INTO users (id,nama,username,email,password,role) VALUES
(1,'Administrator','admin','admin@pacira.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin'),
(2,'Manager Wisata','manager','manager@pacira.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','manager'),
(3,'Wisatawan Demo','wisatawan','wisatawan@pacira.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','wisatawan'),
(4,'Andi Pratama','andi','andi@pacira.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','wisatawan'),
(5,'Sari Wulandari','sari','sari@pacira.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','wisatawan'),
(6,'Budi Santoso','budi','budi@pacira.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','wisatawan'),
(7,'Citra Lestari','citra','citra@pacira.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','wisatawan');

INSERT INTO destinasi (id,nama,kategori,lokasi,deskripsi,harga_tiket) VALUES
(1,'Pantai Klayar','Pantai','Pacitan, Jawa Timur','Pantai dengan pasir putih, karang unik, dan seruling samudra yang menyemburkan air laut. Cocok untuk menikmati matahari terbenam.',15000),
(2,'Gunung Bromo','Gunung','Probolinggo, Jawa Timur','Lautan pasir dan kawah aktif yang terkenal dengan pemandangan matahari terbit dari Penanjakan.',35000),
(3,'Candi Borobudur','Budaya','Magelang, Jawa Tengah','Candi Buddha terbesar di dunia, warisan dunia UNESCO dengan relief yang megah.',50000),
(4,'Raja Ampat','Pantai','Papua Barat Daya','Gugusan pulau karang dengan keanekaragaman bawah laut terbaik untuk menyelam dan snorkeling.',250000),
(5,'Danau Toba','Alam','Sumatera Utara','Danau vulkanik terbesar di Asia Tenggara dengan Pulau Samosir dan budaya Batak.',20000),
(6,'Pantai Kuta','Pantai','Bali','Pantai ramai dengan ombak untuk berselancar dan suasana malam yang hidup.',10000),
(7,'Kawah Ijen','Gunung','Banyuwangi, Jawa Timur','Kawah dengan api biru yang langka dan danau asam berwarna toska.',30000),
(8,'Labuan Bajo','Alam','Nusa Tenggara Timur','Gerbang menuju Taman Nasional Komodo dengan gugusan pulau dan pantai berwarna merah muda.',150000);

INSERT INTO kunjungan (user_id,destinasi_id,tanggal,jumlah_orang,status) VALUES
(3,1,'2026-05-10',2,'selesai'),(4,2,'2026-05-18',4,'selesai'),(5,3,'2026-05-25',3,'selesai'),
(6,4,'2026-06-07',2,'selesai'),(3,5,'2026-06-14',5,'selesai'),(7,1,'2026-06-21',2,'selesai'),(4,6,'2026-06-28',3,'selesai'),
(5,2,'2026-07-05',4,'selesai'),(6,3,'2026-07-12',2,'selesai'),(3,7,'2026-07-19',6,'selesai'),(7,8,'2026-07-26',2,'selesai'),(4,1,'2026-07-30',3,'selesai'),
(3,2,'2026-08-09',2,'selesai'),(5,4,'2026-08-16',3,'selesai'),(6,5,'2026-08-23',4,'selesai'),(7,3,'2026-08-30',2,'selesai'),
(4,8,'2026-09-06',2,'selesai'),(3,6,'2026-09-13',3,'selesai'),(5,7,'2026-09-20',2,'selesai'),(6,1,'2026-09-27',5,'selesai'),(7,2,'2026-09-28',2,'dibatalkan'),
(3,4,'2026-10-04',2,'selesai'),(4,3,'2026-10-10',3,'selesai'),(5,8,'2026-10-25',2,'dijadwalkan'),(3,1,'2026-11-02',2,'dijadwalkan');

INSERT INTO ulasan (user_id,destinasi_id,rating,komentar) VALUES
(3,1,5,'Pasirnya putih dan bersih, sunset-nya luar biasa.'),
(4,2,5,'Sunrise di Penanjakan wajib dicoba, berangkat jam 3 pagi.'),
(5,3,4,'Reliefnya indah, datang pagi supaya tidak terlalu panas.'),
(6,4,4,'Pemandangannya seperti lukisan, perjalanan menuju sana cukup jauh.'),
(3,5,5,'Udara sejuk dan pemandangan danau sangat menenangkan.'),
(7,1,4,'Seruling samudranya seru dilihat, hati-hati dengan ombak.'),
(4,6,4,'Ramai, tapi ombaknya bagus untuk belajar selancar.'),
(5,2,5,'Pengalaman yang tidak terlupakan.'),
(6,3,5,'Terawat dan informatif, pemandu lokalnya ramah.'),
(3,7,5,'Api birunya langka, bawa masker gas dan jaket tebal.'),
(7,8,4,'Pulau-pulaunya cantik, jadwalkan trip kapal sehari penuh.'),
(4,1,5,'Akan kembali lagi bersama keluarga.');

INSERT INTO pengaturan (kunci,nilai) VALUES
('nama_aplikasi','PACIRA'),
('tagline','Explore Destinasi Wisata'),
('email_kontak','info@pacira.local'),
('izin_registrasi','1');
