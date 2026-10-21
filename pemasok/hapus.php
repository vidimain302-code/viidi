<?php
/**
 * pemasok/hapus.php — hapus pemasok (relasi barang otomatis jadi NULL).
 */
require_once __DIR__ . '/../includes/auth.php';
$id = (int) ($_GET['id'] ?? 0);
try {
    $pdo->prepare('DELETE FROM pemasok WHERE id_pemasok = ?')->execute([$id]);
    set_flash('success', 'Pemasok berhasil dihapus.');
} catch (PDOException $ex) {
    set_flash('danger', 'Pemasok tidak dapat dihapus karena masih terhubung data lain.');
}
redirect('index.php');
