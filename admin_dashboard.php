<?php
session_start();

if(empty($_SESSION['admin_login'])){
  header('Location: login.php');
  exit;
}

include 'koneksi.php';

function rupiah_admin($value){
  return 'Rp' . number_format((int)$value, 0, ',', '.');
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $action = $_POST['action'] ?? '';

  if($action === 'verify_payment'){
    $id_booking = (int)($_POST['id_booking'] ?? 0);
    if($id_booking > 0){
      mysqli_begin_transaction($conn);
      try{
        $statusPay = 'sukses';
        $statusBooking = 'dibooking';
        $stmtP = mysqli_prepare($conn, "UPDATE pembayaran SET status=? WHERE id_booking=?");
        mysqli_stmt_bind_param($stmtP, 'si', $statusPay, $id_booking);
        mysqli_stmt_execute($stmtP);

        $stmtB = mysqli_prepare($conn, "UPDATE booking SET status=? WHERE id_booking=?");
        mysqli_stmt_bind_param($stmtB, 'si', $statusBooking, $id_booking);
        mysqli_stmt_execute($stmtB);
        mysqli_commit($conn);
        $_SESSION['admin_notice'] = 'Pembayaran berhasil diverifikasi.';
      }catch(Exception $e){
        mysqli_rollback($conn);
        $_SESSION['admin_notice'] = 'Gagal verifikasi pembayaran.';
      }
    }
    header('Location: admin_dashboard.php');
    exit;
  }

  if($action === 'save_lapangan'){
    $id_lapangan = (int)($_POST['id_lapangan'] ?? 0);
    $nama_lapangan = trim($_POST['nama_lapangan'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $harga = (int)preg_replace('/[^0-9]/', '', $_POST['harga'] ?? '0');

    if($nama_lapangan !== '' && $lokasi !== '' && $harga > 0){
      if($id_lapangan > 0){
        $stmt = mysqli_prepare($conn, "UPDATE lapangan SET nama_lapangan=?, lokasi=?, harga=? WHERE id_lapangan=?");
        mysqli_stmt_bind_param($stmt, 'ssii', $nama_lapangan, $lokasi, $harga, $id_lapangan);
      }else{
        $stmt = mysqli_prepare($conn, "INSERT INTO lapangan(nama_lapangan,lokasi,harga) VALUES(?,?,?)");
        mysqli_stmt_bind_param($stmt, 'ssi', $nama_lapangan, $lokasi, $harga);
      }
      mysqli_stmt_execute($stmt);
      $_SESSION['admin_notice'] = 'Data lapangan berhasil disimpan.';
    }
    header('Location: admin_dashboard.php');
    exit;
  }

  if($action === 'delete_lapangan'){
    $id_lapangan = (int)($_POST['id_lapangan'] ?? 0);
    if($id_lapangan > 0){
      $stmt = mysqli_prepare($conn, "DELETE FROM lapangan WHERE id_lapangan=?");
      mysqli_stmt_bind_param($stmt, 'i', $id_lapangan);
      mysqli_stmt_execute($stmt);
      $_SESSION['admin_notice'] = 'Data lapangan berhasil dihapus.';
    }
    header('Location: admin_dashboard.php');
    exit;
  }
}

$notice = $_SESSION['admin_notice'] ?? '';
unset($_SESSION['admin_notice']);

$today = date('Y-m-d');
$month = date('Y-m');
$year = date('Y');

$totalLapangan = (int)(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM lapangan"))['total'] ?? 0);
$bookingHariIni = (int)(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM booking WHERE tanggal='$today'"))['total'] ?? 0);
$bookingMenunggu = (int)(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM pembayaran WHERE status='menunggu'"))['total'] ?? 0);
$pendapatanTotal = (int)(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(jumlah),0) total FROM pembayaran WHERE status='sukses'"))['total'] ?? 0);
$pendapatanHarian = (int)(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(jumlah),0) total FROM pembayaran WHERE status='sukses' AND tanggal_pembayaran='$today'"))['total'] ?? 0);
$pendapatanBulanan = (int)(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(jumlah),0) total FROM pembayaran WHERE status='sukses' AND DATE_FORMAT(tanggal_pembayaran,'%Y-%m')='$month'"))['total'] ?? 0);
$pendapatanTahunan = (int)(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(jumlah),0) total FROM pembayaran WHERE status='sukses' AND DATE_FORMAT(tanggal_pembayaran,'%Y')='$year'"))['total'] ?? 0);

$pendingBookings = mysqli_query($conn, "
  SELECT b.id_booking,b.tanggal,b.jam,u.nama,u.email,l.nama_lapangan,p.jumlah,p.tanggal_pembayaran,p.jam_pembayaran,p.status
  FROM booking b
  JOIN users u ON u.id_user=b.id_user
  JOIN lapangan l ON l.id_lapangan=b.id_lapangan
  JOIN pembayaran p ON p.id_booking=b.id_booking
  WHERE p.status='menunggu'
  ORDER BY b.id_booking DESC
");

$recentBookings = mysqli_query($conn, "
  SELECT b.id_booking,b.tanggal,b.jam,b.status AS status_booking,u.nama,l.nama_lapangan,p.jumlah,p.status AS status_pembayaran
  FROM booking b
  LEFT JOIN users u ON u.id_user=b.id_user
  LEFT JOIN lapangan l ON l.id_lapangan=b.id_lapangan
  LEFT JOIN pembayaran p ON p.id_booking=b.id_booking
  ORDER BY b.id_booking DESC
  LIMIT 8
");

$lapanganRows = mysqli_query($conn, "SELECT * FROM lapangan ORDER BY id_lapangan DESC");
$chartRows = mysqli_query($conn, "
  SELECT tanggal_pembayaran, COALESCE(SUM(jumlah),0) total
  FROM pembayaran
  WHERE status='sukses'
  GROUP BY tanggal_pembayaran
  ORDER BY tanggal_pembayaran DESC
  LIMIT 7
");
$chartLabels = [];
$chartValues = [];
while($row = $chartRows ? mysqli_fetch_assoc($chartRows) : null){
  if(!$row) break;
  array_unshift($chartLabels, $row['tanggal_pembayaran']);
  array_unshift($chartValues, (int)$row['total']);
}

$chartWidth = 720;
$chartHeight = 220;
$chartPadX = 34;
$chartPadY = 28;
$chartMax = max($chartValues ?: [1]);
$chartCount = count($chartValues);
$chartPoints = [];
$chartPolyline = '';
$chartPath = '';
$chartAreaPath = '';
foreach($chartValues as $idx => $value){
  $x = $chartCount > 1
    ? $chartPadX + (($chartWidth - ($chartPadX * 2)) * ($idx / ($chartCount - 1)))
    : $chartWidth / 2;
  $y = ($chartHeight - $chartPadY) - (($value / $chartMax) * ($chartHeight - ($chartPadY * 2)));
  $chartPoints[] = [
    'x' => round($x, 2),
    'y' => round($y, 2),
    'value' => $value,
    'label' => $chartLabels[$idx] ?? '',
  ];
}
$chartPolyline = implode(' ', array_map(function($point){
  return $point['x'] . ',' . $point['y'];
}, $chartPoints));
if(count($chartPoints) === 1){
  $point = $chartPoints[0];
  $lineHalf = 70;
  $chartPath = 'M ' . max($chartPadX, $point['x'] - $lineHalf) . ' ' . $point['y'] . ' L ' . min($chartWidth - $chartPadX, $point['x'] + $lineHalf) . ' ' . $point['y'];
  $chartAreaPath = 'M ' . max($chartPadX, $point['x'] - $lineHalf) . ' ' . ($chartHeight - $chartPadY) . ' L ' . max($chartPadX, $point['x'] - $lineHalf) . ' ' . $point['y'] . ' L ' . min($chartWidth - $chartPadX, $point['x'] + $lineHalf) . ' ' . $point['y'] . ' L ' . min($chartWidth - $chartPadX, $point['x'] + $lineHalf) . ' ' . ($chartHeight - $chartPadY) . ' Z';
} elseif(count($chartPoints) > 1){
  $chartPath = 'M ' . $chartPoints[0]['x'] . ' ' . $chartPoints[0]['y'];
  for($i = 1; $i < count($chartPoints); $i++){
    $prev = $chartPoints[$i - 1];
    $current = $chartPoints[$i];
    $midX = ($prev['x'] + $current['x']) / 2;
    $chartPath .= ' C ' . $midX . ' ' . $prev['y'] . ', ' . $midX . ' ' . $current['y'] . ', ' . $current['x'] . ' ' . $current['y'];
  }
  $first = $chartPoints[0];
  $last = $chartPoints[count($chartPoints) - 1];
  $chartAreaPath = 'M ' . $first['x'] . ' ' . ($chartHeight - $chartPadY) . ' L ' . $first['x'] . ' ' . $first['y'];
  for($i = 1; $i < count($chartPoints); $i++){
    $prev = $chartPoints[$i - 1];
    $current = $chartPoints[$i];
    $midX = ($prev['x'] + $current['x']) / 2;
    $chartAreaPath .= ' C ' . $midX . ' ' . $prev['y'] . ', ' . $midX . ' ' . $current['y'] . ', ' . $current['x'] . ' ' . $current['y'];
  }
  $chartAreaPath .= ' L ' . $last['x'] . ' ' . ($chartHeight - $chartPadY) . ' Z';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin FootballZone</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',sans-serif;}
body{min-height:100vh;background:#07160e;color:white;padding:22px;}
a{color:#9dffb2;text-decoration:none;font-weight:900;}
.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:12px;}
h1{color:#9dffb2;font-size:26px;}
.muted{color:rgba(255,255,255,.7);font-size:13px;}
.grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:14px;}
.card,.panel{background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.10);border-radius:8px;padding:14px;}
.card{cursor:default;}
.card strong{display:block;color:#9dffb2;font-size:24px;margin-top:8px;}
.panel{margin-bottom:14px;}
.panel h2{font-size:18px;color:#9dffb2;margin-bottom:12px;}
table{width:100%;border-collapse:collapse;}
th,td{padding:10px;border-bottom:1px solid rgba(255,255,255,.08);text-align:left;font-size:13px;vertical-align:top;}
th{color:#9dffb2;}
.btn{border:0;border-radius:8px;padding:9px 12px;font-weight:900;cursor:pointer;background:#43c465;color:white;}
.btn-danger{background:#cc2f2f;}
.form-grid{display:grid;grid-template-columns:1.2fr 1.4fr .8fr auto;gap:10px;align-items:end;}
input{width:100%;padding:11px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.06);color:white;}
label{display:block;font-size:12px;color:rgba(255,255,255,.72);font-weight:900;margin-bottom:6px;}
.notice{padding:12px 14px;border-radius:8px;background:rgba(67,196,101,.12);border:1px solid rgba(67,196,101,.25);margin-bottom:14px;}
.income-detail{display:none;margin-top:10px;color:rgba(255,255,255,.82);line-height:1.7;}
.income-card.is-open .income-detail{display:block;}
.line-chart{width:100%;height:260px;border-top:1px solid rgba(255,255,255,.08);padding-top:14px;}
.line-chart svg{width:100%;height:230px;display:block;overflow:visible;}
.chart-grid{stroke:rgba(255,255,255,.08);stroke-width:1;}
.chart-line{fill:none;stroke:#9dffb2;stroke-width:4;stroke-linecap:round;stroke-linejoin:round;}
.chart-area{fill:rgba(67,196,101,.12);}
.chart-point{fill:#07160e;stroke:#9dffb2;stroke-width:4;}
.chart-label{fill:rgba(255,255,255,.66);font-size:12px;}
.chart-value{fill:#e8ffe8;font-size:12px;font-weight:800;}
@media(max-width:900px){.grid{grid-template-columns:1fr 1fr}.form-grid{grid-template-columns:1fr}body{padding:14px;}}
</style>
</head>
<body>
<div class="top">
  <div>
    <h1>Admin FootballZone</h1>
    <div class="muted">Login sebagai <?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'admin'); ?></div>
  </div>
  <a href="admin_logout.php">Logout</a>
</div>

<?php if($notice): ?><div class="notice"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>

<div class="grid">
  <div class="card"><div>Total Lapangan</div><strong><?php echo $totalLapangan; ?></strong></div>
  <div class="card"><div>Booking Hari Ini</div><strong><?php echo $bookingHariIni; ?></strong></div>
  <div class="card income-card" id="incomeCard"><div>Pendapatan</div><strong><?php echo rupiah_admin($pendapatanTotal); ?></strong><div class="muted">Klik untuk detail</div><div class="income-detail">Harian: <b><?php echo rupiah_admin($pendapatanHarian); ?></b><br>Bulanan: <b><?php echo rupiah_admin($pendapatanBulanan); ?></b><br>Tahunan: <b><?php echo rupiah_admin($pendapatanTahunan); ?></b></div></div>
  <div class="card"><div>Booking Menunggu</div><strong><?php echo $bookingMenunggu; ?></strong></div>
</div>

<div class="panel">
  <h2>Verifikasi Pembayaran</h2>
  <table>
    <thead><tr><th>User</th><th>Lapangan</th><th>Jadwal</th><th>Nominal</th><th>Pembayaran</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php if($pendingBookings && mysqli_num_rows($pendingBookings) > 0): ?>
      <?php while($p = mysqli_fetch_assoc($pendingBookings)): ?>
      <tr>
        <td><b><?php echo htmlspecialchars($p['nama']); ?></b><br><span class="muted"><?php echo htmlspecialchars($p['email']); ?></span></td>
        <td><?php echo htmlspecialchars($p['nama_lapangan']); ?></td>
        <td><?php echo htmlspecialchars($p['tanggal'].' '.$p['jam']); ?></td>
        <td><?php echo rupiah_admin($p['jumlah']); ?></td>
        <td><?php echo htmlspecialchars(($p['tanggal_pembayaran'] ?? '-').' '.($p['jam_pembayaran'] ?? '-')); ?></td>
        <td><?php echo htmlspecialchars($p['status']); ?></td>
        <td><form method="POST"><input type="hidden" name="action" value="verify_payment"><input type="hidden" name="id_booking" value="<?php echo (int)$p['id_booking']; ?>"><button class="btn" type="submit">Verifikasi</button></form></td>
      </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="7" class="muted">Tidak ada pembayaran menunggu.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="panel">
  <h2>Grafik Pendapatan</h2>
  <div class="line-chart">
    <?php if(!empty($chartPoints)): ?>
    <svg viewBox="0 0 <?php echo $chartWidth; ?> <?php echo $chartHeight; ?>" role="img" aria-label="Grafik pendapatan">
      <line class="chart-grid" x1="<?php echo $chartPadX; ?>" y1="<?php echo $chartHeight - $chartPadY; ?>" x2="<?php echo $chartWidth - $chartPadX; ?>" y2="<?php echo $chartHeight - $chartPadY; ?>"></line>
      <line class="chart-grid" x1="<?php echo $chartPadX; ?>" y1="<?php echo $chartPadY; ?>" x2="<?php echo $chartPadX; ?>" y2="<?php echo $chartHeight - $chartPadY; ?>"></line>
      <?php if($chartAreaPath !== ''): ?><path class="chart-area" d="<?php echo htmlspecialchars($chartAreaPath); ?>"></path><?php endif; ?>
      <?php if($chartPath !== ''): ?><path class="chart-line" d="<?php echo htmlspecialchars($chartPath); ?>"></path><?php endif; ?>
      <?php foreach($chartPoints as $point): ?>
        <circle class="chart-point" cx="<?php echo $point['x']; ?>" cy="<?php echo $point['y']; ?>" r="5">
          <title><?php echo htmlspecialchars($point['label'] . ' - ' . rupiah_admin($point['value'])); ?></title>
        </circle>
        <text class="chart-value" x="<?php echo $point['x']; ?>" y="<?php echo max(14, $point['y'] - 12); ?>" text-anchor="middle"><?php echo htmlspecialchars(rupiah_admin($point['value'])); ?></text>
        <text class="chart-label" x="<?php echo $point['x']; ?>" y="<?php echo $chartHeight - 6; ?>" text-anchor="middle"><?php echo htmlspecialchars(substr($point['label'], 5)); ?></text>
      <?php endforeach; ?>
    </svg>
    <?php else: ?>
      <div class="muted">Belum ada pendapatan sukses.</div>
    <?php endif; ?>
  </div>
</div>

<div class="panel">
  <h2>Kelola Data Lapangan</h2>
  <form method="POST" class="form-grid">
    <input type="hidden" name="action" value="save_lapangan">
    <input type="hidden" name="id_lapangan" id="id_lapangan" value="">
    <div><label>Nama Lapangan</label><input name="nama_lapangan" id="nama_lapangan" required></div>
    <div><label>Lokasi</label><input name="lokasi" id="lokasi" required></div>
    <div><label>Harga</label><input name="harga" id="harga" required></div>
    <button class="btn" type="submit">Simpan</button>
  </form>
  <table style="margin-top:14px">
    <thead><tr><th>Nama</th><th>Lokasi</th><th>Harga</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php while($l = $lapanganRows ? mysqli_fetch_assoc($lapanganRows) : null): ?>
      <tr>
        <td><?php echo htmlspecialchars($l['nama_lapangan']); ?></td>
        <td><?php echo htmlspecialchars($l['lokasi']); ?></td>
        <td><?php echo rupiah_admin($l['harga']); ?></td>
        <td>
          <button class="btn" type="button" onclick='editLapangan(<?php echo json_encode($l); ?>)'>Edit</button>
          <form method="POST" style="display:inline" onsubmit="return confirm('Hapus lapangan ini?')"><input type="hidden" name="action" value="delete_lapangan"><input type="hidden" name="id_lapangan" value="<?php echo (int)$l['id_lapangan']; ?>"><button class="btn btn-danger" type="submit">Hapus</button></form>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>

<div class="panel">
  <h2>User Booking Terbaru</h2>
  <table>
    <thead><tr><th>User</th><th>Lapangan</th><th>Jadwal</th><th>Nominal</th><th>Status</th></tr></thead>
    <tbody>
    <?php while($r = $recentBookings ? mysqli_fetch_assoc($recentBookings) : null): ?>
      <tr>
        <td><?php echo htmlspecialchars($r['nama'] ?? '-'); ?></td>
        <td><?php echo htmlspecialchars($r['nama_lapangan'] ?? '-'); ?></td>
        <td><?php echo htmlspecialchars(($r['tanggal'] ?? '-').' '.($r['jam'] ?? '-')); ?></td>
        <td><?php echo rupiah_admin($r['jumlah'] ?? 0); ?></td>
        <td><?php echo htmlspecialchars(($r['status_pembayaran'] ?? '-').' / '.($r['status_booking'] ?? '-')); ?></td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
document.getElementById('incomeCard').addEventListener('click', function(){ this.classList.toggle('is-open'); });
function editLapangan(row){
  document.getElementById('id_lapangan').value = row.id_lapangan || '';
  document.getElementById('nama_lapangan').value = row.nama_lapangan || '';
  document.getElementById('lokasi').value = row.lokasi || '';
  document.getElementById('harga').value = row.harga || '';
  window.scrollTo({top: document.querySelector('.panel:nth-of-type(3)').offsetTop, behavior:'smooth'});
}
</script>
</body>
</html>
