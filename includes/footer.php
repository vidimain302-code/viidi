<?php
/**
 * includes/footer.php
 * Penutup semua halaman: footer, script Bootstrap, Chart.js, dan script.js aplikasi.
 */
?>
    </main>
    <footer class="app-footer">
      &copy; <?= date('Y') ?> Inventory Management System &middot; Dibuat untuk pembelajaran RPL
    </footer>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= e(base_url('assets/js/script.js')) ?>"></script>
</body>
</html>
