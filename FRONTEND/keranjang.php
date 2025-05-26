<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Handle add to cart
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $produk_id = (int)$_GET['id'];
    $query = mysqli_query($koneksi, "SELECT * FROM produk WHERE produk_id = $produk_id");
    $produk = mysqli_fetch_assoc($query);

    if ($produk) {
        if (isset($_SESSION['keranjang'][$produk_id])) {
            $_SESSION['keranjang'][$produk_id] += 1;
        } else {
            $_SESSION['keranjang'][$produk_id] = 1;
        }
    }
    header("Location: keranjang.php");
    exit;
}

// Handle remove from cart
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id'])) {
    $produk_id = (int)$_GET['id'];
    unset($_SESSION['keranjang'][$produk_id]);
    header("Location: keranjang.php");
    exit;
}

// Handle quantity update
if (isset($_GET['action']) && $_GET['action'] == 'update' && isset($_GET['id']) && isset($_GET['qty'])) {
    $produk_id = (int)$_GET['id'];
    $qty = (int)$_GET['qty'];
    
    if ($qty > 0) {
        $_SESSION['keranjang'][$produk_id] = $qty;
    } else {
        unset($_SESSION['keranjang'][$produk_id]);
    }
    header("Location: keranjang.php");
    exit;
}

// Count total items
$total_items = array_sum($_SESSION['keranjang']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link rel="stylesheet" href="../STYLESHEET/keranjang.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <a href="produk/produk.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1>Keranjang Belanja</h1>
        <span class="items-count"><?= $total_items ?> Items</span>
    </div>

    <?php if (empty($_SESSION['keranjang'])): ?>
        <!-- Empty Cart -->
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h3>Keranjang Belanja Kosong</h3>
            <p>Belum ada produk yang ditambahkan ke keranjang</p>
            <div class="shop-link">
                <a href="../index.php">Mulai Belanja</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Cart Items -->
        <div class="cart-items">
            <?php
            $total_semua = 0;
            foreach ($_SESSION['keranjang'] as $produk_id => $jumlah):
                $produk_id = (int) $produk_id;
                $jumlah = (int) $jumlah;

                $query = mysqli_query($koneksi, "SELECT * FROM produk WHERE produk_id = $produk_id");
                $produk = mysqli_fetch_assoc($query);

                if (!$produk) continue;

                $harga = (int) $produk['harga'];
                $total = $harga * $jumlah;
                $total_semua += $total;
            ?>
            <div class="cart-item">
                <img src="../<?= htmlspecialchars($produk['image']) ?>" alt="<?= htmlspecialchars($produk['name']) ?>" class="product-image">
                
                <div class="product-info">
                    <div class="product-name"><?= htmlspecialchars($produk['name']) ?></div>
                    <div class="product-category">Jaket, produk pria</div>
                    <div class="product-description">Lorem ipsum dolor sit amet. Lorem ipsum dolor sit et amet</div>
                    
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(<?= $produk_id ?>, <?= $jumlah - 1 ?>)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantity-display"><?= $jumlah ?></span>
                        <button class="quantity-btn" onclick="updateQuantity(<?= $produk_id ?>, <?= $jumlah + 1 ?>)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="product-actions">
                    <div class="product-price">Rp<?= number_format($total, 0, ',', '.') ?></div>
                    <div class="action-buttons">
                        <button class="simpan-btn">Simpan</button>
                        <a href="checkout.php?id=<?= $produk_id ?>" class="checkout-btn-small">Checkout</a>
                    </div>
                </div>

                <a href="keranjang.php?action=hapus&id=<?= $produk_id ?>" 
                   onclick="return confirm('Yakin ingin menghapus produk ini dari keranjang?')" 
                   class="hapus-btn">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Checkout Box -->
        <div class="checkout-box">
            <div class="total-info">
                <span class="total-label">Total Belanja:</span>
                <span class="total-amount">Rp<?= number_format($total_semua, 0, ',', '.') ?></span>
            </div>
            <a href="checkout.php" class="checkout-btn">Checkout Sekarang</a>
            
            <div class="shop-link">
                <a href="produk/produk.php">shop</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function updateQuantity(productId, newQty) {
    if (newQty < 0) newQty = 0;
    
    if (newQty === 0) {
        if (confirm('Yakin ingin menghapus produk ini dari keranjang?')) {
            window.location.href = `keranjang.php?action=update&id=${productId}&qty=${newQty}`;
        }
    } else {
        window.location.href = `keranjang.php?action=update&id=${productId}&qty=${newQty}`;
    }
}

// Auto-save functionality (optional)
function saveCart() {
    // You can implement auto-save to database here
    console.log('Cart saved');
}

// Add loading animation when buttons are clicked
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.checkout-btn, .checkout-btn-small');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            this.innerHTML = '<span class="loading"></span> Processing...';
            this.style.pointerEvents = 'none';
        });
    });
});
</script>

</body>
</html>