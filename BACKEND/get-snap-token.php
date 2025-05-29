<?php
require '../koneksi.php';
require '../MIDTRANS/Midtrans.php';

session_start();
$gross_amount = $_SESSION['total_harga'];

\Midtrans\Config::$serverKey = 'SB-Mid-server-59v36Vn6tgB1v11nZsVGuVV2';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

$params = [
    'transaction_details' => [
        'order_id' => rand(),
        'gross_amount' => $gross_amount,
    ],
    'customer_details' => [
        'first_name' => 'Asad',
        'phone' => '6285800488815',
    ],
];

try {
    $snapToken = \Midtrans\Snap::getSnapToken($params);
    header('Content-Type: application/json');
    echo json_encode(['token' => $snapToken]);
} catch (Exception $e) {
    echo "Gagal membuat token Midtrans: " . $e->getMessage();
}