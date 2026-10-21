<?php
/**
 * barang/hapus.php
 * Menghapus satu barang berdasarkan id (dipanggil dari tombol hapus di index.php).
 */
require_once __DIR__ . '/../includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        $pdo->prepare('DELETE FROM barang WHERE id_barang = ?')->execute([$id]);
        set_flash('success', 'Barang berhasil dihapus.');
    } catch (PDOException $ex) {
        set_flash('danger', 'Barang tidak dapat dihapus karena masih dipakai data lain.');
    }
} else {
    set_flash('danger', 'ID barang tidak valid.');
}
redirect('index.php');
