-- =====================================================================
-- database.sql  |  INVENTORY MANAGEMENT SYSTEM
-- Import file ini lewat phpMyAdmin (menu Import) ATAU jalankan di tab SQL.
-- Berisi: CREATE DATABASE, CREATE TABLE, relasi (FOREIGN KEY), dan data contoh.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS db_inventaris
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_inventaris;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS barang_keluar;
DROP TABLE IF EXISTS barang_masuk;
DROP TABLE IF EXISTS barang;
DROP TABLE IF EXISTS pemasok;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------- TABEL USERS -------------------------------
CREATE TABLE users (
  id_user       INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password      VARCHAR(255) NOT NULL,
  nama_lengkap  VARCHAR(100) NOT NULL,
  email         VARCHAR(100) DEFAULT NULL,
  role          ENUM('admin','petugas') NOT NULL DEFAULT 'petugas',
  status        ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------ TABEL KATEGORI -----------------------------
CREATE TABLE kategori (
  id_kategori   INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL UNIQUE,
  deskripsi     TEXT DEFAULT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------- TABEL PEMASOK -----------------------------
CREATE TABLE pemasok (
  id_pemasok     INT AUTO_INCREMENT PRIMARY KEY,
  nama_pemasok   VARCHAR(100) NOT NULL,
  nama_perusahaan VARCHAR(100) DEFAULT NULL,
  telepon        VARCHAR(20)  DEFAULT NULL,
  email          VARCHAR(100) DEFAULT NULL,
  alamat         TEXT DEFAULT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------- TABEL BARANG -----------------------------
CREATE TABLE barang (
  id_barang     INT AUTO_INCREMENT PRIMARY KEY,
  kode_barang   VARCHAR(30)  NOT NULL UNIQUE,
  nama_barang   VARCHAR(120) NOT NULL,
  id_kategori   INT NOT NULL,
  id_pemasok    INT DEFAULT NULL,
  merek         VARCHAR(80)  DEFAULT NULL,
  spesifikasi   TEXT DEFAULT NULL,
  satuan        VARCHAR(20)  NOT NULL DEFAULT 'Unit',
  harga_beli    DECIMAL(15,2) NOT NULL DEFAULT 0,
  harga_jual    DECIMAL(15,2) NOT NULL DEFAULT 0,
  stok          INT NOT NULL DEFAULT 0,
  stok_minimum  INT NOT NULL DEFAULT 0,
  kondisi       ENUM('Baik','Rusak Ringan','Rusak Berat') NOT NULL DEFAULT 'Baik',
  lokasi        VARCHAR(100) DEFAULT NULL,
  tanggal_masuk DATE NOT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_barang_kategori FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_barang_pemasok FOREIGN KEY (id_pemasok) REFERENCES pemasok(id_pemasok)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------- TABEL BARANG MASUK --------------------------
CREATE TABLE barang_masuk (
  id_masuk      INT AUTO_INCREMENT PRIMARY KEY,
  kode_transaksi VARCHAR(40) NOT NULL UNIQUE,
  id_barang     INT NOT NULL,
  id_pemasok    INT DEFAULT NULL,
  jumlah        INT NOT NULL,
  tanggal_masuk DATE NOT NULL,
  keterangan    TEXT DEFAULT NULL,
  id_user       INT NOT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_masuk_barang  FOREIGN KEY (id_barang)  REFERENCES barang(id_barang)   ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_masuk_pemasok FOREIGN KEY (id_pemasok) REFERENCES pemasok(id_pemasok) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_masuk_user    FOREIGN KEY (id_user)    REFERENCES users(id_user)      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------- TABEL BARANG KELUAR --------------------------
CREATE TABLE barang_keluar (
  id_keluar      INT AUTO_INCREMENT PRIMARY KEY,
  kode_transaksi VARCHAR(40) NOT NULL UNIQUE,
  id_barang      INT NOT NULL,
  jumlah         INT NOT NULL,
  tujuan         VARCHAR(120) NOT NULL,
  tanggal_keluar DATE NOT NULL,
  keterangan     TEXT DEFAULT NULL,
  id_user        INT NOT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_keluar_barang FOREIGN KEY (id_barang) REFERENCES barang(id_barang) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_keluar_user   FOREIGN KEY (id_user)   REFERENCES users(id_user)    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================== DATA CONTOH ==============================
-- Password admin   : admin123
-- Password petugas : petugas123
INSERT INTO users (username, password, nama_lengkap, email, role) VALUES
('admin',   '$2y$12$6VeadujvKB1mwPD7REz4pOzh2NCdcxripic0tGnWl.JWnxod7h4Ve', 'Administrator', 'admin@inventaris.test',   'admin'),
('petugas', '$2y$12$tvPu7euxgwVCB5OfXLFSa.gz/ri4W.T58mNgqgrC61SaOA4g9EbC2', 'Petugas Gudang','petugas@inventaris.test', 'petugas');

INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Elektronik',      'Peralatan elektronik dan komputer'),
('Alat Tulis',      'Perlengkapan tulis kantor dan sekolah'),
('Furniture',       'Meja, kursi, dan perabot ruangan'),
('Jaringan',        'Perangkat jaringan komputer'),
('Alat Kebersihan', 'Peralatan kebersihan gedung');

INSERT INTO pemasok (nama_pemasok, nama_perusahaan, telepon, email, alamat) VALUES
('Budi Santoso',  'CV Maju Komputer',   '081234567890', 'budi@majukomputer.test',  'Jl. Merdeka No. 12, Jakarta'),
('Siti Rahayu',   'PT Sinar Elektronik','082198765432', 'siti@sinarelektro.test',  'Jl. Diponegoro No. 5, Bandung'),
('Andi Wijaya',   'Toko Alat Tulis Jaya','081377788899','andi@atkjaya.test',       'Jl. Pahlawan No. 88, Surabaya');

INSERT INTO barang (kode_barang, nama_barang, id_kategori, id_pemasok, merek, spesifikasi, satuan, harga_beli, harga_jual, stok, stok_minimum, kondisi, lokasi, tanggal_masuk) VALUES
('BRG-001','Laptop Lenovo V14',       1,1,'Lenovo','Ryzen 5, RAM 8GB, SSD 512GB','Unit', 6500000,7200000, 12, 5,'Baik','Gudang A - Rak 1','2026-01-10'),
('BRG-002','Mouse Wireless Logitech', 1,1,'Logitech','Wireless 2.4GHz, USB receiver','Pcs',  120000, 165000, 40,10,'Baik','Gudang A - Rak 2','2026-01-12'),
('BRG-003','Kertas HVS A4 80gr',      2,3,'Sinar Dunia','1 rim isi 500 lembar','Rim',      52000,  60000,  8,10,'Baik','Gudang B - Rak 1','2026-02-02'),
('BRG-004','Pulpen Standard AE7',     2,3,'Standard','Tinta hitam 0.5mm','Pcs',              2500,   4000,150,25,'Baik','Gudang B - Rak 2','2026-02-02'),
('BRG-005','Kursi Kantor Ergonomis',  3,2,'Ergosit','Sandaran jaring, hidrolik','Unit',    750000, 950000,  4, 3,'Baik','Gudang C','2026-02-15'),
('BRG-006','Switch 24 Port',          4,2,'TP-Link','Gigabit unmanaged 24 port','Unit',    1200000,1450000, 0, 2,'Baik','Gudang A - Rak 3','2026-03-01'),
('BRG-007','Kabel LAN Cat6 (Roll)',   4,1,'Belden','305 meter per roll','Roll',            1500000,1750000,  3, 2,'Baik','Gudang A - Rak 3','2026-03-05'),
('BRG-008','Sapu Lantai',             5,3,'Nagata','Gagang aluminium','Pcs',                 25000,  38000, 20, 5,'Baik','Gudang D','2026-03-10');

INSERT INTO barang_masuk (kode_transaksi, id_barang, id_pemasok, jumlah, tanggal_masuk, keterangan, id_user) VALUES
('TM-20260110-0001',1,1,10,'2026-01-10','Pembelian awal laptop',1),
('TM-20260112-0002',2,1,50,'2026-01-12','Pembelian mouse',1),
('TM-20260202-0003',3,3,20,'2026-02-02','Restok kertas',2),
('TM-20260215-0004',5,2, 6,'2026-02-15','Pembelian kursi kantor',2),
('TM-20260305-0005',7,1, 5,'2026-03-05','Pembelian kabel jaringan',1);

INSERT INTO barang_keluar (kode_transaksi, id_barang, jumlah, tujuan, tanggal_keluar, keterangan, id_user) VALUES
('TK-20260120-0001',2,10,'Lab RPL 1','2026-01-20','Kebutuhan praktikum',2),
('TK-20260210-0002',3,12,'Tata Usaha','2026-02-10','Kebutuhan administrasi',2),
('TK-20260220-0003',5, 2,'Ruang Guru','2026-02-20','Penggantian kursi rusak',1),
('TK-20260308-0004',7, 2,'Lab Jaringan','2026-03-08','Instalasi jaringan baru',1);
