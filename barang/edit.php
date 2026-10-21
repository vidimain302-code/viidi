<?php
/**
 * barang/edit.php
 * Form ubah data barang berdasarkan id, lalu UPDATE ke tabel barang.
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Edit Barang';
$menu_aktif = 'barang';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM barang WHERE id_barang = ?');
$stmt->execute([$id]);
$barang = $stmt->fetch();
if (!$barang) { set_flash('danger', 'Data barang tidak ditemukan.'); redirect('index.php'); }

$kategori = $pdo->query('SELECT * FROM kategori ORDER BY nama_kategori')->fetchAll();
$pemasok  = $pdo->query('SELECT * FROM pemasok ORDER BY nama_pemasok')->fetchAll();
$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = [
        'kode_barang'   => trim($_POST['kode_barang'] ?? ''),
        'nama_barang'   => trim($_POST['nama_barang'] ?? ''),
        'id_kategori'   => (int) ($_POST['id_kategori'] ?? 0),
        'id_pemasok'    => ($_POST['id_pemasok'] ?? '') !== '' ? (int) $_POST['id_pemasok'] : null,
        'merek'         => trim($_POST['merek'] ?? ''),
        'spesifikasi'   => trim($_POST['spesifikasi'] ?? ''),
        'satuan'        => trim($_POST['satuan'] ?? 'Unit'),
        'harga_beli'    => (float) ($_POST['harga_beli'] ?? 0),
        'harga_jual'    => (float) ($_POST['harga_jual'] ?? 0),
        'stok'          => (int) ($_POST['stok'] ?? 0),
        'stok_minimum'  => (int) ($_POST['stok_minimum'] ?? 0),
        'kondisi'       => $_POST['kondisi'] ?? 'Baik',
        'lokasi'        => trim($_POST['lokasi'] ?? ''),
        'tanggal_masuk' => $_POST['tanggal_masuk'] ?? date('Y-m-d'),
    ];
    if ($d['kode_barang'] === '') $error[] = 'Kode barang wajib diisi.';
    if ($d['nama_barang'] === '') $error[] = 'Nama barang wajib diisi.';
    if ($d['id_kategori'] <= 0)   $error[] = 'Kategori wajib dipilih.';
    if ($d['stok'] < 0)           $error[] = 'Stok tidak boleh negatif.';

    if (!$error) {
        $cek = $pdo->prepare('SELECT COUNT(*) FROM barang WHERE kode_barang = ? AND id_barang <> ?');
        $cek->execute([$d['kode_barang'], $id]);
        if ($cek->fetchColumn() > 0) $error[] = 'Kode barang sudah dipakai barang lain.';
    }
    if (!$error) {
        $sql = 'UPDATE barang SET kode_barang=?,nama_barang=?,id_kategori=?,id_pemasok=?,merek=?,spesifikasi=?,
                satuan=?,harga_beli=?,harga_jual=?,stok=?,stok_minimum=?,kondisi=?,lokasi=?,tanggal_masuk=?
                WHERE id_barang=?';
        $pdo->prepare($sql)->execute([...array_values($d), $id]);
        set_flash('success', 'Data barang berhasil diperbarui.');
        redirect('index.php');
    }
    $barang = array_merge($barang, $d);
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card-ims" style="max-width:980px">
  <?php foreach ($error as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="row g-3 needs-validation" novalidate>
    <div class="col-md-4"><label class="form-label">Kode Barang *</label>
      <input name="kode_barang" class="form-control" required value="<?= e($barang['kode_barang']) ?>"></div>
    <div class="col-md-8"><label class="form-label">Nama Barang *</label>
      <input name="nama_barang" class="form-control" required value="<?= e($barang['nama_barang']) ?>"></div>
    <div class="col-md-4"><label class="form-label">Kategori *</label>
      <select name="id_kategori" class="form-select" required>
        <?php foreach ($kategori as $k): ?>
          <option value="<?= (int)$k['id_kategori'] ?>" <?= $barang['id_kategori'] == $k['id_kategori'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-4"><label class="form-label">Pemasok</label>
      <select name="id_pemasok" class="form-select">
        <option value="">-- Tanpa Pemasok --</option>
        <?php foreach ($pemasok as $p): ?>
          <option value="<?= (int)$p['id_pemasok'] ?>" <?= $barang['id_pemasok'] == $p['id_pemasok'] ? 'selected' : '' ?>><?= e($p['nama_pemasok']) ?> - <?= e($p['nama_perusahaan']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-4"><label class="form-label">Merek</label>
      <input name="merek" class="form-control" value="<?= e($barang['merek']) ?>"></div>
    <div class="col-12"><label class="form-label">Spesifikasi</label>
      <textarea name="spesifikasi" class="form-control" rows="2"><?= e($barang['spesifikasi']) ?></textarea></div>
    <div class="col-md-3"><label class="form-label">Satuan *</label>
      <input name="satuan" class="form-control" required value="<?= e($barang['satuan']) ?>"></div>
    <div class="col-md-3"><label class="form-label">Harga Beli</label>
      <input type="number" min="0" name="harga_beli" class="form-control" value="<?= e((int)$barang['harga_beli']) ?>"></div>
    <div class="col-md-3"><label class="form-label">Harga Jual</label>
      <input type="number" min="0" name="harga_jual" class="form-control" value="<?= e((int)$barang['harga_jual']) ?>"></div>
    <div class="col-md-3"><label class="form-label">Kondisi</label>
      <select name="kondisi" class="form-select">
        <?php foreach (['Baik','Rusak Ringan','Rusak Berat'] as $kd): ?>
          <option <?= $barang['kondisi'] === $kd ? 'selected' : '' ?>><?= $kd ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-3"><label class="form-label">Stok</label>
      <input type="number" min="0" name="stok" class="form-control" value="<?= (int)$barang['stok'] ?>"></div>
    <div class="col-md-3"><label class="form-label">Stok Minimum</label>
      <input type="number" min="0" name="stok_minimum" class="form-control" value="<?= (int)$barang['stok_minimum'] ?>"></div>
    <div class="col-md-3"><label class="form-label">Lokasi Penyimpanan</label>
      <input name="lokasi" class="form-control" value="<?= e($barang['lokasi']) ?>"></div>
    <div class="col-md-3"><label class="form-label">Tanggal Masuk *</label>
      <input type="date" name="tanggal_masuk" class="form-control" required value="<?= e($barang['tanggal_masuk']) ?>"></div>
    <div class="col-12 d-flex gap-2">
      <button class="btn btn-teal"><i class="bi bi-save me-1"></i> Perbarui</button>
      <a href="index.php" class="btn btn-outline-secondary">Batal</a>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
