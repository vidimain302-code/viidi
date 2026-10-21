<?php
/**
 * kategori/hapus.php — hapus kategori bila tidak dipakai barang.
 */
require_once __DIR__ . '/../includes/auth.php';
$id = (int) ($_GET['id'] ?? 0);
$cek = $pdo->prepare('SELECT COUNT(*) FROM barang WHERE id_kategori = ?');
$cek->execute([$id]);
if ($cek->fetchColumn() > 0) {
    set_flash('danger', 'Kategori masih dipakai oleh data barang, tidak bisa dihapus.');
} else {
    $pdo->prepare('DELETE FROM kategori WHERE id_kategori = ?')->execute([$id]);
    set_flash('success', 'Kategori berhasil dihapus.');
}
redirect('index.php');
