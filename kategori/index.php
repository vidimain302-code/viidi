<?php
/**
 * kategori/index.php — daftar kategori + pencarian.
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Kategori Barang';
$menu_aktif = 'kategori';

$cari = trim($_GET['cari'] ?? '');
$sql  = 'SELECT k.*, (SELECT COUNT(*) FROM barang b WHERE b.id_kategori=k.id_kategori) AS jumlah_barang
         FROM kategori k';
$param = [];
if ($cari !== '') { $sql .= ' WHERE k.nama_kategori LIKE ?'; $param[] = "%$cari%"; }
$sql .= ' ORDER BY k.nama_kategori';
$stmt = $pdo->prepare($sql); $stmt->execute($param); $daftar = $stmt->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-actions">
  <a href="tambah.php" class="btn btn-teal"><i class="bi bi-plus-lg me-1"></i> Tambah Kategori</a>
  <form class="d-flex gap-2 ms-auto" method="get">
    <input name="cari" class="form-control" placeholder="Cari kategori..." value="<?= e($cari) ?>">
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
  </form>
</div>
<div class="table-wrap">
  <table class="table-ims">
    <thead><tr><th>#</th><th>Nama Kategori</th><th>Deskripsi</th><th>Jumlah Barang</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php if (!$daftar): ?><tr><td colspan="5" class="text-center text-muted py-4">Belum ada kategori.</td></tr><?php endif; ?>
    <?php $no = 1; foreach ($daftar as $k): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><strong><?= e($k['nama_kategori']) ?></strong></td>
        <td><?= e($k['deskripsi'] ?: '-') ?></td>
        <td><span class="badge-soft badge-info"><?= (int)$k['jumlah_barang'] ?> barang</span></td>
        <td class="text-nowrap">
          <a href="edit.php?id=<?= (int)$k['id_kategori'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
          <a href="hapus.php?id=<?= (int)$k['id_kategori'] ?>" class="btn btn-sm btn-outline-danger"
             data-confirm="Hapus kategori <?= e($k['nama_kategori']) ?>?"><i class="bi bi-trash"></i></a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
