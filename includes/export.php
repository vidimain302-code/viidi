<?php
/**
 * includes/export.php
 * Fungsi bantu untuk mengekspor tabel laporan menjadi file Excel (.xls)
 * tanpa library tambahan, cukup dengan header HTTP + tabel HTML.
 * Dipakai oleh semua file di folder laporan/.
 */

/**
 * @param string $nama_file  contoh: laporan-barang
 * @param string $judul      judul di baris pertama file
 * @param array  $kolom      daftar judul kolom
 * @param array  $baris      array of array (isi tabel)
 */
function export_excel(string $nama_file, string $judul, array $kolom, array $baris): void
{
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nama_file . '-' . date('Ymd-His') . '.xls"');
    header('Pragma: no-cache');
    echo "\xEF\xBB\xBF"; // BOM agar huruf beraksen tampil benar di Excel
    echo '<table border="1"><tr><th colspan="' . count($kolom) . '">' . htmlspecialchars($judul) . '</th></tr><tr>';
    foreach ($kolom as $k) {
        echo '<th>' . htmlspecialchars((string) $k) . '</th>';
    }
    echo '</tr>';
    foreach ($baris as $b) {
        echo '<tr>';
        foreach ($b as $sel) {
            echo '<td>' . htmlspecialchars((string) $sel) . '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    exit;
}

/** Tombol standar pada setiap halaman laporan */
function tombol_laporan(string $query_export): string
{
    return '<div class="page-actions no-print">
        <button onclick="window.print()" class="btn btn-teal"><i class="bi bi-printer me-1"></i> Cetak</button>
        <a href="?' . htmlspecialchars($query_export) . '" class="btn btn-success"><i class="bi bi-file-earmark-excel me-1"></i> Export Excel</a>
        <button onclick="window.print()" class="btn btn-danger"><i class="bi bi-file-earmark-pdf me-1"></i> Export PDF</button>
        <span class="text-muted small ms-2">PDF dibuat lewat jendela cetak &rarr; pilih tujuan "Save as PDF".</span>
      </div>';
}

/** Navigasi antar jenis laporan */
function tab_laporan(string $aktif): string
{
    $tabs = [
        'barang' => ['Laporan Barang', 'barang.php'],
        'masuk'  => ['Laporan Barang Masuk', 'masuk.php'],
        'keluar' => ['Laporan Barang Keluar', 'keluar.php'],
        'stok'   => ['Laporan Stok', 'stok.php'],
    ];
    $html = '<ul class="nav nav-pills mb-3 no-print">';
    foreach ($tabs as $key => [$label, $url]) {
        $cls = $key === $aktif ? 'nav-link active' : 'nav-link';
        $style = $key === $aktif ? ' style="background:#0ea5a4"' : '';
        $html .= '<li class="nav-item"><a class="' . $cls . '"' . $style . ' href="' . $url . '">' . $label . '</a></li>';
    }
    return $html . '</ul>';
}
