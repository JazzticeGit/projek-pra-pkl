<?php
session_start();
include '../koneksi.php';

$user_id = $_SESSION['user_id'] ?? 1; // asumsikan user login

// Ambil produk dari keranjang user
$queryKeranjang = mysqli_query($koneksi, "SELECT k.*, p.name, p.image, p.harga 
  FROM keranjang k 
  JOIN produk p ON k.produk_id = p.produk_id 
  WHERE k.user_id = $user_id");

$produk = [];
$subtotal = 0;
while ($row = mysqli_fetch_assoc($queryKeranjang)) {
    $produk[] = $row;
    $subtotal += $row['harga'] * $row['jumlah'];
}

// Ambil metode pembayaran
$queryMetode = mysqli_query($koneksi, "SELECT * FROM metode_pembayaran 
  WHERE nama IN ('DANA', 'GoPay', 'OVO')");

 ?>

 <!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Checkout</title>
  <link rel="stylesheet" href="../STYLESHEET/checkout-style.css">
</head>
<body>
  <div class="container">
    <h2>Checkout</h2>
    <form action="proses-pembayaran.php" method="POST">
      <div class="checkout-container">
        <div class="left-section">
          <h3>Alamat Pengiriman</h3>
          <textarea name="alamat_lengkap" rows="4" required placeholder="Masukkan alamat lengkap..."></textarea>

          <h3>Produk Dibeli</h3>
          <?php foreach ($produk as $item): ?>
            <div class="produk-item">
              <img src="../<?= $item['image'] ?>" alt="<?= $item['name'] ?>">
              <div>
                <h4><?= $item['name'] ?></h4>
                <p>Ukuran: <?= $item['size'] ?></p>
                <p>Jumlah: <?= $item['jumlah'] ?></p>
                <p>Harga: Rp<?= number_format($item['harga']) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="right-section">
          <h3>Metode Pembayaran</h3>
          <?php while ($m = mysqli_fetch_assoc($queryMetode)): ?>
            <label>
              <input type="radio" name="id_metode_pembayaran" value="<?= $m['id'] ?>" required>
              <?= $m['nama'] ?> - <?= $m['norek'] ?>
            </label><br>
          <?php endwhile; ?>

          <hr>
          <h4>Ringkasan</h4>
          <p>Total Belanja: Rp<?= number_format($subtotal) ?></p>
          <p>Ongkir: Rp10.000</p>
          <p><strong>Total: Rp<?= number_format($subtotal + 10000) ?></strong></p>

          <input type="hidden" name="total_bayar" value="<?= $subtotal + 10000 ?>">
          <button type="submit">Bayar Sekarang</button>
        </div>
      </div>
    </form>
  </div>
</body>
</html>
