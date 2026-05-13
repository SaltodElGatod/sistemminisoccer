<?php
// Endpoint untuk CRUD “Read” jadwal slot yang tersedia per lapangan.
// Return JSON agar nanti bisa dipakai AJAX.
// Karena tabel jadwal belum ada, kita pakai konsep jam pemesanan yang sama dengan $fields slot di dashboard.

session_start();
header('Content-Type: application/json');

include 'koneksi.php';

$id_lapangan = (int)($_GET['id_lapangan'] ?? 0);
$nama_lapangan = (string)($_GET['lapangan'] ?? '');

function normalize_slot_text($text){
  $text = strtolower((string)$text);
  $text = str_replace(['â€™','â€˜','`','’','‘'], "'", $text);
  $text = preg_replace('/[^a-z0-9]+/i', ' ', $text);
  return trim(preg_replace('/\s+/', ' ', $text));
}

if($id_lapangan <= 0 && $nama_lapangan === ''){
  echo json_encode(['error'=>'lapangan tidak valid']);
  exit;
}

// Simpan mapping slot jam dan harga dari dashboard hardcode
$slotTemplates = [
  'Arena Deva' => [
    ['label'=>'07.00 – 15.00','jam'=>'07:00-15:00','harga'=>500000],
    ['label'=>'19.00 – 21.00','jam'=>'19:00-21:00','harga'=>700000],
  ],
  "D’ Gadavi" => [
    ['label'=>'Senin – Jumat 07.00 – 12.00','jam'=>'07:00-12:00','harga'=>400000],
    ['label'=>'Senin – Jumat 19.00 – 00.00','jam'=>'19:00-00:00','harga'=>650000],
    ['label'=>'Sabtu – Minggu 19.00 – 00.00','jam'=>'19:00-00:00','harga'=>750000],
  ],
  'Mini Soccer Kota Baru' => [
    ['label'=>'07.00 – 15.00','jam'=>'07:00-15:00','harga'=>600000],
    ['label'=>'19.00 – 21.00','jam'=>'19:00-21:00','harga'=>700000],
  ],
  'Desta Mini Soccer' => [
    ['label'=>'Senin – Jumat 07.00 – 13.00','jam'=>'07:00-13:00','harga'=>550000],
    ['label'=>'Senin – Jumat 14.00 – 00.00','jam'=>'14:00-00:00','harga'=>650000],
    ['label'=>'Sabtu – Minggu 07.00 – 13.00','jam'=>'07:00-13:00','harga'=>650000],
    ['label'=>'Sabtu – Minggu 19.00 – 00.00','jam'=>'19:00-00:00','harga'=>750000],
  ],
  'R26 Mini Soccer' => [
    ['label'=>'Senin – Kamis 08.00 – 15.00','jam'=>'08:00-15:00','harga'=>500000],
    ['label'=>'Senin – Kamis 16.00 – 18.00','jam'=>'16:00-18:00','harga'=>550000],
    ['label'=>'Senin – Kamis 19.00 – 00.00','jam'=>'19:00-00:00','harga'=>650000],
    ['label'=>'Sabtu – Minggu 08.00 – 15.00','jam'=>'08:00-15:00','harga'=>600000],
    ['label'=>'Sabtu – Minggu 16.00 – 18.00','jam'=>'16:00-18:00','harga'=>650000],
    ['label'=>'Sabtu – Minggu 19.00 – 00.00','jam'=>'19:00-00:00','harga'=>750000],
  ],
  'Melanesia Sport' => [
    ['label'=>'Senin – Jumat 07.00 – 14.00','jam'=>'07:00-14:00','harga'=>500000],
    ['label'=>'Senin – Jumat 15.00 – 00.00','jam'=>'15:00-00:00','harga'=>550000],
    ['label'=>'Sabtu – Minggu 07.00 – 14.00','jam'=>'07:00-14:00','harga'=>550000],
    ['label'=>'Sabtu – Minggu 15.00 – 00.00','jam'=>'15:00-00:00','harga'=>600000],
  ],
];

// Cari id_lapangan berdasarkan nama kalau perlu
if($id_lapangan <= 0 && $nama_lapangan !== ''){
  $q = mysqli_query($conn, "SELECT id_lapangan FROM lapangan WHERE nama_lapangan='".mysqli_real_escape_string($conn,$nama_lapangan)."' LIMIT 1");
  if($q && mysqli_num_rows($q) > 0){
    $r = mysqli_fetch_assoc($q);
    $id_lapangan = (int)($r['id_lapangan'] ?? 0);
  }

  if($id_lapangan <= 0){
    $targetName = normalize_slot_text($nama_lapangan);
    $qAll = mysqli_query($conn, "SELECT id_lapangan,nama_lapangan FROM lapangan");
    while($qAll && ($lapRow = mysqli_fetch_assoc($qAll))){
      if(normalize_slot_text($lapRow['nama_lapangan'] ?? '') === $targetName){
        $id_lapangan = (int)($lapRow['id_lapangan'] ?? 0);
        break;
      }
    }
  }
}

// Ambil nama untuk template
$tplName = $nama_lapangan;
if($tplName === ''){
  $q2 = mysqli_query($conn, "SELECT nama_lapangan FROM lapangan WHERE id_lapangan='$id_lapangan' LIMIT 1");
  if($q2 && mysqli_num_rows($q2) > 0){
    $r2 = mysqli_fetch_assoc($q2);
    $tplName = $r2['nama_lapangan'] ?? '';
  }
}

$templates = $slotTemplates[$tplName] ?? [];

// Cek slot yang sudah dibooking hari ini (menggunakan booking.tanggal + booking.jam yang diisi saat create)
$today = date('Y-m-d');

$booked = []; // normalized jam/label => true
$qb = mysqli_prepare(
  $conn,
  "SELECT b.id_lapangan,b.jam,b.status,l.nama_lapangan
   FROM booking b
   LEFT JOIN lapangan l ON l.id_lapangan=b.id_lapangan
   LEFT JOIN pembayaran p ON p.id_booking=b.id_booking
   WHERE b.tanggal=? AND b.status NOT IN ('dibatalkan') AND COALESCE(p.status,'') <> 'gagal'"
);
mysqli_stmt_bind_param($qb, 's', $today);
mysqli_stmt_execute($qb);
$resB = mysqli_stmt_get_result($qb);
$targetLapangan = normalize_slot_text($tplName);
while($rb = $resB ? mysqli_fetch_assoc($resB) : null){
  if(!$rb) break;
  $sameLapangan = ((int)$id_lapangan > 0 && (int)($rb['id_lapangan'] ?? 0) === (int)$id_lapangan)
    || ($targetLapangan !== '' && normalize_slot_text($rb['nama_lapangan'] ?? '') === $targetLapangan);
  if($sameLapangan){
    $booked[normalize_slot_text($rb['jam'] ?? '')] = true;
  }
}

// Compose response
$out = [];
foreach($templates as $t){
  $jamKey = $t['jam'];
  $labelKey = $t['label'];
  $isAvailable = empty($booked[normalize_slot_text($jamKey)]) && empty($booked[normalize_slot_text($labelKey)]);
  $out[] = [
    'label' => $t['label'],
    'jam' => $t['jam'],
    'harga' => 'Rp'.number_format($t['harga'],0,',','.'),
    'available' => $isAvailable,
  ];
}

echo json_encode(['id_lapangan'=>$id_lapangan,'tanggal'=>$today,'slots'=>$out]);

