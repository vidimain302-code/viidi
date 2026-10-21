<?php
/**
 * includes/auth.php
 * Penjaga halaman: memastikan user sudah login dan punya hak akses.
 * Selalu di-require paling atas pada setiap halaman yang butuh login.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

/** Wajib login */
function wajib_login(): void
{
    if (empty($_SESSION['user_id'])) {
        set_flash('warning', 'Silakan login terlebih dahulu.');
        redirect(base_url('auth/login.php'));
    }
}

/** true jika user yang login adalah admin */
function is_admin(): bool
{
    return (($_SESSION['role'] ?? '') === 'admin');
}

/** Halaman khusus admin */
function wajib_admin(): void
{
    wajib_login();
    if (!is_admin()) {
        set_flash('danger', 'Anda tidak memiliki hak akses ke halaman tersebut.');
        redirect(base_url('dashboard/index.php'));
    }
}

wajib_login();
