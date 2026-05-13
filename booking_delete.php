<?php
session_start();

if(!isset($_SESSION['id_user'])){
  header('Location: login.php');
  exit;
}

include 'koneksi.php';

$id_user = (int)$_SESSION['id_user'];
$id_booking = (int)($_POST['id_booking'] ?? 0);

if($id_booking <= 0){
  header('Location: booking_list.php');
  exit;
}

$stmt = mysqli_prepare($conn, "SELECT id_booking FROM booking WHERE id_booking=? AND id_user=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ii', $id_booking, $id_user);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if(!$res || mysqli_num_rows($res) === 0){
  $_SESSION['booking_delete_error'] = 'Riwayat booking tidak ditemukan.';
  header('Location: booking_list.php');
  exit;
}

$stmtDelete = mysqli_prepare($conn, "DELETE FROM booking WHERE id_booking=? AND id_user=?");
mysqli_stmt_bind_param($stmtDelete, 'ii', $id_booking, $id_user);

if(mysqli_stmt_execute($stmtDelete)){
  $_SESSION['booking_delete_success'] = true;
  unset(
    $_SESSION['booking_last_debug'],
    $_SESSION['booking_debug_last_id_booking'],
    $_SESSION['booking_success'],
    $_SESSION['booking_success_message']
  );
} else {
  $_SESSION['booking_delete_error'] = 'Gagal menghapus riwayat booking.';
}

header('Location: booking_list.php');
exit;
