<?php
session_start();
include '../koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fungsi untuk sync keranjang dari database ke session
function syncCartFromDatabase($koneksi, $user_id) {
    if (!isset($_SESSION['keranjang'])) {
        $_SESSION['keranjang'] = [];
    }
    
    $query = "SELECT k.*, p.name, p.harga as harga_asli, p.image, d.persen_diskon
              FROM keranjang k 
              JOIN produk p ON k.produk_id = p.produk_id 
              LEFT JOIN diskon d ON p.produk_id = d.produk_id 
                  AND d.status = 'active' 
                  AND NOW() BETWEEN d.start_date AND d.end_date 
              WHERE k.user_id = ?";
    
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $_SESSION['keranjang'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $harga_final = ($row['persen_diskon'] && $row['persen_diskon'] > 0) 
            ? $row['harga_asli'] * (1 - $row['persen_diskon'] / 100) 
            : $row['harga_asli'];
        
        $cart_key = $row['produk_id'] . '_' . $row['size'];
        $_SESSION['keranjang'][$cart_key] = [
            'id' => $row['produk_id'],
            'nama' => $row['name'],
            'harga' => $harga_final,
            'gambar' => $row['image'],
            'jumlah' => $row['jumlah'],
            'size' => $row['size']
        ];
    }
}

// Fungsi untuk menyimpan keranjang ke database
function saveCartToDatabase($koneksi, $user_id, $produk_id, $size, $jumlah, $harga) {
    // Cek stok produk terlebih dahulu
    $stok_query = "SELECT stok FROM produk WHERE produk_id = ?";
    $stmt = mysqli_prepare($koneksi, $stok_query);
    mysqli_stmt_bind_param($stmt, "i", $produk_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $produk = mysqli_fetch_assoc($result);
    
    if (!$produk || $produk['stok'] <= 0) {
        return false;
    }
    
    // Cek apakah item sudah ada
    $check_query = "SELECT id, jumlah FROM keranjang WHERE user_id = ? AND produk_id = ? AND size = ?";
    $stmt = mysqli_prepare($koneksi, $check_query);
    mysqli_stmt_bind_param($stmt, "iis", $user_id, $produk_id, $size);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Pastikan tidak melebihi stok
        $new_jumlah = min($row['jumlah'] + $jumlah, $produk['stok']);
        
        $new_total = $harga * $new_jumlah;
        
        $update_query = "UPDATE keranjang SET jumlah = ?, total = ? WHERE id = ?";
        $stmt = mysqli_prepare($koneksi, $update_query);
        mysqli_stmt_bind_param($stmt, "idi", $new_jumlah, $new_total, $row['id']);
        mysqli_stmt_execute($stmt);
    } else {
        // Insert baru dengan jumlah maksimal stok
        $jumlah = min($jumlah, $produk['stok']);
        $total = $harga * $jumlah;
        $insert_query = "INSERT INTO keranjang (user_id, produk_id, size, jumlah, total) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $insert_query);
        mysqli_stmt_bind_param($stmt, "iisid", $user_id, $produk_id, $size, $jumlah, $total);
        mysqli_stmt_execute($stmt);
    }
    
    return true;
}

// Fungsi untuk update keranjang di database
function updateCartInDatabase($koneksi, $user_id, $produk_id, $size, $jumlah) {
    if ($jumlah <= 0) {
        // Hapus dari database jika jumlah 0
        $delete_query = "DELETE FROM keranjang WHERE user_id = ? AND produk_id = ? AND size = ?";
        $stmt = mysqli_prepare($koneksi, $delete_query);
        mysqli_stmt_bind_param($stmt, "iis", $user_id, $produk_id, $size);
        mysqli_stmt_execute($stmt);
    } else {
        // Update jumlah dan total
        $get_price_query = "SELECT p.harga, d.persen_diskon 
                           FROM produk p 
                           LEFT JOIN diskon d ON p.produk_id = d.produk_id 
                               AND d.status = 'active' 
                               AND NOW() BETWEEN d.start_date AND d.end_date 
                           WHERE p.produk_id = ?";
        $stmt = mysqli_prepare($koneksi, $get_price_query);
        mysqli_stmt_bind_param($stmt, "i", $produk_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $price_data = mysqli_fetch_assoc($result);
        
        $harga_final = ($price_data['persen_diskon'] && $price_data['persen_diskon'] > 0) 
            ? $price_data['harga'] * (1 - $price_data['persen_diskon'] / 100) 
            : $price_data['harga'];
        
        $total = $harga_final * $jumlah;
        
        $update_query = "UPDATE keranjang SET jumlah = ?, total = ? WHERE user_id = ? AND produk_id = ? AND size = ?";
        $stmt = mysqli_prepare($koneksi, $update_query);
        mysqli_stmt_bind_param($stmt, "idiis", $jumlah, $total, $user_id, $produk_id, $size);
        mysqli_stmt_execute($stmt);
    }
}

// Sync keranjang dari database saat halaman dimuat
syncCartFromDatabase($koneksi, $user_id);

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
        
        // Update session
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
        
        // Simpan ke database
        saveCartToDatabase($koneksi, $user_id, $produk_id, $size, 1, $harga_final);
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
    
    if (isset($_SESSION['keranjang'][$cart_key])) {
        $item = $_SESSION['keranjang'][$cart_key];
        $produk_id = $item['id'];
        $size = $item['size'];
        
        // Hapus dari session
        unset($_SESSION['keranjang'][$cart_key]);
        
        // Hapus dari database
        $delete_query = "DELETE FROM keranjang WHERE user_id = ? AND produk_id = ? AND size = ?";
        $stmt = mysqli_prepare($koneksi, $delete_query);
        mysqli_stmt_bind_param($stmt, "iis", $user_id, $produk_id, $size);
        mysqli_stmt_execute($stmt);
    }
    
    header("Location: keranjang.php");
    exit;
}

