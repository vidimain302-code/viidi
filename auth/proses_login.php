<?php
/**
 * auth/proses_login.php
 * Memeriksa username & password ke tabel users memakai prepared statement
 * dan password_verify(). Jika cocok -> buat session -> dashboard.
 */
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$_SESSION['old_username'] = $username;

if ($username === '' || $password === '') {
    set_flash('danger', 'Username dan password wajib diisi.');
    redirect('login.php');
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    set_flash('danger', 'Username atau password salah.');
    redirect('login.php');
}

if ($user['status'] !== 'aktif') {
    set_flash('danger', 'Akun Anda dinonaktifkan. Hubungi administrator.');
    redirect('login.php');
}

session_regenerate_id(true);            // cegah session fixation
$_SESSION['user_id']      = $user['id_user'];
$_SESSION['username']     = $user['username'];
$_SESSION['nama_lengkap'] = $user['nama_lengkap'];
$_SESSION['role']         = $user['role'];
unset($_SESSION['old_username']);

set_flash('success', 'Selamat datang, ' . $user['nama_lengkap'] . '!');
redirect('../dashboard/index.php');
