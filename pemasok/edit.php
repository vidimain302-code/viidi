<?php
/**
 * pemasok/edit.php — form + UPDATE data pemasok.
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Edit Pemasok'; $menu_aktif = 'pemasok'; $error = [];

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM pemasok WHERE id_pemasok = ?'); $stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { set_flash('danger', 'Pemasok tidak ditemukan.'); redirect('index.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = [
        trim($_POST['nama_pemasok'] ?? ''), trim($_POST['nama_perusahaan'] ?? ''),
        trim($_POST['telepon'] ?? ''), trim($_POST['email'] ?? ''), trim($_POST['alamat'] ?? ''),
    ];
    if ($d[0] === '') $error[] = 'Nama pemasok wajib diisi.';
    if ($d[3] !== '' && !filter_var($d[3], FILTER_VALIDATE_EMAIL)) $error[] = 'Format email tidak valid.';
    if (!$error) {
        $pdo->prepare('UPDATE pemasok SET nama_pemasok=?,nama_perusahaan=?,telepon=?,email=?,alamat=? WHERE id_pemasok=?')
            ->execute([...$d, $id]);
        set_flash('success', 'Data pemasok berhasil diperbarui.');
        redirect('index.php');
    }
    [$p['nama_pemasok'], $p['nama_perusahaan'], $p['telepon'], $p['email'], $p['alamat']] = $d;
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card-ims" style="max-width:760px">
  <?php foreach ($error as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="row g-3 needs-validation" novalidate>
    <div class="col-md-6"><label class="form-label">Nama Pemasok *</label>
      <input name="nama_pemasok" class="form-control" required value="<?= e($p['nama_pemasok']) ?>"></div>
    <div class="col-md-6"><label class="form-label">Nama Perusahaan</label>
      <input name="nama_perusahaan" class="form-control" value="<?= e($p['nama_perusahaan']) ?>"></div>
    <div class="col-md-6"><label class="form-label">Telepon</label>
      <input name="telepon" class="form-control" value="<?= e($p['telepon']) ?>"></div>
    <div class="col-md-6"><label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?= e($p['email']) ?>"></div>
    <div class="col-12"><label class="form-label">Alamat</label>
      <textarea name="alamat" rows="3" class="form-control"><?= e($p['alamat']) ?></textarea></div>
    <div class="col-12"><button class="btn btn-teal"><i class="bi bi-save me-1"></i> Perbarui</button>
      <a href="index.php" class="btn btn-outline-secondary">Batal</a></div>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
