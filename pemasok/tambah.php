<?php
/**
 * pemasok/tambah.php — form + INSERT pemasok baru.
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Tambah Pemasok'; $menu_aktif = 'pemasok'; $error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama_pemasok'] ?? '');
    $persh = trim($_POST['nama_perusahaan'] ?? '');
    $telp  = trim($_POST['telepon'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $alamat= trim($_POST['alamat'] ?? '');
    if ($nama === '') $error[] = 'Nama pemasok wajib diisi.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $error[] = 'Format email tidak valid.';
    if (!$error) {
        $pdo->prepare('INSERT INTO pemasok (nama_pemasok,nama_perusahaan,telepon,email,alamat) VALUES (?,?,?,?,?)')
            ->execute([$nama, $persh, $telp, $email, $alamat]);
        set_flash('success', 'Pemasok berhasil ditambahkan.');
        redirect('index.php');
    }
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card-ims" style="max-width:760px">
  <?php foreach ($error as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="row g-3 needs-validation" novalidate>
    <div class="col-md-6"><label class="form-label">Nama Pemasok *</label>
      <input name="nama_pemasok" class="form-control" required value="<?= e($_POST['nama_pemasok'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Nama Perusahaan</label>
      <input name="nama_perusahaan" class="form-control" value="<?= e($_POST['nama_perusahaan'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Telepon</label>
      <input name="telepon" class="form-control" value="<?= e($_POST['telepon'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>"></div>
    <div class="col-12"><label class="form-label">Alamat</label>
      <textarea name="alamat" rows="3" class="form-control"><?= e($_POST['alamat'] ?? '') ?></textarea></div>
    <div class="col-12"><button class="btn btn-teal"><i class="bi bi-save me-1"></i> Simpan</button>
      <a href="index.php" class="btn btn-outline-secondary">Batal</a></div>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
