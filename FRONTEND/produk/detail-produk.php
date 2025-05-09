<?php
session_start();
include '../../koneksi.php';
$produk_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "SELECT * FROM produk WHERE produk_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $produk_id);
$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

if (!$produk) {
    die("Produk tidak ditemukan");
}

$query_variant = "SELECT * FROM produk WHERE produk_id = ?";
$stmt_variant = $koneksi->prepare($query_variant);
$stmt_variant->bind_param("i", $produk_id);
$stmt_variant->execute();
$variants = $stmt_variant->get_result();

$variant_data = [];
while ($variant = $variants->fetch_assoc()) {
    $variant_data[$variant['type']][] = $variant;
}

if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['keranjang'][$produk_id])) {
        $_SESSION['keranjang'][$produk_id] = [
            'id' => $produk['produk_id'],
            'nama' => $produk['name'],
            'harga' => $produk['harga'],
            'gambar' => $produk['image'],
            'jumlah' => $_POST['quantity'] ?? 1
        ];
    } else {
        $_SESSION['keranjang'][$produk_id]['jumlah'] += $_POST['quantity'] ?? 1;
    }
    header("Location: keranjang.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produk['name']); ?> - Detail Produk</title>
    <link rel="stylesheet" href="../../STYLESHEET/detail-produk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="banner-container">
  <div class="best-seller-banner">
    <img src="../../image/agesa putih.png" alt="AGESA SHOP Logo" class="logo-img">
    <div class="title-group">
      <h1>Detail Produk</h1>
      <p>Lihat detail lengkap produk</p>
    </div>
  </div>
</div>

<div class="container">
    <div class="product-detail">
        <div class="product-images">
            <img src="../../<?php echo htmlspecialchars($produk['image']); ?>" alt="<?php echo htmlspecialchars($produk['name']); ?>">
        </div>
        
        <div class="product-info">
            <h1 class="product-title"><?php echo htmlspecialchars($produk['name']); ?></h1>
            <div class="product-price">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?>-</div>
            
            <div class="product-meta">
                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($produk['kategori'] ?? 'Umum'); ?></span>
                <span><i class="fas fa-box"></i> Stok: <?php echo htmlspecialchars($produk['stok'] ?? 'Tersedia'); ?></span>
            </div>
            
            <?php if (!empty($variant_data)): ?>
                <form method="post" action="">
                    <?php foreach ($variant_data as $type => $options): ?>
                        <div class="variant-section">
                            <div class="variant-title"><?php echo ucfirst($type); ?></div>
                            <div class="variant-options">
                                <?php foreach ($options as $option): ?>
                                    <label class="variant-option">
                                        <input type="radio" name="<?php echo $type; ?>" 
                                               value="<?php echo htmlspecialchars($option['value']); ?>" required>
                                        <?php echo htmlspecialchars($option['value']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="quantity-section">
                        <label for="quantity">Jumlah:</label>
                        <input type="number" id="quantity" name="quantity" min="1" 
                               max="<?php echo htmlspecialchars($produk['stok'] ?? 10); ?>" value="1">
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" name="add_to_cart" class="btn btn-primary">
                            <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                        </button>
                        <button type="button" class="btn btn-secondary">
                            <i class="fas fa-bolt"></i> Beli Sekarang
                        </button>
                    </div>
                </form>
            <?php endif; ?>
            
            <div class="divider"></div>
            
            <div class="product-description">
                <h3>Deskripsi Produk</h3>
                <p><?php echo nl2br(htmlspecialchars($produk['deskripsi'] ?? 'Tidak ada deskripsi tersedia')); ?></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>