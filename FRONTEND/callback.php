<?php
require_once '../vendor/autoload.php';
include '../koneksi.php';

\Midtrans\Config::$serverKey = 'SB-Mid-server-59v36Vn6tgB1v11nZsVGuVV2';
\Midtrans\Config::$isProduction = false;

$notif = new \Midtrans\Notification();

$order_id = $notif->order_id;
$transaction = $notif->transaction_status;
$payment_type = $notif->payment_type;
$fraud = $notif->fraud_status;

// Cek status & update pembayaran
$status = 'pending';
if ($transaction == 'capture') {
    if ($payment_type == 'credit_card') {
        $status = ($fraud == 'challenge') ? 'pending' : 'berhasil';
    } else {
        $status = 'berhasil';
    }
} elseif ($transaction == 'settlement') {
    $status = 'berhasil';
} elseif ($transaction == 'pending') {
    $status = 'pending';
} elseif (in_array($transaction, ['deny', 'expire', 'cancel'])) {
    $status = 'gagal';
}

$pemesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id FROM pemesanan ORDER BY id DESC LIMIT 1"));
if ($pemesanan) {
    mysqli_query($koneksi, "UPDATE pembayaran SET status = '$status' WHERE pemesanan_id = {$pemesanan['id']}");
}

http_response_code(200);
