<?php
/**
 * stok/index.php
 * Monitoring stok: indikator aman / menipis / habis + peringatan stok di bawah minimum.
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Stok Barang'; $menu_aktif = 'stok';

$status = $_GET['status'] ?? 'semua';
$cari   = trim($_GET['cari'] ?? '');

$sql = 'SELECT b.*, k.nama_kategori FROM barang b JOIN kategori k ON k.id_kategori=b.id_kategori WHERE 1=1';
$param = [];
if ($cari !== '') { $sql .= ' AND (b.nama_barang LIKE ? OR b.kode_barang LIKE ?)'; $param[] = "%$cari%"; $param[] = "%$cari%"; }
if ($status === 'aman')    $sql .= ' AND b.stok > b.stok_minimum';
if ($status === 'menipis') $sql .= ' AND b.stok > 0 AND b.stok <= b.stok_minimum';
if ($status === 'habis')   $sql .= ' AND b.stok <= 0';
$sql .= ' ORDER BY b.stok ASC';
$s = $pdo->prepare($sql); $s->execute($param); $daftar = $s->fetchAll();

$jml_peringatan = (int) $pdo->query('SELECT COUNT(*) FROM barang WHERE stok <= stok_minimum')->fetchColumn();
require_once __DIR__ . '/../includes/header.php';
?>
<?php if ($jml_peringatan > 0): ?>
  <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i>
    Peringatan: ada <strong><?= $jml_peringatan ?></strong> barang dengan stok berada di bawah atau sama dengan stok minimum. Segera lakukan pengadaan.</div>
<?php endif; ?>
<form class="page-actions" method="get">
  <select name="status" class="form-select" style="max-width:190px">
    <option value="semua"   <?= $status === 'semua'   ? 'selected' : '' ?>>Semua Status</option>
    <option value="aman"    <?= $status === 'aman'    ? 'selected' : '' ?>>Stok Aman</option>
    <option value="menipis" <?= $status === 'menipis' ? 'selected' : '' ?>>Stok Menipis</option>
    <option value="habis"   <?= $status === 'habis'   ? 'selected' : '' ?>>Stok Habis</option>
  </select>
  <input name="cari" class="form-control" style="max-width:260px" placeholder="Cari barang..." value="<?= e($cari) ?>">
  <button class="btn btn-teal"><i class="bi bi-funnel me-1"></i> Filter</button>
  <a href="index.php" class="btn btn-outline-secondary">Reset</a>
</form>
<div class="table-wrap">
  <table class="table-ims">
    <thead><tr><th>Kode</th><th>Nama Barang</th><th>Kategori</th><th>Stok</th><th>Stok Minimum</th><th>Kondisi</th><th>Lokasi</th><th>Status</th></tr></thead>
    <tbody>
    <?php if (!$daftar): ?><tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data.</td></tr><?php endif; ?>
    <?php foreach ($daftar as $b): [$label, $warna] = status_stok((int)$b['stok'], (int)$b['stok_minimum']); ?>
      <tr>
        <td><strong><?= e($b['kode_barang']) ?></strong></td>
        <td><?= e($b['nama_barang']) ?></td>
        <td><?= e($b['nama_kategori']) ?></td>
        <td><?= (int)$b['stok'] ?> <?= e($b['satuan']) ?></td>
        <td><?= (int)$b['stok_minimum'] ?></td>
        <td><?= e($b['kondisi']) ?></td>
        <td><?= e($b['lokasi'] ?: '-') ?></td>
        <td><span class="badge-soft badge-<?= $warna ?>"><?= $label ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
