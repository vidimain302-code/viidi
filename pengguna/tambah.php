<?php
/**
 * pengguna/tambah.php — tambah pengguna baru. Password disimpan dengan password_hash().
 */
require_once __DIR__ . '/../includes/auth.php';
wajib_admin();
$judul = 'Tambah Pengguna'; $menu_aktif = 'pengguna'; $error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = ($_POST['role'] ?? 'petugas') === 'admin' ? 'admin' : 'petugas';
    $status   = ($_POST['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';
    $pass     = $_POST['password'] ?? '';
    $pass2    = $_POST['password2'] ?? '';

    if ($username === '')            $error[] = 'Username wajib diisi.';
    if ($nama === '')                $error[] = 'Nama lengkap wajib diisi.';
    if (strlen($pass) < 6)           $error[] = 'Password minimal 6 karakter.';
    if ($pass !== $pass2)            $error[] = 'Konfirmasi password tidak sama.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $error[] = 'Format email tidak valid.';
    if (!$error) {
        $cek = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $cek->execute([$username]);
        if ($cek->fetchColumn() > 0) $error[] = 'Username sudah digunakan.';
    }
    if (!$error) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO users (username,password,nama_lengkap,email,role,status) VALUES (?,?,?,?,?,?)')
            ->execute([$username, $hash, $nama, $email, $role, $status]);
        set_flash('success', 'Pengguna berhasil ditambahkan.');
        redirect('index.php');
    }
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card-ims" style="max-width:720px">
  <?php foreach ($error as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="row g-3 needs-validation" novalidate>
    <div class="col-md-6"><label class="form-label">Username *</label>
      <input name="username" class="form-control" required value="<?= e($_POST['username'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Nama Lengkap *</label>
      <input name="nama_lengkap" class="form-control" required value="<?= e($_POST['nama_lengkap'] ?? '') ?>"></div>
    <div class="col-md-6"><label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>"></div>
    <div class="col-md-3"><label class="form-label">Role *</label>
      <select name="role" class="form-select"><option value="petugas">Petugas</option><option value="admin">Admin</option></select></div>
    <div class="col-md-3"><label class="form-label">Status</label>
      <select name="status" class="form-select"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select></div>
    <div class="col-md-6"><label class="form-label">Password *</label>
      <input type="password" name="password" class="form-control" required minlength="6"></div>
    <div class="col-md-6"><label class="form-label">Ulangi Password *</label>
      <input type="password" name="password2" class="form-control" required minlength="6"></div>
    <div class="col-12"><button class="btn btn-teal"><i class="bi bi-save me-1"></i> Simpan</button>
      <a href="index.php" class="btn btn-outline-secondary">Batal</a></div>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
