<?php
/**
 * includes/header.php
 * Bagian atas semua halaman: <head>, sidebar, navbar, dan alert notifikasi.
 * Variabel $judul dan $menu_aktif diisi oleh halaman yang memanggilnya.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';

$judul      = $judul ?? 'Dashboard';
$menu_aktif = $menu_aktif ?? '';
$flash      = get_flash();

$menu = [
    ['key' => 'dashboard', 'label' => 'Dashboard',    'url' => 'dashboard/index.php',  'icon' => 'bi-speedometer2', 'admin' => false],
    ['key' => 'barang',    'label' => 'Data Barang',  'url' => 'barang/index.php',     'icon' => 'bi-box-seam',     'admin' => false],
    ['key' => 'kategori',  'label' => 'Kategori',     'url' => 'kategori/index.php',   'icon' => 'bi-tags',         'admin' => false],
    ['key' => 'pemasok',   'label' => 'Pemasok',      'url' => 'pemasok/index.php',    'icon' => 'bi-truck',        'admin' => false],
    ['key' => 'masuk',     'label' => 'Barang Masuk', 'url' => 'transaksi/masuk.php',  'icon' => 'bi-box-arrow-in-down', 'admin' => false],
    ['key' => 'keluar',    'label' => 'Barang Keluar','url' => 'transaksi/keluar.php', 'icon' => 'bi-box-arrow-up', 'admin' => false],
    ['key' => 'riwayat',   'label' => 'Riwayat',      'url' => 'transaksi/riwayat.php','icon' => 'bi-clock-history','admin' => false],
    ['key' => 'stok',      'label' => 'Stok Barang',  'url' => 'stok/index.php',       'icon' => 'bi-bar-chart-line','admin' => false],
    ['key' => 'laporan',   'label' => 'Laporan',      'url' => 'laporan/barang.php',   'icon' => 'bi-file-earmark-text', 'admin' => false],
    ['key' => 'pengguna',  'label' => 'Pengguna',     'url' => 'pengguna/index.php',   'icon' => 'bi-people',       'admin' => true],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul) ?> &middot; Inventory Management System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= e(base_url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>
<div class="app-wrapper">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <i class="bi bi-boxes"></i>
      <div>
        <span class="brand-title">INVENTORY</span>
        <small class="brand-sub">Management System</small>
      </div>
    </div>
    <nav class="sidebar-menu">
      <?php foreach ($menu as $m): ?>
        <?php if ($m['admin'] && !is_admin()) continue; ?>
        <a class="menu-item <?= $menu_aktif === $m['key'] ? 'active' : '' ?>" href="<?= e(base_url($m['url'])) ?>">
          <i class="bi <?= e($m['icon']) ?>"></i><span><?= e($m['label']) ?></span>
        </a>
      <?php endforeach; ?>
      <a class="menu-item menu-logout" href="<?= e(base_url('auth/logout.php')) ?>" data-confirm="Yakin ingin keluar dari aplikasi?">
        <i class="bi bi-box-arrow-right"></i><span>Logout</span>
      </a>
    </nav>
  </aside>

  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <div class="main-area">
    <header class="topbar">
      <button class="btn btn-light btn-sm d-lg-none" id="toggleSidebar"><i class="bi bi-list"></i></button>
      <h1 class="topbar-title"><?= e($judul) ?></h1>
      <div class="topbar-user">
        <div class="text-end d-none d-sm-block">
          <div class="user-name"><?= e($_SESSION['nama_lengkap'] ?? 'Pengguna') ?></div>
          <div class="user-role"><?= e(ucfirst($_SESSION['role'] ?? '')) ?></div>
        </div>
        <div class="avatar"><?= e(strtoupper(substr($_SESSION['nama_lengkap'] ?? 'U', 0, 1))) ?></div>
      </div>
    </header>

    <main class="content">
      <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
          <?= e($flash['message']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
