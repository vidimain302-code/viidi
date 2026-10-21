<?php
/**
 * auth/login.php
 * Halaman form login. Form dikirim (POST) ke auth/proses_login.php.
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Sudah login? langsung ke dashboard
if (!empty($_SESSION['user_id'])) {
    redirect('../dashboard/index.php');
}
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login &middot; Inventory Management System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
  <div class="login-card">
    <div class="login-logo"><i class="bi bi-boxes"></i></div>
    <h1>INVENTORY MANAGEMENT SYSTEM</h1>
    <p class="sub">Masuk untuk mengelola data inventaris</p>

    <?php if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?> py-2"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <form action="proses_login.php" method="post" class="needs-validation" novalidate>
      <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" class="form-control" id="username" name="username" required autofocus
                 value="<?= e($_SESSION['old_username'] ?? '') ?>">
          <div class="invalid-feedback">Username wajib diisi.</div>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" class="form-control" id="password" name="password" required>
          <button class="btn btn-outline-secondary" type="button" id="togglePass"><i class="bi bi-eye"></i></button>
          <div class="invalid-feedback">Password wajib diisi.</div>
        </div>
      </div>
      <button type="submit" class="btn btn-teal w-100 py-2"><i class="bi bi-box-arrow-in-right me-1"></i> Login</button>
    </form>

    <div class="login-hint">
      <strong>Akun percobaan:</strong><br>
      Admin &rarr; <code>admin</code> / <code>admin123</code><br>
      Petugas &rarr; <code>petugas</code> / <code>petugas123</code>
    </div>
  </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<script>
document.getElementById('togglePass').addEventListener('click', function () {
  const p = document.getElementById('password');
  p.type = p.type === 'password' ? 'text' : 'password';
  this.innerHTML = p.type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
});
</script>
</body>
</html>
<?php unset($_SESSION['old_username']); ?>
