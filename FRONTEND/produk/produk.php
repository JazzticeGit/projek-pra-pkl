<?php
include '../../koneksi.php';

// Ambil produk best seller dari database
$query = "SELECT * FROM produk WHERE best_seller = 1";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Best Seller</title>
    <link rel="stylesheet" href="../../STYLESHEET/produk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="banner-container">
  <div class="best-seller-banner">
    <img src="../../image/agesa putih.png" alt="AGESA SHOP Logo" class="logo-img">
    <div class="title-group">
      <h1>Produk Best Seller</h1>
      <p>Temukan Item Favoritmu Di sini</p>
    </div>
  </div>
</div>

<div class="product-grid">
        <?php while ($produk = mysqli_fetch_assoc($result)): ?>
            <div class="card">
                <img src="../../<?= htmlspecialchars($produk['image']) ?>" alt="<?= htmlspecialchars($produk['name']) ?>">
                <div class="info">
                    <span class="category"><?= htmlspecialchars($produk['kategori'] ?? 'Unisex') ?></span>
                    <h4><?= htmlspecialchars($produk['name']) ?></h4>
                    <p class="price">Rp<?= number_format($produk['harga'], 0, ',', '.') ?></p>
                    <div class="sizes"><?= htmlspecialchars($produk['ukuran'] ?? 'S-XXL') ?></div>
                    <a href="keranjang.php?action=add&id=<?= $produk['produk_id'] ?>" class="cart-btn">
                        <i class="fa-solid fa-cart-shopping"></i> Add to Cart
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>