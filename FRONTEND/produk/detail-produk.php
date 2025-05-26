<?php
include '../../koneksi.php';

$id_produk = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = mysqli_query($koneksi, "
    SELECT 
        p.*, 
        k.jenis_produk AS kategori 
    FROM produk p
    LEFT JOIN kategori k ON p.id_kategori = k.id
    WHERE p.produk_id = $id_produk
");

$data = mysqli_fetch_assoc($query);
if (!$data) {
    echo "Produk tidak ditemukan.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Produk</title>
    <link rel="stylesheet" href="css/detail-produk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- Tombol Kembali -->
<div class="back-button">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<!-- Kontainer Detail Produk -->
<div class="product-detail-container">
    <div class="product-images">
        <div class="image-thumbnails">
            <div class="thumbnail active"><img src="img/<?= $data['image'] ?>" alt="thumb"></div>
        </div>
        <div class="main-image">
            <img src="img/<?= $data['image'] ?>" alt="<?= $data['name'] ?>">
        </div>
    </div>

    <div class="product-info">
        <div class="product-category"><?= $data['kategori'] ?></div>
        <div class="product-title"><?= $data['name'] ?></div>
        <div class="product-rating">
            <div class="stars">★★★★☆</div>
            <div class="rating-text">4.5 / 5</div>
        </div>
        <div class="product-price">Rp <?= number_format($data['harga'], 0, ',', '.') ?></div>

        <div class="size-selection">
            <label class="size-label">Ukuran:</label>
            <div class="size-options">
                <?php
                $ukuran_enum = explode(",", str_replace(["enum(", ")", "'"], "", "'".$data['size']."'"));
                foreach ($ukuran_enum as $size) {
                    echo "<button class='size-btn'>" . strtoupper(trim($size)) . "</button>";
                }
                ?>
            </div>
        </div>

        <div class="product-color">
            <strong>Warna:</strong> <?= ucfirst($data['color']) ?>
        </div>

        <div class="action-buttons">
            <button class="btn-buy"><i class="fas fa-bolt"></i> Beli Sekarang</button>
            <button class="btn-add-cart"><i class="fas fa-shopping-cart"></i> Tambah ke Keranjang</button>
        </div>
    </div>
</div>

<!-- Deskripsi -->
<div class="product-description-section">
    <h2>Deskripsi Produk</h2>
    <p><?= nl2br($data['deskripsi']) ?></p>
</div>

</body>
</html>
