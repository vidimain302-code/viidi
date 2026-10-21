# INVENTORY MANAGEMENT SYSTEM — Panduan Lengkap

Aplikasi manajemen inventaris barang berbasis **PHP Native + MySQL + Bootstrap 5**,
dijalankan memakai **XAMPP (Apache + MySQL)**.

---

## 1. Penjelasan Konsep Aplikasi

Aplikasi ini mencatat keluar-masuk barang di gudang/sekolah secara digital.

Alurnya sederhana:

1. Petugas **login** memakai username & password yang tersimpan di database.
2. Data induk dicatat dulu: **kategori**, **pemasok**, lalu **barang**.
3. Setiap barang datang dicatat di **Barang Masuk** → stok barang **otomatis bertambah**.
4. Setiap barang dipakai/dikeluarkan dicatat di **Barang Keluar** → stok **otomatis berkurang**
   (dan sistem menolak jika jumlah keluar melebihi stok).
5. Halaman **Stok** dan **Dashboard** menampilkan kondisi terkini + peringatan stok menipis.
6. **Laporan** dapat difilter, dicetak, atau diekspor ke Excel/PDF.

Ada 2 role:

| Role | Hak akses |
|------|-----------|
| Admin | Semua menu, termasuk menu **Pengguna** |
| Petugas | Semua menu **kecuali** Pengguna |

---

## 2. ERD / Relasi Antar Tabel

```text
   kategori                 pemasok                    users
  ┌───────────────┐        ┌────────────────┐        ┌────────────────┐
  │ id_kategori PK│        │ id_pemasok  PK │        │ id_user     PK │
  │ nama_kategori │        │ nama_pemasok   │        │ username  (UQ) │
  │ deskripsi     │        │ nama_perusahaan│        │ password(hash) │
  └──────┬────────┘        │ telepon/email  │        │ role           │
         │ 1               └───────┬────────┘        └────────┬───────┘
         │                         │ 1                        │ 1
         │ N                       │ N                        │
  ┌──────┴─────────────────────────┴───────┐                  │
  │                barang                  │                  │
  │ id_barang PK | kode_barang (UNIQUE)    │                  │
  │ nama_barang | id_kategori FK           │                  │
  │ id_pemasok FK | merek | spesifikasi    │                  │
  │ satuan | harga_beli | harga_jual       │                  │
  │ stok | stok_minimum | kondisi | lokasi │                  │
  └───────┬──────────────────────┬─────────┘                  │
          │ 1                    │ 1                          │
          │ N                    │ N                          │
  ┌───────┴────────┐    ┌────────┴────────┐                   │
  │  barang_masuk  │    │  barang_keluar  │                   │
  │ id_masuk PK    │    │ id_keluar PK    │───────────────────┘
  │ kode_transaksi │    │ kode_transaksi  │  (id_user FK)
  │ id_barang   FK │    │ id_barang    FK │
  │ id_pemasok  FK │    │ jumlah | tujuan │
  │ jumlah | tgl   │    │ tanggal_keluar  │
  │ id_user     FK │    │ id_user      FK │
  └────────────────┘    └─────────────────┘
```

Relasi: satu kategori punya banyak barang; satu pemasok memasok banyak barang;
satu barang punya banyak transaksi masuk dan banyak transaksi keluar;
setiap transaksi dicatat oleh satu user.

---

## 3. Struktur Database (`db_inventaris`)

| Tabel | Isi utama |
|-------|-----------|
| `users` | akun login (username UNIQUE, password hash, role enum admin/petugas, status) |
| `kategori` | nama kategori (UNIQUE) + deskripsi |
| `pemasok` | data pemasok/supplier |
| `barang` | data barang + stok, FK ke kategori & pemasok |
| `barang_masuk` | transaksi masuk, FK ke barang, pemasok, users |
| `barang_keluar` | transaksi keluar, FK ke barang, users |

Semua tabel memakai `AUTO_INCREMENT`, `NOT NULL`, `DEFAULT`, dan `TIMESTAMP`
(`created_at` / `updated_at`). Mesin penyimpanan **InnoDB** supaya FOREIGN KEY aktif.

---

## 4. File `database.sql`

Ada di root project: **`database.sql`**. Isinya `CREATE DATABASE`, `CREATE TABLE`
(lengkap dengan PK/FK), serta `INSERT` data contoh: 2 user, 5 kategori, 3 pemasok,
8 barang, 5 transaksi masuk, 4 transaksi keluar.

---

