<?php
/**
 * pengguna/hapus.php — hapus pengguna (tidak boleh menghapus diri sendiri).
 */
require_once __DIR__ . '/../includes/auth.php';
wajib_admin();
$id = (int) ($_GET['id'] ?? 0);
if ($id === (int) $_SESSION['user_id']) {
    set_flash('danger', 'Anda tidak dapat menghapus akun sendiri.');
} else {
    try {
        $pdo->prepare('DELETE FROM users WHERE id_user = ?')->execute([$id]);
        set_flash('success', 'Pengguna berhasil dihapus.');
    } catch (PDOException $ex) {
        set_flash('danger', 'Pengguna tidak dapat dihapus karena masih memiliki data transaksi.');
    }
}
redirect('index.php');
