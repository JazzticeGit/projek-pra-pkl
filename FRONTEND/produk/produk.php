<?php
session_start();
include '../../koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../FRONTEND/login.php");
    exit;
}

include '../../BACKEND/diskon/end-date.php';

// Initialize cart session
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Add to cart handler
if (isset($_GET['action']) && $_GET['action'] === 'add' && isset($_GET['id'])) {
    $produk_id = (int)$_GET['id'];

    // Cek login
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Silakan login terlebih dahulu";
        header("Location: ../../login.php");
        exit;
    }

    // Ambil data produk + diskon jika ada
    $query = "
        SELECT p.*, d.persen_diskon
        FROM produk p
        LEFT JOIN diskon d ON p.produk_id = d.produk_id 
            AND d.status = 'active' 
            AND NOW() BETWEEN d.start_date AND d.end_date
        WHERE p.produk_id = ?
    ";

    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $produk_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $produk = mysqli_fetch_assoc($result);

    if ($produk) {
        $harga_asli = $produk['harga'];
        $persen_diskon = $produk['persen_diskon'] ?? 0;
        $harga_final = ($persen_diskon > 0) ? $harga_asli * (1 - $persen_diskon / 100) : $harga_asli;

        $user_id = $_SESSION['user_id'];
        $size = 'default';
        $color = 'default';

        // Cek apakah produk sudah ada di keranjang
        $check_query = "SELECT * FROM keranjang WHERE user_id = ? AND produk_id = ? AND size = ?";
        $check_stmt = mysqli_prepare($koneksi, $check_query);
        mysqli_stmt_bind_param($check_stmt, "iis", $user_id, $produk_id, $size);
        mysqli_stmt_execute($check_stmt);
        $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($check_stmt));

        if ($existing) {
            // Update jumlah dan total
            $update_query = "UPDATE keranjang 
                             SET jumlah = jumlah + 1, total = total + ? 
                             WHERE user_id = ? AND produk_id = ? AND size = ?";
            $update_stmt = mysqli_prepare($koneksi, $update_query);
            mysqli_stmt_bind_param($update_stmt, "diis", $harga_final, $user_id, $produk_id, $size);
            mysqli_stmt_execute($update_stmt);
        } else {
            // Insert ny
            $insert_query = "INSERT INTO keranjang (user_id, produk_id, jumlah, size, total)
                 VALUES (?, ?, 1, ?, ?)";
$insert_stmt = mysqli_prepare($koneksi, $insert_query);
mysqli_stmt_bind_param($insert_stmt, "iisd", $user_id, $produk_id, $size, $harga_final);
mysqli_stmt_execute($insert_stmt);

        }

        //  session keranjang 
        $cart_key = $produk_id . "_$size" ;
        if (isset($_SESSION['keranjang'][$cart_key])) {
            $_SESSION['keranjang'][$cart_key]['jumlah'] += 1;
        } else {
            $_SESSION['keranjang'][$cart_key] = [
                'id' => $produk['produk_id'],
                'nama' => $produk['name'],
                'harga' => $harga_final,
                'gambar' => $produk['image'],
                'jumlah' => 1,
                'size' => $size,
            ];
        }

        $_SESSION['success'] = "Produk berhasil ditambahkan ke keranjang";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $_SESSION['error'] = "Produk tidak ditemukan.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Query produk best seller + cek diskon aktif
$query = "
SELECT p.*, 
       d.persen_diskon, 
       d.start_date, 
       d.end_date
FROM produk p
LEFT JOIN diskon d ON p.produk_id = d.produk_id 
    AND d.status = 'active' 
    AND NOW() BETWEEN d.start_date AND d.end_date