// Handle quantity update
if (isset($_GET['action']) && $_GET['action'] == 'update' && isset($_GET['key']) && isset($_GET['qty'])) {
    $cart_key = $_GET['key'];
    $qty = (int)$_GET['qty'];
    
    if (isset($_SESSION['keranjang'][$cart_key])) {
        $item = $_SESSION['keranjang'][$cart_key];
        $produk_id = $item['id'];
        $size = $item['size'];
        
        if ($qty > 0) {
            // Update session
            $_SESSION['keranjang'][$cart_key]['jumlah'] = $qty;
            
            // Update database
            updateCartInDatabase($koneksi, $user_id, $produk_id, $size, $qty);
        } else {
            // Hapus dari session
            unset($_SESSION['keranjang'][$cart_key]);
            
            // Hapus dari database
            updateCartInDatabase($koneksi, $user_id, $produk_id, $size, 0);
        }
    }
    header("Location: keranjang.php");
    exit;
}

// Handle AJAX requests untuk update real-time
if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    switch($_POST['ajax_action']) {
        case 'update_quantity':
    $cart_key = $_POST['cart_key'];
    $qty = (int)$_POST['qty'];
    
    if (isset($_SESSION['keranjang'][$cart_key])) {
        $item = $_SESSION['keranjang'][$cart_key];
        $produk_id = $item['id'];
        $size = $item['size'];
        
        // Cek stok produk
        $stok_query = "SELECT stok FROM produk WHERE produk_id = ?";
        $stmt = mysqli_prepare($koneksi, $stok_query);
        mysqli_stmt_bind_param($stmt, "i", $produk_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $produk = mysqli_fetch_assoc($result);
        
        if ($produk && $qty <= $produk['stok']) {
            $_SESSION['keranjang'][$cart_key]['jumlah'] = $qty;
            updateCartInDatabase($koneksi, $user_id, $produk_id, $size, $qty);
            
            $new_total = $item['harga'] * $qty;
            echo json_encode(['success' => true, 'new_total' => number_format($new_total, 0, ',', '.')]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Item tidak ditemukan']);
    }
    exit;
            
        case 'get_cart_total':
            $total_semua = 0;
            $total_items = 0;
            foreach ($_SESSION['keranjang'] as $item) {
                $total = $item['harga'] * $item['jumlah'];
                $total_semua += $total;
                $total_items += $item['jumlah'];
            }
            echo json_encode([
                'total_amount' => number_format($total_semua, 0, ',', '.'),
                'total_items' => $total_items
            ]);
            exit;
    }
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
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .cart-item.removing {
            animation: slideOut 0.3s ease-out forwards;
        }
        
        @keyframes slideOut {
            to {
                transform: translateX(-100%);
                opacity: 0;
            }
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4ade80;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.error {
            background: #ef4444;
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
        <span class="items-count" id="items-count"><?= $total_items ?> Items</span>
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
        <div class="cart-items" id="cart-items">
            <?php
            $total_semua = 0;
            foreach ($_SESSION['keranjang'] as $cart_key => $item):
                $total = $item['harga'] * $item['jumlah'];
                $total_semua += $total;
            ?>
            <div class="cart-item" data-cart-key="<?= htmlspecialchars($cart_key) ?>">
                <img src="../<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama']) ?>" class="product-image">
                
                <div class="product-info">
                    <div class="product-name"><?= htmlspecialchars($item['nama']) ?></div>
                    <div class="product-meta">
                        <div class="product-category">Kategori Produk</div>
                        <span class="size-badge">Size: <?= htmlspecialchars($item['size']) ?></span>
                    </div>
                    <div class="product-description">
                        Unit: Rp<?= number_format($item['harga'], 0, ',', '.') ?> x <span class="item-quantity"><?= $item['jumlah'] ?></span>
                    </div>
                    
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantityAjax('<?= $cart_key ?>', <?= $item['jumlah'] - 1 ?>)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantity-display"><?= $item['jumlah'] ?></span>
                        <button class="quantity-btn" onclick="updateQuantityAjax('<?= $cart_key ?>', <?= $item['jumlah'] + 1 ?>)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="product-actions">
                    <div class="price-info">
                        <div class="unit-price">@Rp<?= number_format($item['harga'], 0, ',', '.') ?></div>
                        <div class="product-price total-price item-total">Rp<?= number_format($total, 0, ',', '.') ?></div>
                    </div>
                    <div class="action-buttons">
                        <!-- <button class="simpan-btn" onclick="saveToWishlist('<?= $cart_key ?>')">Simpan</button> -->
                        <a href="checkout.php?key=<?= $cart_key ?>" class="checkout-btn-small">Checkout</a>
                    </div>
                </div>

                <button onclick="removeItemAjax('<?= $cart_key ?>')" class="hapus-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Checkout Box -->
        <div class="checkout-box">
            <div class="checkout-summary">
                <div class="summary-row">
                    <span>Subtotal (<span id="total-items-display"><?= $total_items ?></span> items):</span>
                    <span id="subtotal-display">Rp<?= number_format($total_semua, 0, ',', '.') ?></span>
                </div>
                <div class="summary-row">
                    <span>Ongkos Kirim:</span>
                    <span class="text-success">GRATIS</span>
                </div>
                <hr>
                <div class="summary-row total-row">
                    <span class="total-label">Total:</span>
                    <span class="total-amount" id="total-amount-display">Rp<?= number_format($total_semua, 0, ',', '.') ?></span>
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
// Fungsi untuk menampilkan notifikasi
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => document.body.removeChild(notification), 300);
    }, 3000);
}

// Update quantity dengan AJAX
function updateQuantityAjax(cartKey, newQty) {
    // Minimal 1, tidak boleh 0 atau negatif
    newQty = Math.max(1, newQty);
    
    const cartItem = document.querySelector(`[data-cart-key="${cartKey}"]`);
    cartItem.classList.add('loading');
    
    const formData = new FormData();
    formData.append('ajax_action', 'update_quantity');
    formData.append('cart_key', cartKey);
    formData.append('qty', newQty);
    
    fetch('keranjang.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update quantity display
            const quantityDisplay = cartItem.querySelector('.quantity-display');
            const currentQty = parseInt(quantityDisplay.textContent);
            
            // Jika stok tidak mencukupi, kembalikan ke jumlah sebelumnya
            if (newQty > currentQty && data.message === 'Stok tidak mencukupi') {
                showNotification(data.message, 'error');
                quantityDisplay.textContent = currentQty;
                cartItem.querySelector('.item-quantity').textContent = currentQty;
            } else {
                quantityDisplay.textContent = newQty;
                cartItem.querySelector('.item-quantity').textContent = newQty;
                cartItem.querySelector('.item-total').textContent = 'Rp' + data.new_total;
                showNotification('Keranjang berhasil diperbarui');
            }
            
            // Update total
            updateCartTotal();
        } else {
            showNotification(data.message || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan', 'error');
    })
    .finally(() => {
        cartItem.classList.remove('loading');
    });
}

// Remove item dengan AJAX
// Fungsi khusus untuk hapus item
function removeItemAjax(cartKey) {
    if (confirm('Yakin ingin menghapus produk ini dari keranjang?')) {
        const cartItem = document.querySelector(`[data-cart-key="${cartKey}"]`);
        cartItem.classList.add('removing');
        
        fetch(`keranjang.php?action=hapus&key=${encodeURIComponent(cartKey)}`)
        .then(response => {
            if (response.ok) {
                setTimeout(() => {
                    cartItem.remove();
                    updateCartTotal();
                    
                    // Check if cart is empty
                    if (document.querySelectorAll('.cart-item').length === 0) {
                        location.reload();
                    }
                }, 300);
                showNotification('Produk berhasil dihapus dari keranjang');
            } else {
                cartItem.classList.remove('removing');
                showNotification('Gagal menghapus produk', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            cartItem.classList.remove('removing');
            showNotification('Terjadi kesalahan', 'error');
        });
    }
}

function updateCartTotal() {
    fetch('keranjang.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'ajax_action=get_cart_total'
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('total-items-display').textContent = data.total_items;
        document.getElementById('items-count').textContent = data.total_items + ' Items';
        document.getElementById('subtotal-display').textContent = 'Rp' + data.total_amount;
        document.getElementById('total-amount-display').textContent = 'Rp' + data.total_amount;
    })
    .catch(error => {
        console.error('Error updating total:', error);
    });
}

function saveToWishlist(cartKey) {
    showNotification('Produk berhasil disimpan ke wishlist!');
}

function updateQuantity(cartKey, newQty) {
    updateQuantityAjax(cartKey, newQty);
}

function saveCart() {
    console.log('Cart saved to database');
}

document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.checkout-btn, .checkout-btn-small');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            if (!this.classList.contains('simpan-btn')) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                this.style.pointerEvents = 'none';
            }
        });
    });
    const cartItems = document.querySelectorAll('.cart-item');
    cartItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        item.classList.add('fade-in');
    });
});

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