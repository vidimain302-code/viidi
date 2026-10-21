<?php
/**
 * index.php (root project)
 * Pintu masuk aplikasi. Jika sudah login -> dashboard, jika belum -> halaman login.
 */
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard/index.php');
} else {
    header('Location: auth/login.php');
}
exit;
