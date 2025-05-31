<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Handle add to cart (from detail_produk.php)
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id']) && isset($_GET['size'])) {
    $produk_id = (int)$_GET['id'];
    $size = $_GET['size'];
    
    // Get product details with discount
    $query = "SELECT p.*, d.persen_diskon 
              FROM produk p 
              LEFT JOIN diskon d ON p.produk_id = d.produk_id 
                  AND d.status = 'active' 
                  AND NOW() BETWEEN d.start_date AND d.end_date 
              WHERE p.produk_id = $produk_id";
    
    $result = mysqli_query($koneksi, $query);
    $produk = mysqli_fetch_assoc($result);

    if ($produk) {
        $harga_asli = $produk['harga'];
        $persen_diskon = $produk['persen_diskon'];
        $harga_final = ($persen_diskon && $persen_diskon > 0) ? $harga_asli * (1 - $persen_diskon / 100) : $harga_asli;
        
        // Create unique cart key with size
        $cart_key = $produk_id . '_' . $size;
        
        if (isset($_SESSION['keranjang'][$cart_key])) {
            $_SESSION['keranjang'][$cart_key]['jumlah'] += 1;
        } else {
            $_SESSION['keranjang'][$cart_key] = [
                'id' => $produk['produk_id'],
                'nama' => $produk['name'],
                'harga' => $harga_final,
                'gambar' => $produk['image'],
                'jumlah' => 1,
                'size' => $size
            ];
        }
    }
    
    // Check if should redirect to checkout
    if (isset($_GET['redirect']) && $_GET['redirect'] == 'checkout') {
        header("Location: checkout.php");
        exit;
    }
    
    header("Location: keranjang.php");
    exit;
}

// Handle remove from cart
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['key'])) {
    $cart_key = $_GET['key'];
    unset($_SESSION['keranjang'][$cart_key]);
    header("Location: keranjang.php");
    exit;
}

// Handle quantity update
if (isset($_GET['action']) && $_GET['action'] == 'update' && isset($_GET['key']) && isset($_GET['qty'])) {
    $cart_key = $_GET['key'];
    $qty = (int)$_GET['qty'];
    
    if (isset($_SESSION['keranjang'][$cart_key])) {
        if ($qty > 0) {
            $_SESSION['keranjang'][$cart_key]['jumlah'] = $qty;
        } else {
            unset($_SESSION['keranjang'][$cart_key]);
        }
    }
    header("Location: keranjang.php");
    exit;
}

// Count total items
$total_items = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $total_items += $item['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link rel="stylesheet" href="../STYLESHEET/keranjang.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .size-badge {
            background-color: #4ade80;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            display: inline-block;
            margin-top: 5px;
        }
        
        .product-size {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .product-meta {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 5px;
        }
        
        .price-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .unit-price {
            font-size: 0.85em;
            color: #6c757d;
            margin-bottom: 2px;
        }
        
        .total-price {
            font-weight: bold;
            color: #4ade80;
        }
    </style>
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
                <a href="produk/produk.php">Mulai Belanja</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Cart Items -->
        <div class="cart-items">
            <?php
            $total_semua = 0;
            foreach ($_SESSION['keranjang'] as $cart_key => $item):
                $total = $item['harga'] * $item['jumlah'];
                $total_semua += $total;
            ?>
            <div class="cart-item">
                <img src="../<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama']) ?>" class="product-image">
                
                <div class="product-info">
                    <div class="product-name"><?= htmlspecialchars($item['nama']) ?></div>
                    <div class="product-meta">
                        <div class="product-category">Kategori Produk</div>
                        <span class="size-badge">Size: <?= htmlspecialchars($item['size']) ?></span>
                    </div>
                    <div class="product-description">
                        Unit: Rp<?= number_format($item['harga'], 0, ',', '.') ?> x <?= $item['jumlah'] ?>
                    </div>
                    
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity('<?= $cart_key ?>', <?= $item['jumlah'] - 1 ?>)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantity-display"><?= $item['jumlah'] ?></span>
                        <button class="quantity-btn" onclick="updateQuantity('<?= $cart_key ?>', <?= $item['jumlah'] + 1 ?>)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="product-actions">
                    <div class="price-info">
                        <div class="unit-price">@Rp<?= number_format($item['harga'], 0, ',', '.') ?></div>
                        <div class="product-price total-price">Rp<?= number_format($total, 0, ',', '.') ?></div>
                    </div>
                    <div class="action-buttons">
                        <button class="simpan-btn" onclick="saveToWishlist('<?= $cart_key ?>')">Simpan</button>
                        <a href="checkout.php?key=<?= $cart_key ?>" class="checkout-btn-small">Checkout</a>
                    </div>
                </div>

                <a href="keranjang.php?action=hapus&key=<?= urlencode($cart_key) ?>" 
                   onclick="return confirm('Yakin ingin menghapus produk ini dari keranjang?')" 
                   class="hapus-btn">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Checkout Box -->
        <div class="checkout-box">
            <div class="checkout-summary">
                <div class="summary-row">
                    <span>Subtotal (<?= $total_items ?> items):</span>
                    <span>Rp<?= number_format($total_semua, 0, ',', '.') ?></span>
                </div>
                <div class="summary-row">
                    <span>Ongkos Kirim:</span>
                    <span class="text-success">GRATIS</span>
                </div>
                <hr>
                <div class="summary-row total-row">
                    <span class="total-label">Total:</span>
                    <span class="total-amount">Rp<?= number_format($total_semua, 0, ',', '.') ?></span>
                </div>
            </div>
            
            <a href="checkout.php" class="checkout-btn">
                <i class="fas fa-credit-card"></i> Checkout Sekarang
            </a>
            
            <div class="shop-link">
                <a href="produk/produk.php">
                    <i class="fas fa-arrow-left"></i> Lanjut Belanja
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function updateQuantity(cartKey, newQty) {
    if (newQty < 0) newQty = 0;
    
    if (newQty === 0) {
        if (confirm('Yakin ingin menghapus produk ini dari keranjang?')) {
            window.location.href = `keranjang.php?action=update&key=${encodeURIComponent(cartKey)}&qty=${newQty}`;
        }
    } else {
        window.location.href = `keranjang.php?action=update&key=${encodeURIComponent(cartKey)}&qty=${newQty}`;
    }
}

function saveToWishlist(cartKey) {
    // Implement save to wishlist functionality
    alert('Produk berhasil disimpan ke wishlist!');
    // You can add AJAX call here to save to database
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
            // Don't show loading for wishlist buttons
            if (!this.classList.contains('simpan-btn')) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                this.style.pointerEvents = 'none';
            }
        });
    });
    
    // Add smooth animations
    const cartItems = document.querySelectorAll('.cart-item');
    cartItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        item.classList.add('fade-in');
    });
});

// Add some CSS animations
const style = document.createElement('style');
style.textContent = `
    .fade-in {
        animation: fadeInUp 0.5s ease forwards;
        opacity: 0;
        transform: translateY(20px);
    }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .checkout-summary {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    .total-row {
        font-weight: bold;
        font-size: 1.1em;
        color: #4ade80;
    }
    
    .text-success {
        color: #4ade80 !important;
    }
`;
document.head.appendChild(style);
</script>

</body>
</html>