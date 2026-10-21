<?php
/**
 * barang/tambah.php
 * Form tambah barang baru + proses simpan ke tabel barang (prepared statement).
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Tambah Barang';
$menu_aktif = 'barang';

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

    if ($d['kode_barang'] === '')  $error[] = 'Kode barang wajib diisi.';
    if ($d['nama_barang'] === '')  $error[] = 'Nama barang wajib diisi.';
    if ($d['id_kategori'] <= 0)    $error[] = 'Kategori wajib dipilih.';
    if ($d['stok'] < 0 || $d['stok_minimum'] < 0) $error[] = 'Stok tidak boleh negatif.';
    if ($d['harga_beli'] < 0 || $d['harga_jual'] < 0) $error[] = 'Harga tidak boleh negatif.';

    if (!$error) {
        $cek = $pdo->prepare('SELECT COUNT(*) FROM barang WHERE kode_barang = ?');
        $cek->execute([$d['kode_barang']]);
        if ($cek->fetchColumn() > 0) $error[] = 'Kode barang sudah digunakan.';
    }

    if (!$error) {
        $sql = 'INSERT INTO barang (kode_barang,nama_barang,id_kategori,id_pemasok,merek,spesifikasi,satuan,
                harga_beli,harga_jual,stok,stok_minimum,kondisi,lokasi,tanggal_masuk)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $pdo->prepare($sql)->execute(array_values($d));
        set_flash('success', 'Barang berhasil ditambahkan.');
        redirect('index.php');
    }
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card-ims" style="max-width:980px">
  <?php foreach ($error as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="row g-3 needs-validation" novalidate>
    <div class="col-md-4"><label class="form-label">Kode Barang *</label>
      <input name="kode_barang" class="form-control" required value="<?= e($_POST['kode_barang'] ?? '') ?>"></div>
    <div class="col-md-8"><label class="form-label">Nama Barang *</label>
      <input name="nama_barang" class="form-control" required value="<?= e($_POST['nama_barang'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label">Kategori *</label>
      <select name="id_kategori" class="form-select" required>
        <option value="">-- Pilih Kategori --</option>
        <?php foreach ($kategori as $k): ?>
          <option value="<?= (int)$k['id_kategori'] ?>" <?= (($_POST['id_kategori'] ?? '') == $k['id_kategori']) ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-4"><label class="form-label">Pemasok</label>
      <select name="id_pemasok" class="form-select">
        <option value="">-- Tanpa Pemasok --</option>
        <?php foreach ($pemasok as $p): ?>
          <option value="<?= (int)$p['id_pemasok'] ?>" <?= (($_POST['id_pemasok'] ?? '') == $p['id_pemasok']) ? 'selected' : '' ?>><?= e($p['nama_pemasok']) ?> - <?= e($p['nama_perusahaan']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-4"><label class="form-label">Merek</label>
      <input name="merek" class="form-control" value="<?= e($_POST['merek'] ?? '') ?>"></div>
    <div class="col-12"><label class="form-label">Spesifikasi</label>
      <textarea name="spesifikasi" class="form-control" rows="2"><?= e($_POST['spesifikasi'] ?? '') ?></textarea></div>
    <div class="col-md-3"><label class="form-label">Satuan *</label>
      <input name="satuan" class="form-control" required value="<?= e($_POST['satuan'] ?? 'Unit') ?>"></div>
    <div class="col-md-3"><label class="form-label">Harga Beli</label>
      <input type="number" step="1" min="0" name="harga_beli" class="form-control" value="<?= e($_POST['harga_beli'] ?? 0) ?>"></div>
    <div class="col-md-3"><label class="form-label">Harga Jual</label>
      <input type="number" step="1" min="0" name="harga_jual" class="form-control" value="<?= e($_POST['harga_jual'] ?? 0) ?>"></div>
    <div class="col-md-3"><label class="form-label">Kondisi</label>
      <select name="kondisi" class="form-select">
        <?php foreach (['Baik','Rusak Ringan','Rusak Berat'] as $kd): ?>
          <option <?= (($_POST['kondisi'] ?? '') === $kd) ? 'selected' : '' ?>><?= $kd ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-3"><label class="form-label">Stok Awal</label>
      <input type="number" min="0" name="stok" class="form-control" value="<?= e($_POST['stok'] ?? 0) ?>"></div>
    <div class="col-md-3"><label class="form-label">Stok Minimum</label>
      <input type="number" min="0" name="stok_minimum" class="form-control" value="<?= e($_POST['stok_minimum'] ?? 0) ?>"></div>
    <div class="col-md-3"><label class="form-label">Lokasi Penyimpanan</label>
      <input name="lokasi" class="form-control" value="<?= e($_POST['lokasi'] ?? '') ?>"></div>
    <div class="col-md-3"><label class="form-label">Tanggal Masuk *</label>
      <input type="date" name="tanggal_masuk" class="form-control" required value="<?= e($_POST['tanggal_masuk'] ?? date('Y-m-d')) ?>"></div>
    <div class="col-12 d-flex gap-2">
      <button class="btn btn-teal"><i class="bi bi-save me-1"></i> Simpan</button>
      <a href="index.php" class="btn btn-outline-secondary">Batal</a>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
