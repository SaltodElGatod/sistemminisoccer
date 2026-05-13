<?php
session_start();

if(!isset($_SESSION['id_user'])){
  header('Location: login.php');
  exit;
}

include 'koneksi.php';

$id_user = (int)$_SESSION['id_user'];
$nama = $_SESSION['nama'] ?? '';

// Ambil booking user beserta lapangan
// Karena kolom jam_pembayaran bisa saja belum ada di DB, kita deteksi dulu.
$hasJamPembayaran = false;
$colCheckRes = mysqli_query($conn, "SHOW COLUMNS FROM pembayaran LIKE 'jam_pembayaran'");
if($colCheckRes && mysqli_num_rows($colCheckRes) > 0){
  $hasJamPembayaran = true;
} else {
  mysqli_query($conn, "ALTER TABLE pembayaran ADD jam_pembayaran VARCHAR(5) NULL AFTER tanggal_pembayaran");
  $colCheckRes = mysqli_query($conn, "SHOW COLUMNS FROM pembayaran LIKE 'jam_pembayaran'");
  if($colCheckRes && mysqli_num_rows($colCheckRes) > 0){
    $hasJamPembayaran = true;
  }
}

$hasKembalian = false;
$colKembalianRes = mysqli_query($conn, "SHOW COLUMNS FROM pembayaran LIKE 'kembalian'");
if($colKembalianRes && mysqli_num_rows($colKembalianRes) > 0){
  $hasKembalian = true;
} else {
  mysqli_query($conn, "ALTER TABLE pembayaran ADD kembalian INT(11) NULL AFTER jam_pembayaran");
  $colKembalianRes = mysqli_query($conn, "SHOW COLUMNS FROM pembayaran LIKE 'kembalian'");
  if($colKembalianRes && mysqli_num_rows($colKembalianRes) > 0){
    $hasKembalian = true;
  }
}

$jamSelect = $hasJamPembayaran ? '  p.jam_pembayaran' : '  NULL AS jam_pembayaran';
$kembalianSelect = $hasKembalian ? '  p.kembalian' : '  NULL AS kembalian';

$sql = "
SELECT 
  b.id_booking,
  b.tanggal,
  b.jam,
  b.status,
  l.nama_lapangan,
  l.lokasi,
  l.harga,
  p.jumlah,
  p.status AS status_pembayaran,
  p.tanggal_pembayaran,
".$jamSelect.",
".$kembalianSelect." 
FROM booking b
JOIN lapangan l ON l.id_lapangan = b.id_lapangan
LEFT JOIN pembayaran p ON p.id_booking = b.id_booking
WHERE b.id_user = ?
ORDER BY b.tanggal DESC, b.id_booking DESC
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id_user);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$rows = [];
while($row = $res ? mysqli_fetch_assoc($res) : null){
  if(!$row) break;
  $rows[] = $row;
}

function format_rupiah($value){
  if($value === null || $value === ''){
    return '-';
  }

  if(is_string($value) && preg_match('/^Rp/i', trim($value))){
    return $value;
  }

  $number = (int)preg_replace('/[^0-9\-]/', '', (string)$value);
  return 'Rp' . number_format($number, 0, ',', '.');
}

$bookingCancelSuccess = $_SESSION['booking_cancel_success'] ?? false;
unset($_SESSION['booking_cancel_success']);

$bookingDeleteSuccess = $_SESSION['booking_delete_success'] ?? false;
$bookingDeleteError = $_SESSION['booking_delete_error'] ?? '';
unset($_SESSION['booking_delete_success'], $_SESSION['booking_delete_error']);

