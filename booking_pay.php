<?php
session_start();

if(!isset($_SESSION['id_user'])){
  header('Location: login.php');
  exit;
}

include 'koneksi.php';

// id_user tidak dipakai untuk perhitungan urutan riwayat
// (riwayat akan tetap mengikuti query booking_list berdasarkan id_user yang valid)
$id_user = (int)$_SESSION['id_user'];


$id_lapangan = (int)($_POST['id_lapangan'] ?? 0);
// fallback ambil dari GET ketika hidden tidak terisi sempurna
if($id_lapangan === 0){
  $id_lapangan = (int)($_GET['id_lapangan'] ?? 0);
}

// fallback terakhir: coba ambil dari session ketika user datang dari checkout
if($id_lapangan === 0){
  $id_lapangan = (int)($_SESSION['last_id_lapangan'] ?? 0);
}

$lapangan_name = trim((string)($_POST['lapangan'] ?? ''));

$jadwal_jam = (string)($_POST['jadwal'] ?? '');
$harga_text = (string)($_POST['harga'] ?? '0');


// parsing angka dari Rp500.000
$expectedJumlah = (int)preg_replace('/[^0-9]/', '', $harga_text);
if($expectedJumlah <= 0){
  $expectedJumlah = 0;
}

$nominal_input = (string)($_POST['nominal_input'] ?? '');
$inputJumlah = (int)preg_replace('/[^0-9]/', '', $nominal_input);
if($inputJumlah <= 0){
  $inputJumlah = 0;
}

$today = date('Y-m-d');
$jadwal_jam = trim($jadwal_jam);

// Validasi simulasi pembayaran: sukses jika nominal >= harga sewa
$paymentSuccess = ($expectedJumlah > 0 && $inputJumlah >= $expectedJumlah);
$status_booking = $paymentSuccess ? 'menunggu' : 'dibatalkan';
$status_pembayaran = $paymentSuccess ? 'menunggu' : 'gagal';
$jumlah = $inputJumlah;
$redirect_to = (string)($_POST['redirect_to'] ?? 'booking_list');

function normalize_booking_text($text){
  $text = strtolower((string)$text);
  $text = str_replace(['â€™','â€˜','`','’','‘'], "'", $text);
  $text = preg_replace('/[^a-z0-9]+/i', ' ', $text);
  return trim(preg_replace('/\s+/', ' ', $text));
}

// Pastikan id_lapangan benar-benar ada di tabel lapangan.
// Dashboard lama mengirim id berdasarkan urutan array, padahal id database bisa berbeda.
if($id_lapangan > 0){
  $stmtCheckLap = mysqli_prepare($conn, "SELECT id_lapangan,nama_lapangan FROM lapangan WHERE id_lapangan=? LIMIT 1");
  if($stmtCheckLap){
    mysqli_stmt_bind_param($stmtCheckLap, 'i', $id_lapangan);
    mysqli_stmt_execute($stmtCheckLap);
    $checkLapRes = mysqli_stmt_get_result($stmtCheckLap);
    if(!$checkLapRes || mysqli_num_rows($checkLapRes) === 0){
      $id_lapangan = 0;
    } else {
      $lapCheck = mysqli_fetch_assoc($checkLapRes);
      $dbLapanganName = $lapCheck['nama_lapangan'] ?? '';
      if($lapangan_name !== '' && normalize_booking_text($dbLapanganName) !== normalize_booking_text($lapangan_name)){
        $id_lapangan = 0;
      }
    }
  } else {
    $id_lapangan = 0;
  }
}

