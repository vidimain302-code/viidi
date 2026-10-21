<?php
/**
 * pemasok/detail.php — detail pemasok + daftar barang yang dipasok.
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Detail Pemasok'; $menu_aktif = 'pemasok';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM pemasok WHERE id_pemasok = ?'); $stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { set_flash('danger', 'Pemasok tidak ditemukan.'); redirect('index.php'); }

$brg = $pdo->prepare('SELECT kode_barang, nama_barang, stok, satuan FROM barang WHERE id_pemasok = ? ORDER BY nama_barang');
$brg->execute([$id]); $brg = $brg->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-actions"><a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a></div>
<div class="row g-3">
  <div class="col-lg-5"><div class="card-ims">
    <div class="card-title"><?= e($p['nama_pemasok']) ?></div>
    <table class="table-ims"><tbody>
      <tr><td>Perusahaan</td><td><?= e($p['nama_perusahaan'] ?: '-') ?></td></tr>
      <tr><td>Telepon</td><td><?= e($p['telepon'] ?: '-') ?></td></tr>
      <tr><td>Email</td><td><?= e($p['email'] ?: '-') ?></td></tr>
      <tr><td>Alamat</td><td><?= nl2br(e($p['alamat'] ?: '-')) ?></td></tr>
    </tbody></table>
  </div></div>
  <div class="col-lg-7"><div class="card-ims">
    <div class="card-title">Barang dari Pemasok Ini</div>
    <table class="table-ims">
      <thead><tr><th>Kode</th><th>Nama Barang</th><th>Stok</th></tr></thead>
      <tbody>
      <?php if (!$brg): ?><tr><td colspan="3" class="text-muted text-center">Belum ada barang.</td></tr><?php endif; ?>
      <?php foreach ($brg as $b): ?>
        <tr><td><?= e($b['kode_barang']) ?></td><td><?= e($b['nama_barang']) ?></td><td><?= (int)$b['stok'] ?> <?= e($b['satuan']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
