<?php
/**
 * config/database.php
 * Koneksi database menggunakan PDO + prepared statement.
 * Dipakai oleh hampir semua file lain lewat: require_once '../config/database.php';
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'db_inventaris');
define('DB_USER', 'root');
define('DB_PASS', '');   // XAMPP default: kosong

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
}
