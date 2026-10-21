<?php
/**
 * pemasok/index.php — daftar pemasok + pencarian.
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Data Pemasok'; $menu_aktif = 'pemasok';

$cari = trim($_GET['cari'] ?? '');
$sql = 'SELECT * FROM pemasok'; $param = [];
if ($cari !== '') { $sql .= ' WHERE nama_pemasok LIKE ? OR nama_perusahaan LIKE ?'; $param = ["%$cari%", "%$cari%"]; }
$sql .= ' ORDER BY nama_pemasok';
$stmt = $pdo->prepare($sql); $stmt->execute($param); $daftar = $stmt->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-actions">
  <a href="tambah.php" class="btn btn-teal"><i class="bi bi-plus-lg me-1"></i> Tambah Pemasok</a>
  <form class="d-flex gap-2 ms-auto" method="get">
    <input name="cari" class="form-control" placeholder="Cari pemasok..." value="<?= e($cari) ?>">
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
  </form>
</div>
<div class="table-wrap">
  <table class="table-ims">
    <thead><tr><th>#</th><th>Nama Pemasok</th><th>Perusahaan</th><th>Telepon</th><th>Email</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php if (!$daftar): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada pemasok.</td></tr><?php endif; ?>
    <?php $no = 1; foreach ($daftar as $p): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><strong><?= e($p['nama_pemasok']) ?></strong></td>
        <td><?= e($p['nama_perusahaan'] ?: '-') ?></td>
        <td><?= e($p['telepon'] ?: '-') ?></td>
        <td><?= e($p['email'] ?: '-') ?></td>
        <td class="text-nowrap">
          <a href="detail.php?id=<?= (int)$p['id_pemasok'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
          <a href="edit.php?id=<?= (int)$p['id_pemasok'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
          <a href="hapus.php?id=<?= (int)$p['id_pemasok'] ?>" class="btn btn-sm btn-outline-danger"
             data-confirm="Hapus pemasok <?= e($p['nama_pemasok']) ?>?"><i class="bi bi-trash"></i></a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