## 5. Struktur Folder Project

```text
inventaris/
├── config/database.php          → koneksi PDO ke MySQL
├── includes/
│   ├── functions.php            → fungsi bantu (e(), flash, status stok, rupiah)
│   ├── auth.php                 → penjaga login & role (wajib_login, wajib_admin)
│   ├── header.php               → <head>, sidebar, navbar, alert
│   ├── footer.php               → penutup + Bootstrap/Chart.js
│   └── export.php               → export Excel + tombol cetak laporan
├── assets/css/style.css         → seluruh tampilan aplikasi
├── assets/js/script.js          → sidebar mobile, konfirmasi hapus, validasi
├── assets/images/               → tempat gambar (opsional)
├── auth/login.php | proses_login.php | logout.php
├── dashboard/index.php          → ringkasan angka + 3 grafik
├── barang/index.php | tambah.php | edit.php | hapus.php | detail.php
├── kategori/index.php | tambah.php | edit.php | hapus.php
├── pemasok/index.php | tambah.php | edit.php | hapus.php | detail.php
├── transaksi/masuk.php | keluar.php | riwayat.php
├── stok/index.php               → monitoring stok + peringatan
├── pengguna/index.php | tambah.php | edit.php | hapus.php   (admin saja)
├── laporan/barang.php | masuk.php | keluar.php | stok.php
├── index.php                    → pengarah: login atau dashboard
└── database.sql
```

**Hubungan antar file:** setiap halaman memanggil `includes/auth.php`
(yang memanggil `config/database.php` + `includes/functions.php`), lalu menampilkan
`includes/header.php` di atas dan `includes/footer.php` di bawah. Jadi jika Anda
mengubah menu sidebar, cukup ubah `includes/header.php` satu kali.

---

## 6–17. Isi Kode

Seluruh kode sudah tertulis lengkap di dalam file masing-masing (tanpa potongan "...").
Setiap file diawali komentar yang menjelaskan fungsinya. Bagian penting:

- **Koneksi** (`config/database.php`) — PDO dengan `ERRMODE_EXCEPTION` dan
  `EMULATE_PREPARES = false` agar prepared statement benar-benar aman dari SQL Injection.
- **Login** (`auth/proses_login.php`) — `password_verify()` + `session_regenerate_id(true)`.
- **Proteksi halaman** (`includes/auth.php`) — halaman tanpa session dilempar ke login;
  menu Pengguna memakai `wajib_admin()`.
- **Stok otomatis** — `transaksi/masuk.php` menjalankan
  `UPDATE barang SET stok = stok + ?` dan `transaksi/keluar.php` menjalankan
  `UPDATE barang SET stok = stok - ? WHERE id_barang=? AND stok >= ?`,
  keduanya dibungkus `beginTransaction()/commit()` supaya data tidak pernah setengah jadi.
- **Laporan** — filter tanggal/kategori/status, tombol **Cetak**, **Export Excel** (.xls),
  dan **Export PDF** (lewat dialog cetak → "Save as PDF").
- **CSS** (`assets/css/style.css`) — warna navy + teal, sidebar, kartu, tabel, responsif,
  dan aturan khusus `@media print`.
- **JavaScript** (`assets/js/script.js`) — sidebar HP, konfirmasi hapus, validasi Bootstrap,
  alert hilang otomatis. Di halaman barang keluar ada skrip pembatas jumlah sesuai stok.

---

## 18. Cara Menjalankan dengan XAMPP

1. Install **XAMPP** (PHP 8.x).
2. Buka **XAMPP Control Panel** → klik **Start** pada **Apache** dan **MySQL**.
3. Pastikan keduanya berwarna hijau.

## 19. Cara Membuat Database di phpMyAdmin

1. Buka `http://localhost/phpmyadmin`.
2. Klik tab **Import** → **Choose File** → pilih `database.sql` → klik **Go/Kirim**.
3. Database `db_inventaris` beserta semua tabel dan data contoh akan terbuat otomatis.
   (Alternatif: tab **SQL** → tempel isi `database.sql` → **Go**.)

## 20. Cara Memasukkan Project ke htdocs

1. Salin folder `inventaris` ke `C:\xampp\htdocs\`.
2. Hasil akhir: `C:\xampp\htdocs\inventaris\index.php`.
3. Buka browser: `http://localhost/inventaris`.

## 21. Cara Login

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |
| Petugas | `petugas` | `petugas123` |

