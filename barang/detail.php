<?php
/**
 * barang/detail.php
 * Menampilkan detail satu barang beserta riwayat masuk & keluarnya.
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Detail Barang';
$menu_aktif = 'barang';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT b.*, k.nama_kategori, p.nama_pemasok, p.nama_perusahaan
     FROM barang b JOIN kategori k ON k.id_kategori=b.id_kategori
     LEFT JOIN pemasok p ON p.id_pemasok=b.id_pemasok WHERE b.id_barang = ?'
);
$stmt->execute([$id]);
$b = $stmt->fetch();
if (!$b) { set_flash('danger', 'Data barang tidak ditemukan.'); redirect('index.php'); }

$masuk = $pdo->prepare('SELECT m.*, u.nama_lengkap FROM barang_masuk m JOIN users u ON u.id_user=m.id_user WHERE m.id_barang=? ORDER BY m.tanggal_masuk DESC');
$masuk->execute([$id]);
$masuk = $masuk->fetchAll();

$keluar = $pdo->prepare('SELECT k.*, u.nama_lengkap FROM barang_keluar k JOIN users u ON u.id_user=k.id_user WHERE k.id_barang=? ORDER BY k.tanggal_keluar DESC');
$keluar->execute([$id]);
$keluar = $keluar->fetchAll();

[$label, $warna] = status_stok((int)$b['stok'], (int)$b['stok_minimum']);
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-actions">
  <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
  <a href="edit.php?id=<?= (int)$b['id_barang'] ?>" class="btn btn-teal"><i class="bi bi-pencil me-1"></i> Edit</a>
</div>
<div class="row g-3">
  <div class="col-lg-6">
    <div class="card-ims">
      <div class="card-title"><?= e($b['nama_barang']) ?> <span class="badge-soft badge-<?= $warna ?>"><?= $label ?></span></div>
      <table class="table-ims">
        <tbody>
          <tr><td>Kode Barang</td><td><strong><?= e($b['kode_barang']) ?></strong></td></tr>
          <tr><td>Kategori</td><td><?= e($b['nama_kategori']) ?></td></tr>
          <tr><td>Merek</td><td><?= e($b['merek'] ?: '-') ?></td></tr>
          <tr><td>Spesifikasi</td><td><?= nl2br(e($b['spesifikasi'] ?: '-')) ?></td></tr>
          <tr><td>Satuan</td><td><?= e($b['satuan']) ?></td></tr>
          <tr><td>Harga Beli</td><td><?= rupiah($b['harga_beli']) ?></td></tr>
          <tr><td>Harga Jual</td><td><?= rupiah($b['harga_jual']) ?></td></tr>
          <tr><td>Stok / Minimum</td><td><?= (int)$b['stok'] ?> / <?= (int)$b['stok_minimum'] ?></td></tr>
          <tr><td>Kondisi</td><td><?= e($b['kondisi']) ?></td></tr>
          <tr><td>Lokasi</td><td><?= e($b['lokasi'] ?: '-') ?></td></tr>
          <tr><td>Tanggal Masuk</td><td><?= e(date('d/m/Y', strtotime($b['tanggal_masuk']))) ?></td></tr>
          <tr><td>Pemasok</td><td><?= e($b['nama_pemasok'] ? $b['nama_pemasok'] . ' (' . $b['nama_perusahaan'] . ')' : '-') ?></td></tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card-ims mb-3">
      <div class="card-title text-success"><i class="bi bi-box-arrow-in-down me-1"></i> Riwayat Barang Masuk</div>
      <table class="table-ims">
        <thead><tr><th>Kode</th><th>Jumlah</th><th>Tanggal</th><th>Petugas</th></tr></thead>
        <tbody>
        <?php if (!$masuk): ?><tr><td colspan="4" class="text-muted text-center">Belum ada.</td></tr><?php endif; ?>
        <?php foreach ($masuk as $m): ?>
          <tr><td><?= e($m['kode_transaksi']) ?></td><td><?= (int)$m['jumlah'] ?></td>
              <td><?= e(date('d/m/Y', strtotime($m['tanggal_masuk']))) ?></td><td><?= e($m['nama_lengkap']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="card-ims">
      <div class="card-title text-warning"><i class="bi bi-box-arrow-up me-1"></i> Riwayat Barang Keluar</div>
      <table class="table-ims">
        <thead><tr><th>Kode</th><th>Jumlah</th><th>Tujuan</th><th>Tanggal</th></tr></thead>
        <tbody>
        <?php if (!$keluar): ?><tr><td colspan="4" class="text-muted text-center">Belum ada.</td></tr><?php endif; ?>
        <?php foreach ($keluar as $k): ?>
          <tr><td><?= e($k['kode_transaksi']) ?></td><td><?= (int)$k['jumlah'] ?></td>
              <td><?= e($k['tujuan']) ?></td><td><?= e(date('d/m/Y', strtotime($k['tanggal_keluar']))) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
