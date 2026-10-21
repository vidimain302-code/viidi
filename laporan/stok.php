<?php
/**
 * laporan/stok.php — laporan posisi stok per barang dan rekap per kategori.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/export.php';
$judul = 'Laporan Stok'; $menu_aktif = 'laporan';

$kategori_id = (int) ($_GET['kategori'] ?? 0);
$status = $_GET['status'] ?? 'semua';

$sql = 'SELECT b.*, k.nama_kategori FROM barang b JOIN kategori k ON k.id_kategori=b.id_kategori WHERE 1=1';
$param = [];
if ($kategori_id > 0) { $sql .= ' AND b.id_kategori = ?'; $param[] = $kategori_id; }
if ($status === 'aman')    $sql .= ' AND b.stok > b.stok_minimum';
if ($status === 'menipis') $sql .= ' AND b.stok > 0 AND b.stok <= b.stok_minimum';
if ($status === 'habis')   $sql .= ' AND b.stok <= 0';
$sql .= ' ORDER BY k.nama_kategori, b.nama_barang';
$s = $pdo->prepare($sql); $s->execute($param); $data = $s->fetchAll();

if (($_GET['export'] ?? '') === 'excel') {
    $baris = []; $no = 1;
    foreach ($data as $b) {
        [$label] = status_stok((int)$b['stok'], (int)$b['stok_minimum']);
        $baris[] = [$no++, $b['kode_barang'], $b['nama_barang'], $b['nama_kategori'], $b['stok'],
                    $b['stok_minimum'], $b['satuan'], $b['kondisi'], $b['lokasi'], $label];
    }
    export_excel('laporan-stok', 'LAPORAN STOK BARANG',
        ['No','Kode','Nama Barang','Kategori','Stok','Stok Min','Satuan','Kondisi','Lokasi','Status'], $baris);
}

$rekap = $pdo->query('SELECT k.nama_kategori, COUNT(b.id_barang) AS jml_barang, COALESCE(SUM(b.stok),0) AS total_stok
                      FROM kategori k LEFT JOIN barang b ON b.id_kategori=k.id_kategori
                      GROUP BY k.id_kategori ORDER BY k.nama_kategori')->fetchAll();
$kategori = $pdo->query('SELECT * FROM kategori ORDER BY nama_kategori')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
echo tab_laporan('stok');
?>
<form class="page-actions no-print" method="get">
  <select name="kategori" class="form-select" style="max-width:200px">
    <option value="0">Semua Kategori</option>
    <?php foreach ($kategori as $k): ?>
      <option value="<?= (int)$k['id_kategori'] ?>" <?= $kategori_id === (int)$k['id_kategori'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="status" class="form-select" style="max-width:180px">
    <option value="semua"   <?= $status === 'semua'   ? 'selected' : '' ?>>Semua Status</option>
    <option value="aman"    <?= $status === 'aman'    ? 'selected' : '' ?>>Aman</option>
    <option value="menipis" <?= $status === 'menipis' ? 'selected' : '' ?>>Menipis</option>
    <option value="habis"   <?= $status === 'habis'   ? 'selected' : '' ?>>Habis</option>
  </select>
  <button class="btn btn-teal"><i class="bi bi-funnel me-1"></i> Tampilkan</button>
  <a href="stok.php" class="btn btn-outline-secondary">Reset</a>
</form>
<?= tombol_laporan('export=excel&kategori=' . $kategori_id . '&status=' . urlencode($status)) ?>

<h5 class="text-center d-none d-print-block">LAPORAN STOK BARANG</h5>
<div class="table-wrap mb-3">
  <table class="table-ims">
    <thead><tr><th>No</th><th>Kode</th><th>Nama Barang</th><th>Kategori</th><th>Stok</th><th>Stok Min</th><th>Kondisi</th><th>Lokasi</th><th>Status</th></tr></thead>
    <tbody>
    <?php if (!$data): ?><tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data.</td></tr><?php endif; ?>
    <?php $no = 1; foreach ($data as $b): [$label, $warna] = status_stok((int)$b['stok'], (int)$b['stok_minimum']); ?>
      <tr>
        <td><?= $no++ ?></td><td><?= e($b['kode_barang']) ?></td><td><?= e($b['nama_barang']) ?></td>
        <td><?= e($b['nama_kategori']) ?></td><td><?= (int)$b['stok'] ?> <?= e($b['satuan']) ?></td>
        <td><?= (int)$b['stok_minimum'] ?></td><td><?= e($b['kondisi']) ?></td><td><?= e($b['lokasi'] ?: '-') ?></td>
        <td><span class="badge-soft badge-<?= $warna ?>"><?= $label ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="card-ims">
  <div class="card-title">Rekap Stok per Kategori</div>
  <table class="table-ims">
    <thead><tr><th>Kategori</th><th>Jumlah Jenis Barang</th><th>Total Stok</th></tr></thead>
    <tbody>
    <?php foreach ($rekap as $r): ?>
      <tr><td><?= e($r['nama_kategori']) ?></td><td><?= (int)$r['jml_barang'] ?></td><td><?= (int)$r['total_stok'] ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<p class="text-muted small mt-2">Dicetak pada <?= date('d/m/Y H:i') ?> oleh <?= e($_SESSION['nama_lengkap']) ?>.</p>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
