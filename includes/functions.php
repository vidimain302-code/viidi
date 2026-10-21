<?php
/**
 * includes/functions.php
 * Kumpulan fungsi bantuan yang dipakai di seluruh aplikasi.
 */

/** Sanitasi output agar aman dari XSS */
function e($text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

/** Simpan pesan notifikasi ke session (ditampilkan oleh header.php) */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Ambil sekaligus hapus pesan notifikasi */
function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/** Redirect ke halaman lain lalu hentikan script */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** URL dasar aplikasi, contoh: /inventaris */
function base_url(string $path = ''): string
{
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    // Naik satu level karena semua halaman berada di dalam subfolder
    $base = preg_replace('#/(config|includes|auth|dashboard|barang|kategori|pemasok|transaksi|stok|pengguna|laporan)$#', '', $base);
    return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
}

/** Membuat kode transaksi otomatis, contoh: TM-20260101-0007 */
function buat_kode_transaksi(PDO $pdo, string $tabel, string $prefix): string
{
    $stmt = $pdo->query("SELECT COUNT(*) AS jml FROM {$tabel}");
    $jumlah = (int) $stmt->fetch()['jml'] + 1;
    return $prefix . '-' . date('Ymd') . '-' . str_pad((string) $jumlah, 4, '0', STR_PAD_LEFT);
}

/** Status stok: habis / menipis / aman */
function status_stok(int $stok, int $stok_minimum): array
{
    if ($stok <= 0) {
        return ['Habis', 'danger'];
    }
    if ($stok <= $stok_minimum) {
        return ['Menipis', 'warning'];
    }
    return ['Aman', 'success'];
}

/** Format angka ke rupiah */
function rupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}