Setelah login sukses Anda diarahkan ke Dashboard. Ganti password lewat menu
**Pengguna → Edit** setelah aplikasi dipakai sungguhan.

## 22. Cara Menguji Setiap Fitur

1. **Login salah** → masukkan password ngawur, harus muncul "Username atau password salah".
2. **Proteksi halaman** → logout, lalu buka `http://localhost/inventaris/dashboard/index.php`,
   harus dilempar kembali ke halaman login.
3. **Role** → login sebagai `petugas`, menu **Pengguna** tidak muncul; jika URL-nya
   dipaksa, muncul pesan "Anda tidak memiliki hak akses".
4. **Kategori** → tambah "Olahraga", edit, lalu coba hapus kategori yang masih dipakai barang
   (harus ditolak).
5. **Pemasok** → tambah, lihat detail, edit, hapus.
6. **Barang** → tambah barang baru (kode harus unik), cari, filter kategori, buka detail,
   edit, hapus.
7. **Barang Masuk** → simpan 10 unit untuk satu barang, lalu cek halaman Stok:
   stok harus bertambah 10.
8. **Barang Keluar** → keluarkan 5 unit → stok berkurang 5. Coba keluarkan melebihi stok →
   muncul pesan penolakan dan stok tidak berubah.
9. **Stok** → filter "Menipis"/"Habis", perhatikan badge dan kotak peringatan kuning.
10. **Laporan** → pilih rentang tanggal + kategori, klik **Cetak** dan **Export Excel**.
11. **Dashboard** → semua angka dan grafik harus ikut berubah setelah transaksi di atas.

## 23. Troubleshooting Error Umum

| Pesan / gejala | Penyebab & solusi |
|----------------|-------------------|
| `Koneksi database gagal: ... Unknown database 'db_inventaris'` | `database.sql` belum diimport. Ulangi langkah 19. |
| `Access denied for user 'root'@'localhost'` | Password MySQL tidak kosong. Isi `DB_PASS` di `config/database.php`. |
| Halaman putih kosong | Ada error PHP tersembunyi. Tambahkan sementara `ini_set('display_errors',1); error_reporting(E_ALL);` di atas `config/database.php`. |
| `Object not found! 404` | Folder tidak bernama `inventaris` atau tidak berada di `htdocs`. Sesuaikan URL-nya. |
| Port 80 dipakai (Apache gagal start) | Matikan Skype/IIS, atau ubah port Apache di `httpd.conf` menjadi 8080 lalu akses `http://localhost:8080/inventaris`. |
| MySQL gagal start | Port 3306 bentrok (ada MySQL lain terinstall) — matikan service MySQL bawaan Windows. |
| `Cannot add or update a child row: a foreign key constraint fails` | Data induk (kategori/pemasok/barang) belum ada. Buat dulu data induknya. |
| Tampilan berantakan / tanpa warna | Komputer sedang offline: Bootstrap & Chart.js diambil dari CDN. Sambungkan internet atau unduh filenya ke `assets/`. |
| Login selalu gagal padahal password benar | Password di tabel `users` diisi teks biasa. Password wajib hasil `password_hash()` — gunakan menu Pengguna untuk membuat akun baru. |
| Stok tidak berubah setelah transaksi | Tabel dibuat dengan MyISAM. Pastikan `ENGINE=InnoDB` seperti pada `database.sql`. |

---

## DARI NOL SAMPAI WEBSITE BERHASIL DIJALANKAN

1. **Unduh & install XAMPP** dari apachefriends.org, install di `C:\xampp`.
2. **Ekstrak** file ZIP project ini.
3. **Pindahkan** folder `inventaris` ke `C:\xampp\htdocs\`.
4. **Jalankan XAMPP Control Panel**, klik **Start** pada **Apache**, lalu **MySQL**.
5. Buka browser → `http://localhost/phpmyadmin`.
6. Klik **Import** → pilih file `C:\xampp\htdocs\inventaris\database.sql` → **Go**.
7. Pastikan muncul database **db_inventaris** dengan 6 tabel di panel kiri.
8. Buka `http://localhost/inventaris`.
9. Login dengan **admin / admin123**.
10. Anda akan masuk ke Dashboard berisi angka ringkasan dan grafik.
11. Coba menu **Barang Masuk** → pilih barang, isi jumlah 10 → Simpan.
12. Buka menu **Stok** → stok barang tersebut bertambah 10. Selesai, aplikasi berjalan!
13. (Opsional) Ganti password admin lewat menu **Pengguna → Edit**.
