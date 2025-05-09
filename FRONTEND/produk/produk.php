<?php
session_start();
include '../../koneksi.php';

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $produk_id = (int)$_GET['id'];

    $produk_query = mysqli_query($koneksi, "SELECT * FROM produk WHERE produk_id = $produk_id");
    $produk = mysqli_fetch_assoc($produk_query);

    if ($produk) {
        if (isset($_SESSION['keranjang'][$produk_id])) {
            $_SESSION['keranjang'][$produk_id]['jumlah'] += 1;
        } else {
            $_SESSION['keranjang'][$produk_id] = [
                'id' => $produk['produk_id'],
                'nama' => $produk['name'],
                'harga' => $produk['harga'],
                'gambar' => $produk['image'],
                'jumlah' => 1
            ];
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

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
            <a href="detail-produk.php?id=<?= $produk['produk_id'] ?>">
                <img src="../../<?= htmlspecialchars($produk['image']) ?>" alt="<?= htmlspecialchars($produk['name']) ?>">
            </a>
            <div class="info">
                <span class="category"><?= htmlspecialchars($produk['kategori'] ?? '') ?></span>
                <h4><a href="detail-produk.php?id=<?= $produk['produk_id'] ?>"><?= htmlspecialchars($produk['name']) ?></a></h4>
                <p class="price">Rp<?= number_format($produk['harga'], 0, ',', '.') ?></p>
                <div class="sizes"><?= htmlspecialchars($produk['size'] ?? 'S-XXL') ?></div>
                <a href="?action=add&id=<?= $produk['produk_id'] ?>" class="cart-btn">
                    <i class="fa-solid fa-cart-shopping"></i> Add to Cart
                </a>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>