$bookingSuccess = $_SESSION['booking_success'] ?? false;
$bookingSuccessMessage = $_SESSION['booking_success_message'] ?? '';
unset($_SESSION['booking_success'], $_SESSION['booking_success_message']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Riwayat Booking</title>
  <link rel="stylesheet" href="assets/page-transition.css">
  <script defer src="assets/page-transition.js"></script>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
    body{min-height:100vh;display:flex;justify-content:center;align-items:flex-start;background:rgba(0,0,0,0.65);padding:24px;color:white;}
    .wrap{width:min(1000px,96vw);}
    h1{color:#9dffb2;margin-bottom:10px;}
    .muted{color:rgba(255,255,255,0.75);margin-bottom:18px;}
    table{width:100%;border-collapse:collapse;background:rgba(13,33,18,0.88);border:1px solid rgba(67,196,101,0.20);border-radius:18px;overflow:hidden;}
    th,td{padding:12px 10px;border-bottom:1px solid rgba(255,255,255,0.08);vertical-align:top;}
    th{color:#9dffb2;font-size:13px;font-weight:1000;text-align:left;}
    td{font-size:13.5px;}
    tr:last-child td{border-bottom:none;}
    tbody tr[data-id-booking]{cursor:pointer;transition:background .18s ease, transform .18s ease;}
    tbody tr[data-id-booking]:hover{background:rgba(255,255,255,0.055);}
    .tag{display:inline-block;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.10);font-weight:1000;}
    .actions{white-space:nowrap;}
    .btn{padding:10px 12px;border:none;border-radius:14px;cursor:pointer;font-weight:1000;}
    .btn-ghost{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.10);color:#e8ffe8;}
    .btn-danger{background:linear-gradient(135deg,#ff5b5b,#cc2f2f);color:white;}
    .topbar{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px;}
    .topbar a{color:#9dffb2;text-decoration:none; font-weight:1000;}
    .notice{margin:12px 0;padding:12px 14px;border-radius:16px;border:1px solid rgba(67,196,101,0.25);background:rgba(67,196,101,0.12);color:#e8ffe8;}
    .confirm-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,0.62);z-index:9999;padding:18px;}
    .confirm-modal.is-open{display:flex;}
    .confirm-box{width:min(380px,94vw);background:rgba(7,22,14,0.98);border:1px solid rgba(67,196,101,0.28);border-radius:20px;padding:18px;box-shadow:0 18px 70px rgba(0,0,0,0.55);}
    .confirm-title{font-size:18px;font-weight:1000;color:#9dffb2;margin-bottom:8px;}
    .confirm-text{color:rgba(255,255,255,0.78);font-size:13.5px;line-height:1.5;margin-bottom:16px;}
    .confirm-actions{display:flex;justify-content:flex-end;gap:10px;}
    .btn-delete{background:linear-gradient(135deg,#ff5b5b,#cc2f2f);color:white;}
  </style>
</head>
<body>
<div class="wrap">
<div class="topbar">
    <div>
      <h1>Riwayat Booking</h1>
      <div class="muted">Halo, <?php echo htmlspecialchars($nama); ?></div>
    </div>
    <div>
      <a href="dashboard.php">← Kembali ke Dashboard</a>
    </div>
  </div>

  <div class="notice" style="background:rgba(255,255,255,0.04);">
    <b>Update:</b> Saat booking sudah dibayar, status akan berubah sehingga slot muncul “Sudah dibooking” di popup.
  </div>

  <?php if($bookingSuccess && $bookingSuccessMessage): ?>
    <div class="notice"><?php echo htmlspecialchars($bookingSuccessMessage); ?></div>
  <?php endif; ?>

  <?php if($bookingCancelSuccess): ?>
    <div class="notice">Booking berhasil dibatalkan.</div>
  <?php endif; ?>

  <?php if($bookingDeleteSuccess): ?>
    <div class="notice">Riwayat booking berhasil dihapus.</div>
  <?php endif; ?>

  <?php if($bookingDeleteError): ?>
    <div class="notice" style="border-color:rgba(255,91,91,0.35);background:rgba(255,91,91,0.12);">
      <?php echo htmlspecialchars($bookingDeleteError); ?>
    </div>
  <?php endif; ?>

  <?php
  $dbg = $_SESSION['booking_debug'] ?? null;
  $dbgResolve = $_SESSION['booking_debug_resolve_input'] ?? null;
  $dbgLast = null;

  // Tampilkan debug bila ada (penting untuk kasus riwayat kosong)
  if($dbg || $dbgResolve || $dbgLast){
    echo '<!-- booking_last_debug exists: '.(empty($dbgLast)?'0':'1').' -->';
    unset($_SESSION['booking_debug'], $_SESSION['booking_debug_resolve_input']);
    echo '<div class="notice" style="background:rgba(255,255,255,0.04); border-color:rgba(255,255,255,0.12;">';
    if($dbg){
      echo '<b>Debug booking:</b><br>';
      echo '<pre style="white-space:pre-wrap; margin-top:8px; color:#fff;">'.htmlspecialchars(print_r($dbg,true)).'</pre>';
    }
    if($dbgResolve){
      echo '<b>Debug resolve input:</b><br>';
      echo '<pre style="white-space:pre-wrap; margin-top:8px; color:#fff;">'.htmlspecialchars(print_r($dbgResolve,true)).'</pre>';
    }
    echo '</div>';
  }
  ?>




  <table>
    <thead>
      <tr>
        <th>Tanggal Pembayaran</th>
        <th>Jam Pembayaran</th>
        <th>Lapangan</th>
        <th>Harga</th>
        <th>Nominal Bayar</th>
        <th>Kembalian</th>
        <th>Status</th>
      </tr>
    </thead>

<tbody>
      <?php
      if(isset($_GET['refresh'])){
        unset($_SESSION['booking_success'], $_SESSION['booking_success_message']);
      }
      ?>
<?php
$displayRows = [];
if(!empty($rows)){
  foreach($rows as $row){
    $hargaAngka = (int)preg_replace('/[^0-9]/', '', (string)($row['harga'] ?? '0'));
    $jumlahAngka = (int)preg_replace('/[^0-9]/', '', (string)($row['jumlah'] ?? '0'));
    $kembalianDb = $row['kembalian'] ?? null;
    $kembalianAngka = ($kembalianDb !== null && $kembalianDb !== '')
      ? (int)$kembalianDb
      : max(0, $jumlahAngka - $hargaAngka);

    $displayRows[] = [
      'id_booking' => $row['id_booking'] ?? 0,
      'tanggal_pembayaran' => $row['tanggal_pembayaran'] ?? ($row['tanggal'] ?? '-'),
      'jam_pembayaran' => !empty($row['jam_pembayaran']) ? $row['jam_pembayaran'] : '-',
      'nama_lapangan' => $row['nama_lapangan'] ?? '-',
      'lokasi' => $row['lokasi'] ?? '',
      'harga' => format_rupiah($row['harga'] ?? 0),
      'jumlah' => $jumlahAngka > 0 ? format_rupiah($jumlahAngka) : '-',
      'kembalian' => format_rupiah($kembalianAngka),
      'status_pembayaran' => $row['status_pembayaran'] ?? '',
      'status' => $row['status'] ?? '',
    ];
  }
}
?>

<?php if(!empty($displayRows)): ?>

  <?php foreach($displayRows as $r): ?>

    <?php
      $tanggalBayar = $r['tanggal_pembayaran'] ?? '-';
      $jamBayar = $r['jam_pembayaran'] ?? '-';

      $statusBooking = strtolower((string)($r['status'] ?? ''));
      $statusPembayaran = strtolower((string)($r['status_pembayaran'] ?? ''));

      $statusFinal = 'pending';
      if($statusPembayaran === 'sukses' || $statusBooking === 'sukses' || $statusBooking === 'sudah dibooking' || $statusBooking === 'dibooking'){
        $statusFinal = 'sukses';
      } elseif($statusPembayaran === 'menunggu' || $statusBooking === 'menunggu'){
        $statusFinal = 'menunggu';
      } elseif($statusBooking === 'gagal dibookin'){
        $statusFinal = 'gagal';
      } elseif($statusBooking === 'dibatalkan'){
        $statusFinal = 'gagal';
      }
    ?>

    <tr <?php if(!empty($r['id_booking'])): ?>data-id-booking="<?php echo (int)$r['id_booking']; ?>"<?php endif; ?>>
      <td><?php echo htmlspecialchars((string)$tanggalBayar); ?></td>
      <td><?php echo htmlspecialchars((string)$jamBayar); ?></td>
      <td><b><?php echo htmlspecialchars((string)($r['nama_lapangan'] ?? '-')); ?></b></td>
      <td><?php echo htmlspecialchars((string)($r['harga'] ?? '-')); ?></td>
      <td><?php echo htmlspecialchars((string)($r['jumlah'] ?? '-')); ?></td>
      <td><?php echo htmlspecialchars((string)($r['kembalian'] ?? '-')); ?></td>
      <td>
        <span class="tag">
          <?php echo htmlspecialchars($statusFinal); ?>
        </span>
      </td>
    </tr>

  <?php endforeach; ?>

<?php else: ?>

  <tr>
    <td colspan="7" style="padding:18px;color:rgba(255,255,255,0.75);">
      Belum ada booking.
    </td>
  </tr>

<?php endif; ?>
    </tbody>
  </table>

</div>

<div class="confirm-modal" id="deleteConfirm" aria-hidden="true">
  <div class="confirm-box" role="dialog" aria-modal="true" aria-labelledby="deleteConfirmTitle">
    <div class="confirm-title" id="deleteConfirmTitle">Hapus riwayat ini?</div>
    <div class="confirm-text">Data booking yang dipilih akan dihapus dari riwayat.</div>
    <form method="POST" action="booking_delete.php" id="deleteForm">
      <input type="hidden" name="id_booking" id="deleteBookingId" value="">
      <div class="confirm-actions">
        <button type="button" class="btn btn-ghost" id="deleteNo">Tidak</button>
        <button type="submit" class="btn btn-delete">Ya</button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  const modal = document.getElementById('deleteConfirm');
  const input = document.getElementById('deleteBookingId');
  const btnNo = document.getElementById('deleteNo');

  function closeModal(){
    if(!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    if(input) input.value = '';
  }

  document.querySelectorAll('tbody tr[data-id-booking]').forEach(row => {
    row.addEventListener('click', function(){
      if(!modal || !input) return;
      input.value = this.getAttribute('data-id-booking') || '';
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
    });
  });

  btnNo && btnNo.addEventListener('click', closeModal);
  modal && modal.addEventListener('click', function(e){
    if(e.target === modal) closeModal();
  });
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') closeModal();
  });
})();
</script>
</body>
</html>