// Resolve id_lapangan dari nama jika hidden input kosong atau ID dari dashboard tidak valid.
if($id_lapangan === 0 && $lapangan_name !== ''){
  // normalisasi nama untuk menghindari mismatch karena spasi/karakter formatting
  $lapangan_name = trim($lapangan_name);

  // simpan input asli untuk debug
  $_SESSION['booking_debug_resolve_input'] = [
    'lapangan_name_raw' => $lapangan_name,
    'hex' => bin2hex($lapangan_name),
    'len' => strlen($lapangan_name),
  ];

  // samakan gaya kutip unicode ke apostrof ASCII untuk pencocokan
  $normalized = preg_replace('/\s+/', ' ', $lapangan_name);
  $normalized = str_replace(['’','‘','`','“','”'], ["'","'","'","\"","\""], $normalized);

  $ascii1 = $normalized;

  // 1) exact match dengan nama yang sudah dinormalisasi

  if($normalized !== ''){
    $q = mysqli_query(
      $conn,
      "SELECT id_lapangan FROM lapangan WHERE nama_lapangan='".mysqli_real_escape_string($conn,$normalized)."' LIMIT 1"
    );

    if($q && mysqli_num_rows($q) > 0){
      $r = mysqli_fetch_assoc($q);
      $id_lapangan = (int)($r['id_lapangan'] ?? 0);
    }
  }

// 2) fallback LIKE kalau masih tidak ketemu (contoh: ada karakter tambahan di DB)
  if($id_lapangan === 0 && $normalized !== ''){
    $like = '%'.mysqli_real_escape_string($conn,$normalized).'%';
    $q2 = mysqli_query(
      $conn,
      "SELECT id_lapangan FROM lapangan WHERE nama_lapangan LIKE '".$like."' LIMIT 1"
    );
    if($q2 && mysqli_num_rows($q2) > 0){
      $r2 = mysqli_fetch_assoc($q2);
      $id_lapangan = (int)($r2['id_lapangan'] ?? 0);
    }
  }

  // 3) fallback lebih spesifik untuk kasus apostrof/unicode quote: D’ vs D'
  if($id_lapangan === 0 && $normalized !== ''){
    $ascii1 = str_replace(['’','‘','`','“','”'], ["'","'","'",'"','"'], $normalized);
    $ascii1 = trim(preg_replace('/\s+/', ' ', $ascii1));

    if($ascii1 !== ''){
      $q3 = mysqli_query(
        $conn,
        "SELECT id_lapangan FROM lapangan WHERE nama_lapangan='".mysqli_real_escape_string($conn,$ascii1)."' LIMIT 1"
      );
      if($q3 && mysqli_num_rows($q3) > 0){
        $r3 = mysqli_fetch_assoc($q3);
        $id_lapangan = (int)($r3['id_lapangan'] ?? 0);
      }

      if($id_lapangan === 0){
        $like2 = '%'.mysqli_real_escape_string($conn,$ascii1).'%';
        $q4 = mysqli_query(
          $conn,
          "SELECT id_lapangan FROM lapangan WHERE nama_lapangan LIKE '".$like2."' LIMIT 1"
        );
        if($q4 && mysqli_num_rows($q4) > 0){
          $r4 = mysqli_fetch_assoc($q4);
          $id_lapangan = (int)($r4['id_lapangan'] ?? 0);
        }
      }
    }
  }
}


// Jika tabel lapangan masih kosong/belum berisi nama ini, buat data lapangan minimal
// agar relasi foreign key booking tetap valid.
if($id_lapangan === 0 && $lapangan_name !== ''){
  $harga_lapangan = $expectedJumlah > 0 ? $expectedJumlah : 0;
  $lokasi_lapangan = '-';
  $stmtInsertLap = mysqli_prepare($conn, "INSERT INTO lapangan(nama_lapangan,lokasi,harga) VALUES(?,?,?)");
  if($stmtInsertLap){
    mysqli_stmt_bind_param($stmtInsertLap, 'ssi', $lapangan_name, $lokasi_lapangan, $harga_lapangan);
    if(mysqli_stmt_execute($stmtInsertLap)){
      $id_lapangan = (int)mysqli_insert_id($conn);
    }
  }

  if($id_lapangan === 0){
    $targetName = normalize_booking_text($lapangan_name);
    $qAll = mysqli_query($conn, "SELECT id_lapangan,nama_lapangan FROM lapangan");
    while($qAll && ($lapRow = mysqli_fetch_assoc($qAll))){
      if(normalize_booking_text($lapRow['nama_lapangan'] ?? '') === $targetName){
        $id_lapangan = (int)($lapRow['id_lapangan'] ?? 0);
        break;
      }
    }
  }
}


if($id_lapangan === 0){
  $_SESSION['booking_debug'] = [
    'step' => 'resolve_lapangan',
    'error' => 'Lapangan tidak valid',
    'lapangan_name' => $lapangan_name,
  ];
  header('Location: dashboard.php');
  exit;
}

if($jadwal_jam === ''){
  $_SESSION['booking_debug'] = [
    'step' => 'validate_jadwal',
    'error' => 'jadwal kosong'
  ];
  header('Location: booking_checkout.php?from=retry&id_lapangan='.(int)$id_lapangan.'&lapangan='.urlencode($lapangan_name).'&jadwal='.urlencode($jadwal_jam).'&harga='.urlencode($harga_text));
  exit;
}

