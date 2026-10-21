<?php
/**
 * transaksi/riwayat.php
 * Gabungan riwayat barang masuk & keluar dengan filter jenis dan rentang tanggal.
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Riwayat Transaksi'; $menu_aktif = 'riwayat';

$jenis = $_GET['jenis'] ?? 'semua';
$mulai = $_GET['mulai'] ?? '';
$akhir = $_GET['akhir'] ?? '';

$rows = [];
$filterTanggal = function (string $kolom) use ($mulai, $akhir, &$param) {
    $sql = '';
    if ($mulai !== '') { $sql .= " AND $kolom >= ?"; $param[] = $mulai; }
    if ($akhir !== '') { $sql .= " AND $kolom <= ?"; $param[] = $akhir; }
    return $sql;
};

if ($jenis === 'semua' || $jenis === 'masuk') {
    $param = [];
    $sql = "SELECT 'Masuk' AS jenis, m.kode_transaksi, b.kode_barang, b.nama_barang, m.jumlah,
                   COALESCE(p.nama_pemasok,'-') AS pihak, m.tanggal_masuk AS tanggal, m.keterangan, u.nama_lengkap
            FROM barang_masuk m JOIN barang b ON b.id_barang=m.id_barang
            LEFT JOIN pemasok p ON p.id_pemasok=m.id_pemasok JOIN users u ON u.id_user=m.id_user
            WHERE 1=1" . $filterTanggal('m.tanggal_masuk');
    $s = $pdo->prepare($sql); $s->execute($param);
    $rows = array_merge($rows, $s->fetchAll());
}
if ($jenis === 'semua' || $jenis === 'keluar') {
    $param = [];
    $sql = "SELECT 'Keluar' AS jenis, k.kode_transaksi, b.kode_barang, b.nama_barang, k.jumlah,
                   k.tujuan AS pihak, k.tanggal_keluar AS tanggal, k.keterangan, u.nama_lengkap
            FROM barang_keluar k JOIN barang b ON b.id_barang=k.id_barang JOIN users u ON u.id_user=k.id_user
            WHERE 1=1" . $filterTanggal('k.tanggal_keluar');
    $s = $pdo->prepare($sql); $s->execute($param);
    $rows = array_merge($rows, $s->fetchAll());
}
usort($rows, fn($a, $b) => strcmp($b['tanggal'], $a['tanggal']));
require_once __DIR__ . '/../includes/header.php';
?>
<form class="page-actions" method="get">
  <select name="jenis" class="form-select" style="max-width:180px">
    <option value="semua"  <?= $jenis === 'semua'  ? 'selected' : '' ?>>Semua Transaksi</option>
    <option value="masuk"  <?= $jenis === 'masuk'  ? 'selected' : '' ?>>Barang Masuk</option>
    <option value="keluar" <?= $jenis === 'keluar' ? 'selected' : '' ?>>Barang Keluar</option>
  </select>
  <input type="date" name="mulai" class="form-control" style="max-width:180px" value="<?= e($mulai) ?>">
  <input type="date" name="akhir" class="form-control" style="max-width:180px" value="<?= e($akhir) ?>">
  <button class="btn btn-teal"><i class="bi bi-funnel me-1"></i> Filter</button>
  <a href="riwayat.php" class="btn btn-outline-secondary">Reset</a>
</form>
<div class="table-wrap">
  <table class="table-ims">
    <thead><tr><th>Jenis</th><th>Kode</th><th>Barang</th><th>Jumlah</th><th>Pemasok / Tujuan</th><th>Tanggal</th><th>Petugas</th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="7" class="text-center text-muted py-4">Tidak ada transaksi pada filter ini.</td></tr><?php endif; ?>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><span class="badge-soft <?= $r['jenis'] === 'Masuk' ? 'badge-success' : 'badge-warning' ?>"><?= e($r['jenis']) ?></span></td>
        <td><?= e($r['kode_transaksi']) ?></td>
        <td><?= e($r['kode_barang']) ?> - <?= e($r['nama_barang']) ?></td>
        <td><?= (int)$r['jumlah'] ?></td>
        <td><?= e($r['pihak']) ?></td>
        <td><?= e(date('d/m/Y', strtotime($r['tanggal']))) ?></td>
        <td><?= e($r['nama_lengkap']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
