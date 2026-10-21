<?php
/**
 * dashboard/index.php
 * Halaman utama setelah login: ringkasan angka, grafik, stok menipis,
 * barang terbaru, dan aktivitas transaksi terbaru. Semua data dari MySQL.
 */
require_once __DIR__ . '/../includes/auth.php';

$judul      = 'Dashboard';
$menu_aktif = 'dashboard';

$total_barang   = (int) $pdo->query('SELECT COUNT(*) FROM barang')->fetchColumn();
$total_kategori = (int) $pdo->query('SELECT COUNT(*) FROM kategori')->fetchColumn();
$total_pemasok  = (int) $pdo->query('SELECT COUNT(*) FROM pemasok')->fetchColumn();
$total_stok     = (int) $pdo->query('SELECT COALESCE(SUM(stok),0) FROM barang')->fetchColumn();
$total_masuk    = (int) $pdo->query('SELECT COALESCE(SUM(jumlah),0) FROM barang_masuk')->fetchColumn();
$total_keluar   = (int) $pdo->query('SELECT COALESCE(SUM(jumlah),0) FROM barang_keluar')->fetchColumn();

$stok_menipis = $pdo->query(
    'SELECT b.kode_barang, b.nama_barang, b.stok, b.stok_minimum, k.nama_kategori
     FROM barang b JOIN kategori k ON k.id_kategori = b.id_kategori
     WHERE b.stok <= b.stok_minimum ORDER BY b.stok ASC LIMIT 6'
)->fetchAll();

$barang_terbaru = $pdo->query(
    'SELECT b.kode_barang, b.nama_barang, b.stok, b.satuan, k.nama_kategori, b.tanggal_masuk
     FROM barang b JOIN kategori k ON k.id_kategori = b.id_kategori
     ORDER BY b.id_barang DESC LIMIT 5'
)->fetchAll();

$aktivitas = $pdo->query(
    "SELECT 'Masuk' AS jenis, m.kode_transaksi, b.nama_barang, m.jumlah, m.tanggal_masuk AS tanggal, u.nama_lengkap
       FROM barang_masuk m JOIN barang b ON b.id_barang=m.id_barang JOIN users u ON u.id_user=m.id_user
     UNION ALL
     SELECT 'Keluar', k.kode_transaksi, b.nama_barang, k.jumlah, k.tanggal_keluar, u.nama_lengkap
       FROM barang_keluar k JOIN barang b ON b.id_barang=k.id_barang JOIN users u ON u.id_user=k.id_user
     ORDER BY tanggal DESC LIMIT 8"
)->fetchAll();

// Data grafik: 6 bulan terakhir
$labels_bulan = $masuk_bulan = $keluar_bulan = [];
for ($i = 5; $i >= 0; $i--) {
    $bulan = date('Y-m', strtotime("-$i month"));
    $labels_bulan[] = date('M Y', strtotime($bulan . '-01'));

    $s = $pdo->prepare("SELECT COALESCE(SUM(jumlah),0) FROM barang_masuk WHERE DATE_FORMAT(tanggal_masuk,'%Y-%m') = ?");
    $s->execute([$bulan]);
    $masuk_bulan[] = (int) $s->fetchColumn();

    $s = $pdo->prepare("SELECT COALESCE(SUM(jumlah),0) FROM barang_keluar WHERE DATE_FORMAT(tanggal_keluar,'%Y-%m') = ?");
    $s->execute([$bulan]);
    $keluar_bulan[] = (int) $s->fetchColumn();
}

$per_kategori = $pdo->query(
    'SELECT k.nama_kategori, COUNT(b.id_barang) AS jumlah
     FROM kategori k LEFT JOIN barang b ON b.id_kategori = k.id_kategori
     GROUP BY k.id_kategori ORDER BY jumlah DESC'
)->fetchAll();

$kondisi_stok = [
    'Aman'    => (int) $pdo->query('SELECT COUNT(*) FROM barang WHERE stok > stok_minimum')->fetchColumn(),
    'Menipis' => (int) $pdo->query('SELECT COUNT(*) FROM barang WHERE stok > 0 AND stok <= stok_minimum')->fetchColumn(),
    'Habis'   => (int) $pdo->query('SELECT COUNT(*) FROM barang WHERE stok <= 0')->fetchColumn(),
];

