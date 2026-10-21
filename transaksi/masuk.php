<?php
/**
 * transaksi/masuk.php
 * Form barang masuk + daftar transaksi masuk.
 * Saat disimpan: INSERT ke barang_masuk DAN stok barang otomatis BERTAMBAH
 * (dibungkus transaction agar keduanya konsisten).
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Barang Masuk'; $menu_aktif = 'masuk'; $error = [];

$barang  = $pdo->query('SELECT id_barang, kode_barang, nama_barang, satuan, stok FROM barang ORDER BY nama_barang')->fetchAll();
$pemasok = $pdo->query('SELECT id_pemasok, nama_pemasok, nama_perusahaan FROM pemasok ORDER BY nama_pemasok')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_barang  = (int) ($_POST['id_barang'] ?? 0);
    $id_pemasok = ($_POST['id_pemasok'] ?? '') !== '' ? (int) $_POST['id_pemasok'] : null;
    $jumlah     = (int) ($_POST['jumlah'] ?? 0);
    $tanggal    = $_POST['tanggal_masuk'] ?? date('Y-m-d');
    $ket        = trim($_POST['keterangan'] ?? '');

    if ($id_barang <= 0) $error[] = 'Barang wajib dipilih.';
    if ($jumlah <= 0)    $error[] = 'Jumlah harus lebih dari 0.';

    if (!$error) {
        try {
            $pdo->beginTransaction();
            $kode = buat_kode_transaksi($pdo, 'barang_masuk', 'TM');
            $pdo->prepare('INSERT INTO barang_masuk (kode_transaksi,id_barang,id_pemasok,jumlah,tanggal_masuk,keterangan,id_user)
                           VALUES (?,?,?,?,?,?,?)')
                ->execute([$kode, $id_barang, $id_pemasok, $jumlah, $tanggal, $ket, $_SESSION['user_id']]);
            // STOK OTOMATIS BERTAMBAH
            $pdo->prepare('UPDATE barang SET stok = stok + ? WHERE id_barang = ?')->execute([$jumlah, $id_barang]);
            $pdo->commit();
            set_flash('success', "Transaksi $kode tersimpan. Stok barang bertambah $jumlah.");
            redirect('masuk.php');
        } catch (PDOException $ex) {
            $pdo->rollBack();
            $error[] = 'Gagal menyimpan transaksi: ' . $ex->getMessage();
        }
    }
}

$daftar = $pdo->query(
    'SELECT m.*, b.kode_barang, b.nama_barang, p.nama_pemasok, u.nama_lengkap
     FROM barang_masuk m
     JOIN barang b ON b.id_barang = m.id_barang
     LEFT JOIN pemasok p ON p.id_pemasok = m.id_pemasok
     JOIN users u ON u.id_user = m.id_user
     ORDER BY m.id_masuk DESC LIMIT 25'
)->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-3">
  <div class="col-xl-4">
    <div class="card-ims">
      <div class="card-title"><i class="bi bi-box-arrow-in-down me-1"></i> Form Barang Masuk</div>
      <?php foreach ($error as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
      <form method="post" class="needs-validation" novalidate>
        <div class="mb-3"><label class="form-label">Barang *</label>
          <select name="id_barang" class="form-select" required>
            <option value="">-- Pilih Barang --</option>
            <?php foreach ($barang as $b): ?>
              <option value="<?= (int)$b['id_barang'] ?>"><?= e($b['kode_barang'] . ' - ' . $b['nama_barang']) ?> (stok <?= (int)$b['stok'] ?>)</option>
            <?php endforeach; ?>
          </select></div>
        <div class="mb-3"><label class="form-label">Pemasok</label>
          <select name="id_pemasok" class="form-select">
            <option value="">-- Tanpa Pemasok --</option>
            <?php foreach ($pemasok as $p): ?>
              <option value="<?= (int)$p['id_pemasok'] ?>"><?= e($p['nama_pemasok']) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="mb-3"><label class="form-label">Jumlah *</label>
          <input type="number" min="1" name="jumlah" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Tanggal Masuk *</label>
          <input type="date" name="tanggal_masuk" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
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
        <thead><tr><th>Kode</th><th>Barang</th><th>Jumlah</th><th>Pemasok</th><th>Tanggal</th><th>Petugas</th><th>Ket.</th></tr></thead>
        <tbody>
        <?php if (!$daftar): ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada transaksi.</td></tr><?php endif; ?>
        <?php foreach ($daftar as $m): ?>
          <tr>
            <td><strong><?= e($m['kode_transaksi']) ?></strong></td>
            <td><?= e($m['kode_barang']) ?> - <?= e($m['nama_barang']) ?></td>
            <td><span class="badge-soft badge-success">+<?= (int)$m['jumlah'] ?></span></td>
            <td><?= e($m['nama_pemasok'] ?: '-') ?></td>
            <td><?= e(date('d/m/Y', strtotime($m['tanggal_masuk']))) ?></td>
            <td><?= e($m['nama_lengkap']) ?></td>
            <td><?= e($m['keterangan'] ?: '-') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
