<?php
/**
 * pengguna/index.php — daftar pengguna. HANYA bisa diakses role admin.
 */
require_once __DIR__ . '/../includes/auth.php';
wajib_admin();
$judul = 'Manajemen Pengguna'; $menu_aktif = 'pengguna';

$cari = trim($_GET['cari'] ?? '');
$sql = 'SELECT * FROM users'; $param = [];
if ($cari !== '') { $sql .= ' WHERE username LIKE ? OR nama_lengkap LIKE ?'; $param = ["%$cari%", "%$cari%"]; }
$sql .= ' ORDER BY id_user';
$s = $pdo->prepare($sql); $s->execute($param); $daftar = $s->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-actions">
  <a href="tambah.php" class="btn btn-teal"><i class="bi bi-person-plus me-1"></i> Tambah Pengguna</a>
  <form class="d-flex gap-2 ms-auto" method="get">
    <input name="cari" class="form-control" placeholder="Cari pengguna..." value="<?= e($cari) ?>">
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
  </form>
</div>
<div class="table-wrap">
  <table class="table-ims">
    <thead><tr><th>#</th><th>Username</th><th>Nama Lengkap</th><th>Email</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php $no = 1; foreach ($daftar as $u): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><strong><?= e($u['username']) ?></strong></td>
        <td><?= e($u['nama_lengkap']) ?></td>
        <td><?= e($u['email'] ?: '-') ?></td>
        <td><span class="badge-soft <?= $u['role'] === 'admin' ? 'badge-info' : 'badge-success' ?>"><?= e(ucfirst($u['role'])) ?></span></td>
        <td><span class="badge-soft <?= $u['status'] === 'aktif' ? 'badge-success' : 'badge-danger' ?>"><?= e(ucfirst($u['status'])) ?></span></td>
        <td class="text-nowrap">
          <a href="edit.php?id=<?= (int)$u['id_user'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
          <?php if ((int)$u['id_user'] !== (int)$_SESSION['user_id']): ?>
            <a href="hapus.php?id=<?= (int)$u['id_user'] ?>" class="btn btn-sm btn-outline-danger"
               data-confirm="Hapus pengguna <?= e($u['username']) ?>?"><i class="bi bi-trash"></i></a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
