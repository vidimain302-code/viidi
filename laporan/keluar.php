<?php
/**
 * laporan/keluar.php — laporan transaksi barang keluar dengan filter tanggal & kategori.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/export.php';
$judul = 'Laporan Barang Keluar'; $menu_aktif = 'laporan';

$kategori_id = (int) ($_GET['kategori'] ?? 0);
$mulai = $_GET['mulai'] ?? '';
$akhir = $_GET['akhir'] ?? '';

$sql = 'SELECT kl.*, b.kode_barang, b.nama_barang, k.nama_kategori, u.nama_lengkap
        FROM barang_keluar kl
        JOIN barang b ON b.id_barang=kl.id_barang
        JOIN kategori k ON k.id_kategori=b.id_kategori
        JOIN users u ON u.id_user=kl.id_user WHERE 1=1';
$param = [];
if ($kategori_id > 0) { $sql .= ' AND b.id_kategori = ?'; $param[] = $kategori_id; }
if ($mulai !== '')    { $sql .= ' AND kl.tanggal_keluar >= ?'; $param[] = $mulai; }
if ($akhir !== '')    { $sql .= ' AND kl.tanggal_keluar <= ?'; $param[] = $akhir; }
$sql .= ' ORDER BY kl.tanggal_keluar DESC';
$s = $pdo->prepare($sql); $s->execute($param); $data = $s->fetchAll();

if (($_GET['export'] ?? '') === 'excel') {
    $baris = []; $no = 1;
    foreach ($data as $m) {
        $baris[] = [$no++, $m['kode_transaksi'], $m['kode_barang'], $m['nama_barang'], $m['nama_kategori'],
                    $m['jumlah'], $m['tujuan'], $m['tanggal_keluar'], $m['nama_lengkap'], $m['keterangan']];
    }
    export_excel('laporan-barang-keluar', 'LAPORAN BARANG KELUAR',
        ['No','Kode Transaksi','Kode Barang','Nama Barang','Kategori','Jumlah','Tujuan','Tanggal','Petugas','Keterangan'], $baris);
}

$kategori = $pdo->query('SELECT * FROM kategori ORDER BY nama_kategori')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
echo tab_laporan('keluar');
?>
<form class="page-actions no-print" method="get">
  <select name="kategori" class="form-select" style="max-width:200px">
    <option value="0">Semua Kategori</option>
    <?php foreach ($kategori as $k): ?>
      <option value="<?= (int)$k['id_kategori'] ?>" <?= $kategori_id === (int)$k['id_kategori'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
    <?php endforeach; ?>
  </select>
  <input type="date" name="mulai" class="form-control" style="max-width:180px" value="<?= e($mulai) ?>">
  <input type="date" name="akhir" class="form-control" style="max-width:180px" value="<?= e($akhir) ?>">
  <button class="btn btn-teal"><i class="bi bi-funnel me-1"></i> Tampilkan</button>
  <a href="keluar.php" class="btn btn-outline-secondary">Reset</a>
</form>
<?= tombol_laporan('export=excel&kategori=' . $kategori_id . '&mulai=' . urlencode($mulai) . '&akhir=' . urlencode($akhir)) ?>

<h5 class="text-center d-none d-print-block">LAPORAN BARANG KELUAR</h5>
<div class="table-wrap">
  <table class="table-ims">
    <thead><tr><th>No</th><th>Kode Transaksi</th><th>Barang</th><th>Kategori</th><th>Jumlah</th><th>Tujuan</th><th>Tanggal</th><th>Petugas</th><th>Keterangan</th></tr></thead>
    <tbody>
    <?php if (!$data): ?><tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data.</td></tr><?php endif; ?>
    <?php $no = 1; $total = 0; foreach ($data as $m): $total += (int)$m['jumlah']; ?>
      <tr>
        <td><?= $no++ ?></td><td><?= e($m['kode_transaksi']) ?></td>
        <td><?= e($m['kode_barang']) ?> - <?= e($m['nama_barang']) ?></td>
        <td><?= e($m['nama_kategori']) ?></td><td><?= (int)$m['jumlah'] ?></td>
        <td><?= e($m['tujuan']) ?></td><td><?= e(date('d/m/Y', strtotime($m['tanggal_keluar']))) ?></td>
        <td><?= e($m['nama_lengkap']) ?></td><td><?= e($m['keterangan'] ?: '-') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><th colspan="4" class="text-end">Total Barang Keluar</th><th colspan="5"><?= $total ?> unit</th></tr></tfoot>
  </table>
</div>
<p class="text-muted small mt-2">Dicetak pada <?= date('d/m/Y H:i') ?> oleh <?= e($_SESSION['nama_lengkap']) ?>.</p>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
