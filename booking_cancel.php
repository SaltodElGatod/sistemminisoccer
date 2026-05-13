<?php
session_start();

if(!isset($_SESSION['id_user'])){
  header('Location: login.php');
  exit;
}

include 'koneksi.php';

$id_user = (int)$_SESSION['id_user'];

$id_booking = (int)($_POST['id_booking'] ?? $_GET['id_booking'] ?? 0);

if($id_booking <= 0){
  header('Location: booking_list.php');
  exit;
}

// Pastikan booking ini milik user
$stmt = mysqli_prepare($conn, "SELECT id_booking FROM booking WHERE id_booking=? AND id_user=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ii', $id_booking, $id_user);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = $res ? mysqli_fetch_assoc($res) : null;

if(!$row){
  echo "<script>alert('Booking tidak ditemukan atau tidak milik anda');window.location='booking_list.php';</script>";
  exit;
}

// Update status booking dan pembayaran
// Sesuai request: setelah dibooking maka tidak bisa dibooking lagi. Cancel berarti membatalkan (status bisa diubah ke 'dibatalkan' jika kolom status varchar).
$status_cancel = 'dibatalkan';

mysqli_begin_transaction($conn);
try{
  $stmtB = mysqli_prepare($conn, "UPDATE booking SET status=? WHERE id_booking=?");
  mysqli_stmt_bind_param($stmtB, 'si', $status_cancel, $id_booking);
  if(!mysqli_stmt_execute($stmtB)) throw new Exception('update booking failed');

  $stmtP = mysqli_prepare($conn, "UPDATE pembayaran SET status=? WHERE id_booking=?");
  $status_pembayaran_cancel = 'dibatalkan';
  mysqli_stmt_bind_param($stmtP, 'si', $status_pembayaran_cancel, $id_booking);
  mysqli_stmt_execute($stmtP);

  mysqli_commit($conn);
}catch(Exception $e){
  mysqli_rollback($conn);
  echo "<script>alert('Gagal membatalkan booking');window.location='booking_list.php';</script>";
  exit;
}

$_SESSION['booking_cancel_success'] = true;
header('Location: booking_list.php');
exit;

