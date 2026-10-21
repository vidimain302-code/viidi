<?php
/**
 * kategori/edit.php — form + proses UPDATE kategori.
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Edit Kategori'; $menu_aktif = 'kategori'; $error = [];

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM kategori WHERE id_kategori = ?'); $stmt->execute([$id]);
$kat = $stmt->fetch();
if (!$kat) { set_flash('danger', 'Kategori tidak ditemukan.'); redirect('index.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_kategori'] ?? '');
    $desk = trim($_POST['deskripsi'] ?? '');
    if ($nama === '') $error[] = 'Nama kategori wajib diisi.';
    if (!$error) {
        $cek = $pdo->prepare('SELECT COUNT(*) FROM kategori WHERE nama_kategori = ? AND id_kategori <> ?');
        $cek->execute([$nama, $id]);
        if ($cek->fetchColumn() > 0) $error[] = 'Nama kategori sudah dipakai.';
    }
    if (!$error) {
        $pdo->prepare('UPDATE kategori SET nama_kategori=?, deskripsi=? WHERE id_kategori=?')->execute([$nama, $desk, $id]);
        set_flash('success', 'Kategori berhasil diperbarui.');
        redirect('index.php');
    }
    $kat['nama_kategori'] = $nama; $kat['deskripsi'] = $desk;
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card-ims" style="max-width:620px">
  <?php foreach ($error as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="needs-validation" novalidate>
    <div class="mb-3"><label class="form-label">Nama Kategori *</label>
      <input name="nama_kategori" class="form-control" required value="<?= e($kat['nama_kategori']) ?>"></div>
    <div class="mb-3"><label class="form-label">Deskripsi</label>
      <textarea name="deskripsi" rows="3" class="form-control"><?= e($kat['deskripsi']) ?></textarea></div>
    <button class="btn btn-teal"><i class="bi bi-save me-1"></i> Perbarui</button>
    <a href="index.php" class="btn btn-outline-secondary">Batal</a>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
