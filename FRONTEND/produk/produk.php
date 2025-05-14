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

<div class="product-grid">
    <?php while ($produk = mysqli_fetch_assoc($result)): ?>
        <div class="product-card">
            <img src="../../<?= htmlspecialchars($produk['image']) ?>" alt="<?= htmlspecialchars($produk['name']) ?>" class="product-image">

            <div class="product-content">
                <h2 class="product-title"><?= htmlspecialchars($produk['name']) ?></h2>
                <p class="product-description"><?= htmlspecialchars($produk['deskripsi'] ?? 'Deskripsi belum tersedia.') ?></p>

                <div class="product-options">
                    <!-- <select name="warna" class="color-dropdown">
                        <option value="<?= htmlspecialchars($produk['warna']) ?>"><?= htmlspecialchars($produk['warna']) ?></option>
                    </select> -->
                </div>

                <div class="product-footer">
                    <div class="product-price">Rp<?= number_format($produk['harga'], 0, ',', '.') ?></div>
                    <a href="?action=add&id=<?= $produk['produk_id'] ?>" class="add-to-cart-btn">
                        <i class="fa-solid fa-cart-plus"></i> Add to cart
                    </a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