WHERE p.best_seller = 1
";
$result = mysqli_query($koneksi, $query);

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Best Seller</title>
    <link rel="stylesheet" href="../../STYLESHEET/produk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        .original-price {
            text-decoration: line-through;
            color: #999;
            margin-right: 8px;
        }
        .discounted-price {
            color: #e74c3c;
            font-weight: bold;
        }
        .product-price {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        .product-card {
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .product-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 10px;
        }
        .view-detail-btn {
            flex: 1;
            background: #007bff;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .view-detail-btn:hover {
            background: #0056b3;
            color: white;
        }
        .quick-add-btn {
            background: #28a745;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .quick-add-btn:hover {
            background: #1e7e34;
        }
        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            opacity: 0;
            transition: opacity 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }
        .product-card:hover .product-overlay {
            opacity: 1;
        }
        .overlay-btn {
            background: white;
            color: #333;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.3s;
        }
        .overlay-btn:hover {
            transform: scale(1.05);
            color: #333;
        }
        .product-image-container {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<!-- NAVIGASI -->
<nav>
    <div class="navbg">
        <a href="../index.php"><img src="../../image/AGESA.png" alt=""></a>
        <div class="navlink">
            <ul>
                <li><a href="all-produk.php">Shop</a></li>
                <li><a href="../produk/colection.php">Collection</a></li>
                <li><a href="../about.html">About</a></li>
                <li><a href="../index.php#footer">Contact</a></li>
<<<<<<< HEAD
                <li><a href="">Transaksi</a></li>
=======
                    <li><a href="../FRONTEND/riwayat-transaksi.php">Transaksi</a></li>
>>>>>>> f312364d8ab9ea281d28f4ab313e37f5d29b3f21
            </ul>
        </div>
        <div class="searchBar">
            <form action="../FRONTEND/search.php" method="GET">
                <input type="text" name="query" placeholder="Search" required>
                <i class="fas fa-search"></i>
            </form>
        </div>
        <div class="iconLink">
            <ul>
                <li><a href="../keranjang.php" class="fa-solid fa-cart-shopping"></a></li>
                <li><a href="../FRONTEND/profile.php" class="fa-solid fa-user"></a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- HEADER -->
<header class="pricing-header">
    <div class="container">
        <div class="pricing-label">Product</div>
        <h1 class="header-title">Items Terlaris Saat Ini<br>Jangan Ketinggalan</h1>
    </div>
</header>

<!-- PRODUK -->
<div class="product-grid">
    <?php while ($produk = mysqli_fetch_assoc($result)): ?>
        <?php
        $harga_asli = $produk['harga'];
        $persen_diskon = $produk['persen_diskon'];
        $harga_diskon = ($persen_diskon && $persen_diskon > 0) ? $harga_asli * (1 - $persen_diskon / 100) : $harga_asli;
        ?>
        <div>
            <div class="product-card">
                <div class="product-image-container">
                    <img src="../../<?= htmlspecialchars($produk['image']) ?>" alt="<?= htmlspecialchars($produk['name']) ?>" class="product-image">
                    
                    <!-- Overlay saat hover -->
                    <div class="product-overlay">
                        <a href="detail-produk.php?id=<?= $produk['produk_id'] ?>" class="overlay-btn">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                    </div>
                </div>

                <div class="product-content">
                    <h2 class="product-title"><?= htmlspecialchars($produk['name']) ?></h2>
                    <p class="product-description"><?= htmlspecialchars(substr($produk['deskripsi'] ?? 'Deskripsi belum tersedia.', 0, 100)) ?>...</p>

                    <div class="product-footer">
                        <div class="product-price">
                            <?php if ($persen_diskon && $persen_diskon > 0): ?>
                                <span class="original-price">Rp<?= number_format($harga_asli, 0, ',', '.') ?></span>
                                <span class="discounted-price">Rp<?= number_format($harga_diskon, 0, ',', '.') ?></span>
                            <?php else: ?>
                                Rp<?= number_format($harga_asli, 0, ',', '.') ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <!-- <a href="detail-produk.php?id=<?= $produk['produk_id'] ?>" class="view-detail-btn">
                                <i class="fas fa-eye"></i> Detail
                            </a> -->
                            <a href="?action=add&id=<?= $produk['produk_id'] ?>" class="quick-add-btn" onclick="return confirm('Tambahkan produk ke keranjang?')">
                                <i class="fa-solid fa-cart-plus"></i>  Keranjang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script>
// Add click event to product cards
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', function(e) {
        // Don't navigate if clicking on buttons
        if (e.target.closest('.product-actions') || e.target.closest('.overlay-btn')) {
            return;
        }
        
        // Get product ID from the detail link
        const detailLink = card.querySelector('.view-detail-btn');
        if (detailLink) {
            window.location.href = detailLink.href;
        }
    });
});

// Add loading state to quick add buttons
document.querySelectorAll('.quick-add-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menambahkan...';
        this.style.pointerEvents = 'none';
        
        // Simulate loading then redirect
        setTimeout(() => {
            window.location.href = this.href;
        }, 500);
    });
});
</script>

</body>
</html>