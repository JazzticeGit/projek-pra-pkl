<?php
require_once '../vendor/autoload.php';

\Midtrans\Config::$serverKey = 'SB-Mid-server-59v36Vn6tgB1v11nZsVGuVV2';
\Midtrans\Config::$isProduction = false;

$notif = new \Midtrans\Notification();

$order_id = $notif->order_id;
$transaction_status = $notif->transaction_status;
$payment_type = $notif->payment_type;

// Simpan ke database
include '../koneksi.php';

$status = ($transaction_status === 'settlement') ? 'berhasil' : $transaction_status;
$tgl = date('Y-m-d H:i:s');

mysqli_query($koneksi, "UPDATE pembayaran SET status='$status', tgl_pembayaran='$tgl' WHERE pemesanan_id='$order_id'");