require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-3 mb-3">
  <?php
  $kartu = [
      ['Total Barang',  $total_barang,   'bi-box-seam',          'bg-navy'],
      ['Total Kategori',$total_kategori, 'bi-tags',              'bg-violet'],
      ['Total Stok',    $total_stok,     'bi-stack',             'bg-teal'],
      ['Barang Masuk',  $total_masuk,    'bi-box-arrow-in-down', 'bg-green'],
      ['Barang Keluar', $total_keluar,   'bi-box-arrow-up',      'bg-amber'],
      ['Total Pemasok', $total_pemasok,  'bi-truck',             'bg-red'],
  ];
  foreach ($kartu as $c): ?>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="card-ims stat-card">
        <div class="stat-icon <?= $c[3] ?>"><i class="bi <?= $c[2] ?>"></i></div>
        <div>
          <div class="stat-value"><?= number_format($c[1], 0, ',', '.') ?></div>
          <div class="stat-label"><?= e($c[0]) ?></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-xl-8">
    <div class="card-ims">
      <div class="card-title"><i class="bi bi-graph-up me-1"></i> Grafik Barang Masuk &amp; Keluar (6 bulan terakhir)</div>
      <canvas id="chartTransaksi" height="110"></canvas>
    </div>
  </div>
  <div class="col-12 col-xl-4">
    <div class="card-ims">
      <div class="card-title"><i class="bi bi-pie-chart me-1"></i> Kondisi Stok</div>
      <canvas id="chartKondisi" height="190"></canvas>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-12 col-xl-6">
    <div class="card-ims">
      <div class="card-title"><i class="bi bi-bar-chart me-1"></i> Jumlah Barang per Kategori</div>
      <canvas id="chartKategori" height="170"></canvas>
    </div>
  </div>
  <div class="col-12 col-xl-6">
    <div class="card-ims">
      <div class="card-title text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Barang Stok Menipis</div>
      <div class="table-wrap" style="box-shadow:none;border:none">
        <table class="table-ims">
          <thead><tr><th>Kode</th><th>Nama Barang</th><th>Stok</th><th>Min</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (!$stok_menipis): ?>
            <tr><td colspan="5" class="text-center text-muted">Semua stok aman.</td></tr>
          <?php else: foreach ($stok_menipis as $b):
            [$label, $warna] = status_stok((int) $b['stok'], (int) $b['stok_minimum']); ?>
            <tr>
              <td><?= e($b['kode_barang']) ?></td>
              <td><?= e($b['nama_barang']) ?></td>
              <td><?= (int) $b['stok'] ?></td>
              <td><?= (int) $b['stok_minimum'] ?></td>
              <td><span class="badge-soft badge-<?= $warna ?>"><?= $label ?></span></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-xl-5">
    <div class="card-ims">
      <div class="card-title"><i class="bi bi-clock me-1"></i> Barang Terbaru</div>
      <div class="table-wrap" style="box-shadow:none;border:none">
        <table class="table-ims">
          <thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Stok</th></tr></thead>
          <tbody>
          <?php foreach ($barang_terbaru as $b): ?>
            <tr>
              <td><?= e($b['kode_barang']) ?></td>
              <td><?= e($b['nama_barang']) ?></td>
              <td><?= e($b['nama_kategori']) ?></td>
              <td><?= (int) $b['stok'] ?> <?= e($b['satuan']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-7">
    <div class="card-ims">
      <div class="card-title"><i class="bi bi-activity me-1"></i> Aktivitas Transaksi Terbaru</div>
      <div class="table-wrap" style="box-shadow:none;border:none">
        <table class="table-ims">
          <thead><tr><th>Jenis</th><th>Kode</th><th>Barang</th><th>Jumlah</th><th>Tanggal</th><th>Petugas</th></tr></thead>
          <tbody>
          <?php foreach ($aktivitas as $a): ?>
            <tr>
              <td><span class="badge-soft <?= $a['jenis'] === 'Masuk' ? 'badge-success' : 'badge-warning' ?>"><?= e($a['jenis']) ?></span></td>
              <td><?= e($a['kode_transaksi']) ?></td>
              <td><?= e($a['nama_barang']) ?></td>
              <td><?= (int) $a['jumlah'] ?></td>
              <td><?= e(date('d/m/Y', strtotime($a['tanggal']))) ?></td>
              <td><?= e($a['nama_lengkap']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
const dataDashboard = {
  labels:   <?= json_encode($labels_bulan) ?>,
  masuk:    <?= json_encode($masuk_bulan) ?>,
  keluar:   <?= json_encode($keluar_bulan) ?>,
  katLabel: <?= json_encode(array_column($per_kategori, 'nama_kategori')) ?>,
  katData:  <?= json_encode(array_map('intval', array_column($per_kategori, 'jumlah'))) ?>,
  kondisi:  <?= json_encode(array_values($kondisi_stok)) ?>
};
</script>
<script>
function setupCanvas(canvas) {
  const ratio = window.devicePixelRatio || 1;
  const rect = canvas.getBoundingClientRect();
  canvas.width = rect.width * ratio;
  canvas.height = rect.height * ratio;
  const ctx = canvas.getContext('2d');
  ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
  return {ctx, w: rect.width, h: rect.height};
}

function drawLineChart(id, labels, masuk, keluar) {
  const canvas = document.getElementById(id);
  if (!canvas) return;
  canvas.style.width = '100%';
  canvas.style.height = '280px';
  const {ctx, w, h} = setupCanvas(canvas);
  ctx.clearRect(0,0,w,h);

  const pad = {l:45,r:20,t:25,b:45};
  const cw = w-pad.l-pad.r, ch = h-pad.t-pad.b;
  const max = Math.max(1, ...masuk, ...keluar);
  const yMax = Math.ceil(max / 5) * 5 || 5;

  ctx.font = '12px Arial';
  ctx.strokeStyle = '#e5e7eb';
  ctx.fillStyle = '#6b7280';
  ctx.lineWidth = 1;

  for (let i=0;i<=4;i++) {
    const y = pad.t + ch - (i/4)*ch;
    ctx.beginPath(); ctx.moveTo(pad.l,y); ctx.lineTo(w-pad.r,y); ctx.stroke();
    ctx.fillText(Math.round(yMax*i/4), 5, y+4);
  }

  const draw = (data, stroke, fill) => {
    const pts = data.map((v,i)=>({
      x: pad.l + (data.length===1 ? cw/2 : i*cw/(data.length-1)),
      y: pad.t + ch - (v/yMax)*ch
    }));
    ctx.beginPath();
    pts.forEach((p,i)=> i ? ctx.lineTo(p.x,p.y) : ctx.moveTo(p.x,p.y));
    ctx.strokeStyle = stroke; ctx.lineWidth=3; ctx.stroke();

    ctx.lineTo(pts[pts.length-1].x, pad.t+ch);
    ctx.lineTo(pts[0].x, pad.t+ch);
    ctx.closePath(); ctx.globalAlpha=.12; ctx.fillStyle=fill; ctx.fill(); ctx.globalAlpha=1;

    pts.forEach(p=>{
      ctx.beginPath(); ctx.arc(p.x,p.y,4,0,Math.PI*2);
      ctx.fillStyle=stroke; ctx.fill();
    });
  };

  draw(masuk, '#16a34a', '#16a34a');
  draw(keluar, '#f59e0b', '#f59e0b');

  ctx.fillStyle='#6b7280'; ctx.font='11px Arial';
  labels.forEach((lab,i)=>{
    const x = pad.l + (labels.length===1 ? cw/2 : i*cw/(labels.length-1));
    ctx.fillText(lab, x-22, h-15);
  });

  ctx.fillStyle='#16a34a'; ctx.fillRect(w-180,12,12,12);
  ctx.fillStyle='#374151'; ctx.fillText('Barang Masuk',w-162,22);
  ctx.fillStyle='#f59e0b'; ctx.fillRect(w-80,12,12,12);
  ctx.fillStyle='#374151'; ctx.fillText('Barang Keluar',w-62,22);
}

function drawDonutChart(id, labels, values) {
  const canvas=document.getElementById(id);
  if(!canvas) return;
  canvas.style.width='100%'; canvas.style.height='250px';
  const {ctx,w,h}=setupCanvas(canvas);
  ctx.clearRect(0,0,w,h);

  const total=values.reduce((a,b)=>a+b,0);
  const colors=['#16a34a','#f59e0b','#dc2626'];
  const cx=w/2, cy=105, r=70, inner=40;
  let angle=-Math.PI/2;

  if(total===0){
    ctx.beginPath(); ctx.arc(cx,cy,r,0,Math.PI*2);
    ctx.strokeStyle='#e5e7eb'; ctx.lineWidth=30; ctx.stroke();
  } else {
    values.forEach((v,i)=>{
      const next=angle+(v/total)*Math.PI*2;
      ctx.beginPath(); ctx.arc(cx,cy,r,angle,next);
      ctx.strokeStyle=colors[i]; ctx.lineWidth=30; ctx.stroke();
      angle=next;
    });
  }

  ctx.fillStyle='#374151'; ctx.font='bold 16px Arial'; ctx.textAlign='center';
  ctx.fillText(total, cx, cy+6);
  ctx.font='11px Arial'; ctx.fillStyle='#6b7280'; ctx.fillText('Total',cx,cy+23);

  ctx.textAlign='left';
  labels.forEach((lab,i)=>{
    const y=190+i*20;
    ctx.fillStyle=colors[i]; ctx.fillRect(18,y-9,10,10);
    ctx.fillStyle='#374151'; ctx.fillText(lab+' ('+values[i]+')',34,y);
  });
}

function drawBarChart(id, labels, values) {
  const canvas=document.getElementById(id);
  if(!canvas) return;
  canvas.style.width='100%'; canvas.style.height='250px';
  const {ctx,w,h}=setupCanvas(canvas);
  ctx.clearRect(0,0,w,h);

  const pad={l:40,r:15,t:20,b:55}, cw=w-pad.l-pad.r, ch=h-pad.t-pad.b;
  const max=Math.max(1,...values);
  const yMax=Math.max(1,Math.ceil(max/5)*5);

  ctx.font='11px Arial'; ctx.fillStyle='#6b7280'; ctx.strokeStyle='#e5e7eb';
  for(let i=0;i<=4;i++){
    const y=pad.t+ch-(i/4)*ch;
    ctx.beginPath();ctx.moveTo(pad.l,y);ctx.lineTo(w-pad.r,y);ctx.stroke();
    ctx.fillText(Math.round(yMax*i/4),5,y+4);
  }

  const slot=cw/Math.max(labels.length,1);
  values.forEach((v,i)=>{
    const bw=Math.max(10,slot*.55);
    const bh=(v/yMax)*ch;
    const x=pad.l+i*slot+(slot-bw)/2;
    const y=pad.t+ch-bh;
    ctx.fillStyle='#0ea5a4'; ctx.fillRect(x,y,bw,bh);
    ctx.fillStyle='#374151'; ctx.textAlign='center';
    ctx.fillText(String(v),x+bw/2,y-5);
    const label=String(labels[i]);
    ctx.fillStyle='#6b7280';
    ctx.fillText(label.length>12?label.slice(0,12)+'…':label,x+bw/2,h-18);
  });
  ctx.textAlign='left';
}

function renderDashboardCharts(){
  drawLineChart('chartTransaksi',dataDashboard.labels,dataDashboard.masuk,dataDashboard.keluar);
  drawDonutChart('chartKondisi',['Aman','Menipis','Habis'],dataDashboard.kondisi);
  drawBarChart('chartKategori',dataDashboard.katLabel,dataDashboard.katData);
}
window.addEventListener('load',renderDashboardCharts);
window.addEventListener('resize',renderDashboardCharts);
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
