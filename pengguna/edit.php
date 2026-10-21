<?php
/**
 * pengguna/edit.php — ubah data pengguna. Password hanya diganti bila diisi.
 */
require_once __DIR__ . '/../includes/auth.php';
wajib_admin();
$judul = 'Edit Pengguna'; $menu_aktif = 'pengguna'; $error = [];

$id = (int) ($_GET['id'] ?? 0);
$s = $pdo->prepare('SELECT * FROM users WHERE id_user = ?'); $s->execute([$id]);
$u = $s->fetch();
if (!$u) { set_flash('danger', 'Pengguna tidak ditemukan.'); redirect('index.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = ($_POST['role'] ?? 'petugas') === 'admin' ? 'admin' : 'petugas';
    $status   = ($_POST['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';
    $pass     = $_POST['password'] ?? '';

    if ($username === '') $error[] = 'Username wajib diisi.';
    if ($nama === '')     $error[] = 'Nama lengkap wajib diisi.';
    if ($pass !== '' && strlen($pass) < 6) $error[] = 'Password minimal 6 karakter.';
    if (!$error) {
        $cek = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id_user <> ?');
        $cek->execute([$username, $id]);
        if ($cek->fetchColumn() > 0) $error[] = 'Username sudah dipakai pengguna lain.';
    }
    if (!$error) {
        if ($pass !== '') {
            $pdo->prepare('UPDATE users SET username=?,nama_lengkap=?,email=?,role=?,status=?,password=? WHERE id_user=?')
                ->execute([$username, $nama, $email, $role, $status, password_hash($pass, PASSWORD_DEFAULT), $id]);
        } else {
            $pdo->prepare('UPDATE users SET username=?,nama_lengkap=?,email=?,role=?,status=? WHERE id_user=?')
                ->execute([$username, $nama, $email, $role, $status, $id]);
        }
        set_flash('success', 'Data pengguna berhasil diperbarui.');
        redirect('index.php');
    }
    $u = array_merge($u, compact('username', 'nama', 'email', 'role', 'status'));
    $u['nama_lengkap'] = $nama;
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card-ims" style="max-width:720px">
  <?php foreach ($error as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="row g-3 needs-validation" novalidate>
    <div class="col-md-6"><label class="form-label">Username *</label>
      <input name="username" class="form-control" required value="<?= e($u['username']) ?>"></div>
    <div class="col-md-6"><label class="form-label">Nama Lengkap *</label>
      <input name="nama_lengkap" class="form-control" required value="<?= e($u['nama_lengkap']) ?>"></div>
    <div class="col-md-6"><label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?= e($u['email']) ?>"></div>
    <div class="col-md-3"><label class="form-label">Role *</label>
      <select name="role" class="form-select">
        <option value="petugas" <?= $u['role'] === 'petugas' ? 'selected' : '' ?>>Petugas</option>
        <option value="admin"   <?= $u['role'] === 'admin'   ? 'selected' : '' ?>>Admin</option>
      </select></div>
    <div class="col-md-3"><label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="aktif"    <?= $u['status'] === 'aktif'    ? 'selected' : '' ?>>Aktif</option>
        <option value="nonaktif" <?= $u['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
      </select></div>
    <div class="col-md-6"><label class="form-label">Password Baru <span class="text-muted">(kosongkan bila tidak diubah)</span></label>
      <input type="password" name="password" class="form-control" minlength="6"></div>
    <div class="col-12"><button class="btn btn-teal"><i class="bi bi-save me-1"></i> Perbarui</button>
      <a href="index.php" class="btn btn-outline-secondary">Batal</a></div>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
