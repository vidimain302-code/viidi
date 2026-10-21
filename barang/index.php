<?php
/**
 * barang/index.php
 * Daftar barang: pencarian, filter kategori, pagination, dan tombol aksi
 * menuju tambah.php / edit.php / detail.php / hapus.php
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Data Barang';
$menu_aktif = 'barang';

$cari       = trim($_GET['cari'] ?? '');
$f_kategori = (int) ($_GET['kategori'] ?? 0);
$halaman    = max(1, (int) ($_GET['halaman'] ?? 1));
$per_hal    = 10;
$offset     = ($halaman - 1) * $per_hal;

$where = [];
$param = [];
if ($cari !== '') {
    $where[] = '(b.nama_barang LIKE ? OR b.kode_barang LIKE ? OR b.merek LIKE ?)';
    $param[] = "%$cari%"; $param[] = "%$cari%"; $param[] = "%$cari%";
}
if ($f_kategori > 0) { $where[] = 'b.id_kategori = ?'; $param[] = $f_kategori; }
$sql_where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM barang b $sql_where");
$stmt->execute($param);
$total_data = (int) $stmt->fetchColumn();
$total_hal  = max(1, (int) ceil($total_data / $per_hal));

$stmt = $pdo->prepare(
    "SELECT b.*, k.nama_kategori, p.nama_pemasok
     FROM barang b
     JOIN kategori k ON k.id_kategori = b.id_kategori
     LEFT JOIN pemasok p ON p.id_pemasok = b.id_pemasok
     $sql_where ORDER BY b.id_barang DESC LIMIT $per_hal OFFSET $offset"
);
$stmt->execute($param);
$daftar = $stmt->fetchAll();

$kategori = $pdo->query('SELECT * FROM kategori ORDER BY nama_kategori')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-actions">
  <a href="tambah.php" class="btn btn-teal"><i class="bi bi-plus-lg me-1"></i> Tambah Barang</a>
  <form class="d-flex gap-2 ms-auto flex-wrap" method="get">
    <input type="text" name="cari" class="form-control" placeholder="Cari kode / nama / merek..." value="<?= e($cari) ?>">
    <select name="kategori" class="form-select" style="min-width:170px">
      <option value="0">Semua Kategori</option>
      <?php foreach ($kategori as $k): ?>
        <option value="<?= (int)$k['id_kategori'] ?>" <?= $f_kategori === (int)$k['id_kategori'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
  </form>
</div>

<div class="table-wrap">
  <table class="table-ims">
    <thead>
      <tr><th>#</th><th>Kode</th><th>Nama Barang</th><th>Kategori</th><th>Merek</th><th>Stok</th><th>Harga Jual</th><th>Status</th><th>Aksi</th></tr>
    </thead>
    <tbody>
    <?php if (!$daftar): ?>
      <tr><td colspan="9" class="text-center text-muted py-4">Data barang tidak ditemukan.</td></tr>
    <?php else: $no = $offset + 1; foreach ($daftar as $b):
      [$label, $warna] = status_stok((int)$b['stok'], (int)$b['stok_minimum']); ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><strong><?= e($b['kode_barang']) ?></strong></td>
        <td><?= e($b['nama_barang']) ?></td>
        <td><?= e($b['nama_kategori']) ?></td>
        <td><?= e($b['merek'] ?: '-') ?></td>
        <td><?= (int)$b['stok'] ?> <?= e($b['satuan']) ?></td>
        <td><?= rupiah($b['harga_jual']) ?></td>
        <td><span class="badge-soft badge-<?= $warna ?>"><?= $label ?></span></td>
        <td class="text-nowrap">
          <a href="detail.php?id=<?= (int)$b['id_barang'] ?>" class="btn btn-sm btn-outline-secondary" title="Detail"><i class="bi bi-eye"></i></a>
          <a href="edit.php?id=<?= (int)$b['id_barang'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
          <a href="hapus.php?id=<?= (int)$b['id_barang'] ?>" class="btn btn-sm btn-outline-danger" title="Hapus"
             data-confirm="Hapus barang <?= e($b['nama_barang']) ?>? Riwayat transaksinya ikut terhapus."><i class="bi bi-trash"></i></a>
        </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php if ($total_hal > 1): ?>
<nav class="mt-3">
  <ul class="pagination justify-content-center">
    <?php for ($i = 1; $i <= $total_hal; $i++): ?>
      <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
        <a class="page-link" href="?halaman=<?= $i ?>&cari=<?= urlencode($cari) ?>&kategori=<?= $f_kategori ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>
<p class="text-muted small">Total <?= $total_data ?> barang.</p>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
