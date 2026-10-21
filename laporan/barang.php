<?php
/**
 * laporan/barang.php
 * Laporan seluruh barang dengan filter kategori & rentang tanggal masuk.
 * Tersedia tombol Cetak, Export Excel, dan Export PDF (lewat jendela cetak).
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/export.php';
$judul = 'Laporan Barang'; $menu_aktif = 'laporan';

$kategori_id = (int) ($_GET['kategori'] ?? 0);
$mulai = $_GET['mulai'] ?? '';
$akhir = $_GET['akhir'] ?? '';

$sql = 'SELECT b.*, k.nama_kategori, COALESCE(p.nama_pemasok,"-") AS nama_pemasok
        FROM barang b JOIN kategori k ON k.id_kategori=b.id_kategori
        LEFT JOIN pemasok p ON p.id_pemasok=b.id_pemasok WHERE 1=1';
$param = [];
if ($kategori_id > 0) { $sql .= ' AND b.id_kategori = ?'; $param[] = $kategori_id; }
if ($mulai !== '')    { $sql .= ' AND b.tanggal_masuk >= ?'; $param[] = $mulai; }
if ($akhir !== '')    { $sql .= ' AND b.tanggal_masuk <= ?'; $param[] = $akhir; }
$sql .= ' ORDER BY k.nama_kategori, b.nama_barang';
$s = $pdo->prepare($sql); $s->execute($param); $data = $s->fetchAll();

if (($_GET['export'] ?? '') === 'excel') {
    $baris = [];
    $no = 1;
    foreach ($data as $b) {
        $baris[] = [$no++, $b['kode_barang'], $b['nama_barang'], $b['nama_kategori'], $b['merek'],
                    $b['satuan'], $b['stok'], $b['stok_minimum'], $b['harga_beli'], $b['harga_jual'],
                    $b['kondisi'], $b['lokasi'], $b['nama_pemasok'], $b['tanggal_masuk']];
    }
    export_excel('laporan-barang', 'LAPORAN SELURUH BARANG',
        ['No','Kode','Nama Barang','Kategori','Merek','Satuan','Stok','Stok Min','Harga Beli','Harga Jual','Kondisi','Lokasi','Pemasok','Tanggal Masuk'], $baris);
}

$kategori = $pdo->query('SELECT * FROM kategori ORDER BY nama_kategori')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
echo tab_laporan('barang');
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
  <a href="barang.php" class="btn btn-outline-secondary">Reset</a>
</form>
<?= tombol_laporan('export=excel&kategori=' . $kategori_id . '&mulai=' . urlencode($mulai) . '&akhir=' . urlencode($akhir)) ?>

<h5 class="text-center d-none d-print-block">LAPORAN SELURUH BARANG</h5>
<div class="table-wrap">
  <table class="table-ims">
    <thead><tr><th>No</th><th>Kode</th><th>Nama Barang</th><th>Kategori</th><th>Merek</th><th>Stok</th><th>Harga Beli</th><th>Harga Jual</th><th>Kondisi</th><th>Pemasok</th><th>Tgl Masuk</th></tr></thead>
    <tbody>
    <?php if (!$data): ?><tr><td colspan="11" class="text-center text-muted py-4">Tidak ada data.</td></tr><?php endif; ?>
    <?php $no = 1; $nilai = 0; foreach ($data as $b): $nilai += $b['stok'] * $b['harga_beli']; ?>
      <tr>
        <td><?= $no++ ?></td><td><?= e($b['kode_barang']) ?></td><td><?= e($b['nama_barang']) ?></td>
        <td><?= e($b['nama_kategori']) ?></td><td><?= e($b['merek'] ?: '-') ?></td>
        <td><?= (int)$b['stok'] ?> <?= e($b['satuan']) ?></td>
        <td><?= rupiah($b['harga_beli']) ?></td><td><?= rupiah($b['harga_jual']) ?></td>
        <td><?= e($b['kondisi']) ?></td><td><?= e($b['nama_pemasok']) ?></td>
        <td><?= e(date('d/m/Y', strtotime($b['tanggal_masuk']))) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><th colspan="6" class="text-end">Total Nilai Persediaan (harga beli)</th><th colspan="5"><?= rupiah($nilai) ?></th></tr></tfoot>
  </table>
</div>
<p class="text-muted small mt-2">Dicetak pada <?= date('d/m/Y H:i') ?> oleh <?= e($_SESSION['nama_lengkap']) ?>.</p>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