mysqli_begin_transaction($conn);

// INSERT booking (sekali)
$stmtB = mysqli_prepare($conn, "INSERT INTO booking(tanggal,jam,status,id_user,id_lapangan) VALUES(?,?,?,?,?)");
if(!$stmtB){
  mysqli_rollback($conn);
  $_SESSION['booking_debug'] = [
    'step' => 'prepare_booking',
    'error' => mysqli_error($conn)
  ];
  header('Location: booking_checkout.php?from=retry&id_lapangan='.(int)$id_lapangan.'&lapangan='.urlencode($lapangan_name).'&jadwal='.urlencode($jadwal_jam).'&harga='.urlencode($harga_text));
  exit;
}

mysqli_stmt_bind_param($stmtB, 'sssii', $today, $jadwal_jam, $status_booking, $id_user, $id_lapangan);
// pastikan id_lapangan valid sebelum insert (menghindari foreign key gagal)
if($id_lapangan <= 0){
  mysqli_rollback($conn);
  $_SESSION['booking_debug'] = [
    'step' => 'validate_id_lapangan',
    'error' => 'id_lapangan tidak valid',
    'id_lapangan' => $id_lapangan,
    'lapangan_name' => $lapangan_name,
  ];
  header('Location: dashboard.php');
  exit;
}

if(!mysqli_stmt_execute($stmtB)){

  mysqli_rollback($conn);
  $_SESSION['booking_debug'] = [
    'step' => 'execute_booking',
    'error' => mysqli_error($conn)
  ];
  header('Location: booking_checkout.php?from=retry&id_lapangan='.(int)$id_lapangan.'&lapangan='.urlencode($lapangan_name).'&jadwal='.urlencode($jadwal_jam).'&harga='.urlencode($harga_text));
  exit;
}

$id_booking = (int)mysqli_insert_id($conn);
if($id_booking <= 0){
  mysqli_rollback($conn);
  $_SESSION['booking_debug'] = [
    'step' => 'insert_booking_id',
    'error' => 'id_booking tidak valid'
  ];
  header('Location: booking_checkout.php?from=retry&id_lapangan='.(int)$id_lapangan.'&lapangan='.urlencode($lapangan_name).'&jadwal='.urlencode($jadwal_jam).'&harga='.urlencode($harga_text));
  exit;
}

// INSERT pembayaran (sekali)
// catatan: kolom jam_pembayaran dan kembalian dipakai riwayat booking.
$kembalian = $inputJumlah - $expectedJumlah; // tidak ditampilkan di riwayat (hanya disimpan untuk kebutuhan lain)
$jam_pembayaran = date('H:i');

$hasJamPembayaran = false;
$hasKembalian = false;
$colJam = mysqli_query($conn, "SHOW COLUMNS FROM pembayaran LIKE 'jam_pembayaran'");
if($colJam && mysqli_num_rows($colJam) > 0){
  $hasJamPembayaran = true;
} else {
  mysqli_query($conn, "ALTER TABLE pembayaran ADD jam_pembayaran VARCHAR(5) NULL AFTER tanggal_pembayaran");
  $colJam = mysqli_query($conn, "SHOW COLUMNS FROM pembayaran LIKE 'jam_pembayaran'");
  if($colJam && mysqli_num_rows($colJam) > 0){
    $hasJamPembayaran = true;
  }
}
$colKembalian = mysqli_query($conn, "SHOW COLUMNS FROM pembayaran LIKE 'kembalian'");
if($colKembalian && mysqli_num_rows($colKembalian) > 0){
  $hasKembalian = true;
} else {
  mysqli_query($conn, "ALTER TABLE pembayaran ADD kembalian INT(11) NULL AFTER jam_pembayaran");
  $colKembalian = mysqli_query($conn, "SHOW COLUMNS FROM pembayaran LIKE 'kembalian'");
  if($colKembalian && mysqli_num_rows($colKembalian) > 0){
    $hasKembalian = true;
  }
}

