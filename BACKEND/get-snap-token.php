<?php
session_start();
require_once '../vendor/autoload.php'; // pastikan Midtrans SDK sudah di-install

\Midtrans\Config::$serverKey = 'SB-Mid-server-59v36Vn6tgB1v11nZsVGuVV2';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Ambil total dari session
$gross_amount = isset($_SESSION['total_harga']) ? $_SESSION['total_harga'] : 0;

// Buat transaksi
$transaction_details = array(
    'order_id' => 'ORDER-' . rand(),
    'gross_amount' => $gross_amount
);

$transaction = array(
    'transaction_details' => $transaction_details
);

$snapToken = \Midtrans\Snap::getSnapToken($transaction);
echo json_encode(['token' => $snapToken]);
