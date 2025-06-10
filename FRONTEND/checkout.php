<?php
session_start();
include '../koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? 1;

// Ambil produk dari keranjang user yang statusnya 'aktif'
$queryKeranjang = mysqli_query($koneksi, "SELECT k.*, p.name, p.image, p.harga 
  FROM keranjang k 
  JOIN produk p ON k.produk_id = p.produk_id 
  WHERE k.user_id = $user_id AND k.status = 'aktif'");

$produk = [];
$subtotal = 0;
while ($row = mysqli_fetch_assoc($queryKeranjang)) {
    $produk[] = $row;
    $subtotal += $row['harga'] * $row['jumlah'];
}

// Cek jika keranjang kosong
if (empty($produk)) {
    echo "<script>alert('Keranjang kosong!'); window.location.href='keranjang.php';</script>";
    exit;
}

// Ambil metode pembayaran
$queryMetode = mysqli_query($koneksi, "SELECT * FROM metode_pembayaran 
  WHERE nama IN ('DANA', 'GoPay', 'OVO')");

$metode_pembayaran = [];
while ($m = mysqli_fetch_assoc($queryMetode)) {
    $metode_pembayaran[] = $m;
}
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
    
    <!-- DEBUG INFO - hapus setelah testing -->
    <!-- 
    <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;">
      <strong>DEBUG INFO:</strong><br>
      User ID: <?= $user_id ?><br>
      Jumlah Produk: <?= count($produk) ?><br>
      Subtotal: <?= $subtotal ?><br>
      Total: <?= $subtotal + 10000 ?><br>
      Metode Pembayaran: <?= count($metode_pembayaran) ?> tersedia
    </div>
    -->
    
    <form action="proses-chekout.php" method="POST" id="checkoutForm">
      <div class="checkout-container">
        <div class="left-section">
          <h3>Alamat Pengiriman</h3>
          <textarea name="alamat_lengkap" rows="4" required placeholder="Masukkan alamat lengkap..."></textarea>

          <h3>Produk Dibeli</h3>
          <?php foreach ($produk as $item): ?>
            <div class="produk-item">
              <img src="../<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
              <div>
                <h4><?= htmlspecialchars($item['name']) ?></h4>
                <p>Ukuran: <?= htmlspecialchars($item['size']) ?></p>
                <p>Jumlah: <?= $item['jumlah'] ?></p>
                <p>Harga: Rp<?= number_format($item['harga']) ?></p>
              </div>
            </div>
            <input type="hidden" name="keranjang_ids[]" value="<?= intval($item['id']) ?>">
          <?php endforeach; ?>
        </div>

        <div class="right-section">
          <h3>Metode Pembayaran</h3>
          <?php if (!empty($metode_pembayaran)): ?>
            <?php foreach ($metode_pembayaran as $m): ?>
              <label style="display: block; margin: 10px 0;">
                <input type="radio" name="id_metode_pembayaran" value="<?= intval($m['id']) ?>" required>
                <?= htmlspecialchars($m['nama']) ?> - <?= htmlspecialchars($m['norek']) ?>
              </label>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="color: red;">Tidak ada metode pembayaran tersedia!</p>
          <?php endif; ?>

          <hr>
          <h4>Ringkasan</h4>
          <p>Total Belanja: Rp<?= number_format($subtotal) ?></p>
          <p>Ongkir: Rp10.000</p>
          <p><strong>Total: Rp<?= number_format($subtotal + 10000) ?></strong></p>

          <input type="hidden" name="total_bayar" value="<?= intval($subtotal + 10000) ?>">
          
          <?php if (!empty($metode_pembayaran)): ?>
            <button type="submit" onclick="return validateForm()">Bayar Sekarang</button>
          <?php else: ?>
            <button type="button" disabled>Metode Pembayaran Tidak Tersedia</button>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>

  <script>
    function validateForm() {
      // Cek apakah metode pembayaran dipilih
      const metodePembayaran = document.querySelector('input[name="id_metode_pembayaran"]:checked');
      if (!metodePembayaran) {
        alert('Silakan pilih metode pembayaran!');
        return false;
      }
      
      // Cek alamat
      const alamat = document.querySelector('textarea[name="alamat_lengkap"]').value.trim();
      if (!alamat) {
        alert('Silakan isi alamat pengiriman!');
        return false;
      }
      
      // Konfirmasi
      return confirm('Lanjutkan ke pembayaran?');
    }
    
    // Debug - tampilkan data yang akan dikirim (hapus setelah testing)
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
      console.log('Form Data:');
      const formData = new FormData(this);
      for (let [key, value] of formData.entries()) {
        console.log(key, value);
      }
    });
  </script>
</body>
</html>