if($hasJamPembayaran && $hasKembalian){
  $stmtP = mysqli_prepare($conn, "INSERT INTO pembayaran(id_booking,jumlah,status,tanggal_pembayaran,jam_pembayaran,kembalian) VALUES(?,?,?,?,?,?)");
} elseif($hasJamPembayaran){
  $stmtP = mysqli_prepare($conn, "INSERT INTO pembayaran(id_booking,jumlah,status,tanggal_pembayaran,jam_pembayaran) VALUES(?,?,?,?,?)");
} elseif($hasKembalian){
  $stmtP = mysqli_prepare($conn, "INSERT INTO pembayaran(id_booking,jumlah,status,tanggal_pembayaran,kembalian) VALUES(?,?,?,?,?)");
} else {
  $stmtP = mysqli_prepare($conn, "INSERT INTO pembayaran(id_booking,jumlah,status,tanggal_pembayaran) VALUES(?,?,?,?)");
}
if(!$stmtP){
  mysqli_rollback($conn);
  $_SESSION['booking_debug'] = [
    'step' => 'prepare_pembayaran',
    'error' => mysqli_error($conn),
    'id_booking' => $id_booking
  ];
  header('Location: booking_checkout.php?from=retry&id_lapangan='.(int)$id_lapangan.'&lapangan='.urlencode($lapangan_name).'&jadwal='.urlencode($jadwal_jam).'&harga='.urlencode($harga_text));
  exit;
}

$tanggal_bayar = $today;
if($hasJamPembayaran && $hasKembalian){
  mysqli_stmt_bind_param($stmtP, 'iisssi', $id_booking, $jumlah, $status_pembayaran, $tanggal_bayar, $jam_pembayaran, $kembalian);
} elseif($hasJamPembayaran){
  mysqli_stmt_bind_param($stmtP, 'iisss', $id_booking, $jumlah, $status_pembayaran, $tanggal_bayar, $jam_pembayaran);
} elseif($hasKembalian){
  mysqli_stmt_bind_param($stmtP, 'iissi', $id_booking, $jumlah, $status_pembayaran, $tanggal_bayar, $kembalian);
} else {
  mysqli_stmt_bind_param($stmtP, 'iiss', $id_booking, $jumlah, $status_pembayaran, $tanggal_bayar);
}
if(!mysqli_stmt_execute($stmtP)){
  mysqli_rollback($conn);

  $_SESSION['booking_debug'] = [
    'step' => 'execute_pembayaran',
    'error' => mysqli_error($conn),
    'id_booking' => $id_booking
  ];
  header('Location: booking_checkout.php?from=retry&id_lapangan='.(int)$id_lapangan.'&lapangan='.urlencode($lapangan_name).'&jadwal='.urlencode($jadwal_jam).'&harga='.urlencode($harga_text));
  exit;
}

// simpan id_booking agar booking_list bisa fallback bila query belum ada
$_SESSION['booking_debug_last_id_booking'] = $id_booking;

mysqli_commit($conn);

// Simpan debug server-side agar bisa dilihat walau riwayat kosong
$_SESSION['booking_last_debug'] = [
  'step' => 'commit_ok',
  'id_user' => $id_user,
  'id_lapangan' => $id_lapangan,
  'lapangan_name' => $lapangan_name,
  'jadwal_jam' => $jadwal_jam,
  'expectedJumlah' => $expectedJumlah,
  'inputJumlah' => $inputJumlah,
  'kembalian' => 'Rp'.number_format($kembalian,0,',','.'),
  'jam_pembayaran' => $jam_pembayaran,
  'paymentSuccess' => $paymentSuccess,
  'id_booking' => (int)$id_booking,
  'tanggal' => $today,
  'status_booking' => $status_booking,
  'status_pembayaran' => $status_pembayaran,
];

// Flash notif agar booking_list bisa menampilkan pesan
$_SESSION['booking_success'] = true;
$_SESSION['booking_success_message'] = ($paymentSuccess ? 'Pembayaran terkirim. Booking menunggu verifikasi admin.' : 'Pembayaran gagal. Nominal tidak sesuai.');

// Clear debug input when successful
if($paymentSuccess){
  unset($_SESSION['booking_debug_resolve_input']);
}

// Setelah pembayaran (berhasil atau gagal) tetap arahkan ke riwayat
// (Jangan redirect hanya ke dashboard checkout, biar riwayat ter-update)
$_SESSION['booking_success'] = true;
$_SESSION['booking_success_message'] = ($paymentSuccess ? 'Pembayaran terkirim. Booking menunggu verifikasi admin.' : 'Pembayaran gagal. Booking kamu tetap masuk riwayat.');

if($redirect_to === 'dashboard'){
  header('Location: dashboard.php');
} else {
  header('Location: booking_list.php');
}
exit;





