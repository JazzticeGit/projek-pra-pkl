<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; 

require_once '../vendor/autoload.php';
include '../koneksi.php';

// Konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-59v36Vn6tgB1v11nZsVGuVV2';
\Midtrans\Config::$isProduction = false; // true jika production
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;


$_SESSION['user_id'] = $user['id'];
$queryUser = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($queryUser);

$queryCart = mysqli_query($koneksi, "SELECT k.*, p.name, p.harga FROM keranjang k 
    JOIN produk p ON k.produk_id = p.produk_id WHERE user_id = $user_id");

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

$transaction = [
    'transaction_details' => [
        'order_id' => 'AGESA-' . rand(),
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
  <link rel="stylesheet" href="../STYLESHEET/checkout.css">
  <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="YOUR_CLIENT_KEY"></script>
</head>
<body>

<!-- UI Tampilan Checkout sesuai gambar -->
<div class="checkout-container">
  <div class="alamat">
    <h3>Alamat Pengiriman</h3>
    <p><?= htmlspecialchars($user['username']) ?> - <?= htmlspecialchars($user['phone']) ?><br>
       Alamat dummy: Kalimanah, Purbalingga</p>
    <button>Edit Alamat</button>
  </div>

  <div class="produk-list">
    <?php foreach ($items as $item): ?>
      <div class="produk-item">
        <img src="../image/contoh.jpg" alt="<?= $item['name'] ?>">
        <div class="detail">
          <h4><?= $item['name'] ?></h4>
          <p>Rp<?= number_format($item['price'], 0, ',', '.') ?> x <?= $item['quantity'] ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="ringkasan">
    <h3>Ringkasan Pembayaran</h3>
    <p>Total Harga: Rp<?= number_format($total, 0, ',', '.') ?></p>
    <button id="pay-button">Checkout</button>
  </div>
</div>

<script>
  var payButton = document.getElementById('pay-button');
  payButton.addEventListener('click', function () {
    snap.pay('<?= $snapToken ?>', {
      onSuccess: function(result) {
        window.location.href = 'success.php';
      },
      onPending: function(result) {
        alert("Menunggu pembayaran!");
      },
      onError: function(result) {
        alert("Pembayaran gagal!");
      }
    });
  });
</script>

</body>
</html>
