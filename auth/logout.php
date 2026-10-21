<?php
/**
 * auth/logout.php
 * Menghapus semua session lalu kembali ke halaman login.
 */
session_start();
$_SESSION = [];
session_destroy();
session_start();
$_SESSION['flash'] = ['type' => 'success', 'message' => 'Anda telah keluar dari aplikasi.'];
header('Location: login.php');
exit;
