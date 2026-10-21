<?php
/**
 * transaksi/keluar.php
 * Form barang keluar + daftar transaksi keluar.
 * Saat disimpan: INSERT ke barang_keluar DAN stok barang otomatis BERKURANG.
 * Jumlah keluar TIDAK BOLEH melebihi stok yang tersedia.
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Barang Keluar'; $menu_aktif = 'keluar'; $error = [];

$barang = $pdo->query('SELECT id_barang, kode_barang, nama_barang, stok, satuan FROM barang ORDER BY nama_barang')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_barang = (int) ($_POST['id_barang'] ?? 0);
    $jumlah    = (int) ($_POST['jumlah'] ?? 0);
    $tujuan    = trim($_POST['tujuan'] ?? '');
    $tanggal   = $_POST['tanggal_keluar'] ?? date('Y-m-d');
    $ket       = trim($_POST['keterangan'] ?? '');

    if ($id_barang <= 0) $error[] = 'Barang wajib dipilih.';
    if ($jumlah <= 0)    $error[] = 'Jumlah harus lebih dari 0.';
    if ($tujuan === '')  $error[] = 'Tujuan wajib diisi.';

    if (!$error) {
        $s = $pdo->prepare('SELECT stok, nama_barang FROM barang WHERE id_barang = ?');
        $s->execute([$id_barang]);
        $brg = $s->fetch();
        if (!$brg) {
            $error[] = 'Barang tidak ditemukan.';
        } elseif ($jumlah > (int) $brg['stok']) {
            $error[] = 'Jumlah keluar (' . $jumlah . ') melebihi stok tersedia (' . (int) $brg['stok'] . ') untuk ' . $brg['nama_barang'] . '.';
        }
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();
            $kode = buat_kode_transaksi($pdo, 'barang_keluar', 'TK');
            $pdo->prepare('INSERT INTO barang_keluar (kode_transaksi,id_barang,jumlah,tujuan,tanggal_keluar,keterangan,id_user)
                           VALUES (?,?,?,?,?,?,?)')
                ->execute([$kode, $id_barang, $jumlah, $tujuan, $tanggal, $ket, $_SESSION['user_id']]);
            // STOK OTOMATIS BERKURANG (aman dari stok minus)
            $upd = $pdo->prepare('UPDATE barang SET stok = stok - ? WHERE id_barang = ? AND stok >= ?');
            $upd->execute([$jumlah, $id_barang, $jumlah]);
            if ($upd->rowCount() === 0) {
                throw new PDOException('Stok tidak mencukupi.');
            }
            $pdo->commit();
            set_flash('success', "Transaksi $kode tersimpan. Stok barang berkurang $jumlah.");
            redirect('keluar.php');
        } catch (PDOException $ex) {
            $pdo->rollBack();
            $error[] = 'Gagal menyimpan transaksi: ' . $ex->getMessage();
        }
    }
}

$daftar = $pdo->query(
    'SELECT k.*, b.kode_barang, b.nama_barang, u.nama_lengkap
     FROM barang_keluar k JOIN barang b ON b.id_barang=k.id_barang JOIN users u ON u.id_user=k.id_user
     ORDER BY k.id_keluar DESC LIMIT 25'
)->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-3">
  <div class="col-xl-4">
    <div class="card-ims">
      <div class="card-title"><i class="bi bi-box-arrow-up me-1"></i> Form Barang Keluar</div>
      <?php foreach ($error as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
      <form method="post" class="needs-validation" novalidate>
        <div class="mb-3"><label class="form-label">Barang *</label>
          <select name="id_barang" id="pilihBarang" class="form-select" required>
            <option value="">-- Pilih Barang --</option>
            <?php foreach ($barang as $b): ?>
              <option value="<?= (int)$b['id_barang'] ?>" data-stok="<?= (int)$b['stok'] ?>">
                <?= e($b['kode_barang'] . ' - ' . $b['nama_barang']) ?> (stok <?= (int)$b['stok'] ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <div class="form-text" id="infoStok">Stok tersedia akan tampil setelah barang dipilih.</div>
        </div>
        <div class="mb-3"><label class="form-label">Jumlah *</label>
          <input type="number" min="1" name="jumlah" id="jumlahKeluar" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Tujuan *</label>
          <input name="tujuan" class="form-control" required placeholder="Contoh: Lab RPL 1"></div>
        <div class="mb-3"><label class="form-label">Tanggal Keluar *</label>
          <input type="date" name="tanggal_keluar" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
        <div class="mb-3"><label class="form-label">Keterangan</label>
          <textarea name="keterangan" rows="2" class="form-control"></textarea></div>
        <div class="mb-3 small text-muted">Petugas: <strong><?= e($_SESSION['nama_lengkap']) ?></strong></div>
        <button class="btn btn-teal w-100"><i class="bi bi-save me-1"></i> Simpan Transaksi</button>
      </form>
    </div>
  </div>
  <div class="col-xl-8">
    <div class="table-wrap">
      <table class="table-ims">
        <thead><tr><th>Kode</th><th>Barang</th><th>Jumlah</th><th>Tujuan</th><th>Tanggal</th><th>Petugas</th></tr></thead>
        <tbody>
        <?php if (!$daftar): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada transaksi.</td></tr><?php endif; ?>
        <?php foreach ($daftar as $k): ?>
          <tr>
            <td><strong><?= e($k['kode_transaksi']) ?></strong></td>
            <td><?= e($k['kode_barang']) ?> - <?= e($k['nama_barang']) ?></td>
            <td><span class="badge-soft badge-warning">-<?= (int)$k['jumlah'] ?></span></td>
            <td><?= e($k['tujuan']) ?></td>
            <td><?= e(date('d/m/Y', strtotime($k['tanggal_keluar']))) ?></td>
            <td><?= e($k['nama_lengkap']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
// Batasi input jumlah sesuai stok yang tersedia (validasi tambahan di sisi browser)
const pilih = document.getElementById('pilihBarang');
const jumlah = document.getElementById('jumlahKeluar');
const info = document.getElementById('infoStok');
pilih.addEventListener('change', function () {
  const stok = parseInt(this.selectedOptions[0].dataset.stok || '0', 10);
  jumlah.max = stok;
  info.textContent = 'Stok tersedia: ' + stok;
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
