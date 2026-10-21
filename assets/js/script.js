/* assets/js/script.js — interaksi kecil: sidebar mobile, konfirmasi hapus, pencarian tabel */
document.addEventListener('DOMContentLoaded', function () {
  // Sidebar untuk layar HP
  const sidebar = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebarBackdrop');
  const toggle = document.getElementById('toggleSidebar');
  if (toggle) {
    toggle.addEventListener('click', function () {
      sidebar.classList.add('show');
      backdrop.classList.add('show');
    });
  }
  if (backdrop) {
    backdrop.addEventListener('click', function () {
      sidebar.classList.remove('show');
      backdrop.classList.remove('show');
    });
  }

  // Konfirmasi sebelum hapus / logout
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!window.confirm(el.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });

  // Validasi form bawaan Bootstrap
  document.querySelectorAll('.needs-validation').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  });

  // Alert hilang otomatis setelah 4 detik
  document.querySelectorAll('.alert-dismissible').forEach(function (al) {
    setTimeout(function () {
      if (bootstrap.Alert.getOrCreateInstance(al)) {
        bootstrap.Alert.getOrCreateInstance(al).close();
      }
    }, 4000);
  });
});
