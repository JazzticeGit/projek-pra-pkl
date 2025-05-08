<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../vendor/autoload.php';
include '../koneksi.php';

$user_id = $_SESSION['user_id'];

\Midtrans\Config::$serverKey = 'SB-Mid-server-59v36Vn6tgB1v11nZsVGuVV2';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

$queryUser = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($queryUser);

$queryCart = mysqli_query($koneksi, "SELECT k.*, p.name, p.harga FROM keranjang k 
    JOIN produk p ON k.produk_id = p.produk_id WHERE k.user_id = $user_id");

$items = [];
$total = 0;
while ($row = mysqli_fetch_assoc($queryCart)) {
    $items[] = [
        'id'       => $row['produk_id'],
        'price'    => (int)$row['harga'],
        'quantity' => (int)$row['jumlah'],
        'name'     => $row['name']
    ];
    $total += $row['harga'] * $row['jumlah'];
}

$order_id = 'AGESA-' . time();

$transaction = [
    'transaction_details' => [
        'order_id' => $order_id,
        'gross_amount' => $total
    ],
    'customer_details' => [
        'first_name' => $user['username'],
        'email' => $user['email'],
        'phone' => $user['phone'],
    ],
    'item_details' => $items
];

$snapToken = \Midtrans\Snap::getSnapToken($transaction);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - AGESA</title>
    <link rel="stylesheet" href="style/checkout.css">
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-0KijI4JUjVxvvbYV"></script>
</head>
<body>

<div class="checkout-container">
    <h2>Checkout</h2>
    <p>Nama: <?= htmlspecialchars($user['username']) ?></p>
    <p>Telepon: <?= htmlspecialchars($user['phone']) ?></p>
    <p>Total Pembayaran: Rp<?= number_format($total, 0, ',', '.') ?></p>
    <button id="pay-button">Bayar Sekarang</button>
</div>

<script>
    document.getElementById('pay-button').addEventListener('click', function () {
        snap.pay('<?= $snapToken ?>', {
            onSuccess: function(result){
                window.location.href = 'success.php?order_id=<?= $order_id ?>';
            },
            onPending: function(result){
                alert("Menunggu pembayaran.");
            },
            onError: function(result){
                alert("Pembayaran gagal.");
            }
        });
    });
</script>

</body>
</html>
