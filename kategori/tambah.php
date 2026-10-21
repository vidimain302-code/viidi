<?php
/**
 * kategori/tambah.php — form + proses INSERT kategori baru.
 */
require_once __DIR__ . '/../includes/auth.php';
$judul = 'Tambah Kategori'; $menu_aktif = 'kategori'; $error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_kategori'] ?? '');
    $desk = trim($_POST['deskripsi'] ?? '');
    if ($nama === '') $error[] = 'Nama kategori wajib diisi.';
    if (!$error) {
        $cek = $pdo->prepare('SELECT COUNT(*) FROM kategori WHERE nama_kategori = ?');
        $cek->execute([$nama]);
        if ($cek->fetchColumn() > 0) $error[] = 'Nama kategori sudah ada.';
    }
    if (!$error) {
        $pdo->prepare('INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?,?)')->execute([$nama, $desk]);
        set_flash('success', 'Kategori berhasil ditambahkan.');
        redirect('index.php');
    }
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card-ims" style="max-width:620px">
  <?php foreach ($error as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="needs-validation" novalidate>
    <div class="mb-3"><label class="form-label">Nama Kategori *</label>
      <input name="nama_kategori" class="form-control" required value="<?= e($_POST['nama_kategori'] ?? '') ?>"></div>
    <div class="mb-3"><label class="form-label">Deskripsi</label>
      <textarea name="deskripsi" rows="3" class="form-control"><?= e($_POST['deskripsi'] ?? '') ?></textarea></div>
    <button class="btn btn-teal"><i class="bi bi-save me-1"></i> Simpan</button>
    <a href="index.php" class="btn btn-outline-secondary">Batal</a>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
