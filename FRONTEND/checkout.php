<?php
session_start();
require '../koneksi.php';

$user_id = $_SESSION['user_id'] ?? 1; // default testing

$query = "SELECT p.name, p.image, p.harga, k.jumlah, (p.harga * k.jumlah) AS subtotal
          FROM keranjang k
          JOIN produk p ON p.produk_id = k.produk_id
          WHERE k.id IN (SELECT keranjang_id FROM pemesanan WHERE user_id = $user_id)";
$result = mysqli_query($koneksi, $query);

$items = [];
$total_harga = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
    $total_harga += $row['subtotal'];
}

$ongkir = 20000;
$asuransi = 1000;
$diskon = 0;
$total_bayar = $total_harga + $ongkir + $asuransi - $diskon;

$_SESSION['total_harga'] = $total_bayar;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Checkout</title>
  <link rel="stylesheet" href="../../STYLESHEET/checkout-style.css">
</head>
<body>
  <div class="checkout-container">
    <section class="alamat-pengiriman">
      <h3>Alamat Pengiriman</h3>
      <p><strong>Rumah Asad</strong><br>
         Babakan rt23 rw6, Kalimanah, Purbalingga, Jawa Tengah<br>
         6285800488815</p>
    </section>

    <section class="keranjang">
      <h3>Produk Dipesan</h3>
      <?php foreach($items as $item): ?>
        <div class="produk">
          <img src="../image/<?= $item['image'] ?>" alt="<?= $item['name'] ?>" width="100">
          <div>
            <h4><?= $item['name'] ?></h4>
            <p>Qty: <?= $item['jumlah'] ?> | Harga: Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </section>

    <section class="metode-pembayaran">
      <h3>Metode Pembayaran</h3>
      <label><input type="radio" name="metode" checked> Dana</label><br>
      <label><input type="radio" name="metode"> BCA</label><br>
      <label><input type="radio" name="metode"> Alfamart</label>
    </section>

    <section class="ringkasan">
      <h3>Ringkasan Transaksi</h3>
      <p>Total Harga: Rp<?= number_format($total_harga, 0, ',', '.') ?></p>
      <p>Total Ongkos Kirim: Rp<?= number_format($ongkir, 0, ',', '.') ?></p>
      <p>Total Asuransi: Rp<?= number_format($asuransi, 0, ',', '.') ?></p>
      <p>Diskon: Rp<?= number_format($diskon, 0, ',', '.') ?></p>
      <hr>
      <h4>Total Bayar: Rp<?= number_format($total_bayar, 0, ',', '.') ?></h4>
      <form action="../BACKEND/get-snap-token.php" method="POST">
        <button type="submit">Checkout</button>
      </form>
    </section>
  </div>
</body>
</html>