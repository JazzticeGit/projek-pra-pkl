<?php
session_start();
include '../../koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../FRONTEND/login.php");
    exit;
}

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $produk_id = (int)$_GET['id'];

    // Cek produk dan diskon aktif
    $query = "
        SELECT p.*, d.persen_diskon, d.start_date, d.end_date
        FROM produk p
        LEFT JOIN diskon d ON p.produk_id = d.produk_id 
            AND d.status = 'active' 
            AND NOW() BETWEEN d.start_date AND d.end_date
        WHERE p.produk_id = $produk_id
    ";
    $produk_query = mysqli_query($koneksi, $query);
    $produk = mysqli_fetch_assoc($produk_query);

    if ($produk) {
        // Hitung harga diskon jika ada diskon aktif
        $harga_asli = $produk['harga'];
        $persen_diskon = $produk['persen_diskon'];
        if ($persen_diskon && $persen_diskon > 0) {
            $harga_final = $harga_asli * (1 - $persen_diskon / 100);
        } else {
            $harga_final = $harga_asli;
        }

        // Default size dan color untuk add to cart langsung dari halaman produk
        $cart_key = $produk_id . '_default_default';

        if (isset($_SESSION['keranjang'][$cart_key])) {
            $_SESSION['keranjang'][$cart_key]['jumlah'] += 1;
        } else {
            $_SESSION['keranjang'][$cart_key] = [
                'id' => $produk['produk_id'],
                'nama' => $produk['name'],
                'harga' => $harga_final,
                'gambar' => $produk['image'],
                'jumlah' => 1,
                'size' => 'default',
                'color' => 'default'
            ];
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Query kaos
$query_kaos = "
SELECT p.*, 
       d.persen_diskon, 
       d.start_date, 
       d.end_date
FROM produk p
LEFT JOIN diskon d ON p.produk_id = d.produk_id 
    AND d.status = 'active' 
    AND NOW() BETWEEN d.start_date AND d.end_date
WHERE p.id_kategori = 1
";
// Query kemeja
$query_kemeja = "
SELECT p.*, 
       d.persen_diskon, 
       d.start_date, 
       d.end_date
FROM produk p
LEFT JOIN diskon d ON p.produk_id = d.produk_id 
    AND d.status = 'active' 
    AND NOW() BETWEEN d.start_date AND d.end_date
WHERE p.id_kategori = 2
";
// Query jaket
$query_jaket = "
SELECT p.*, 
       d.persen_diskon, 
       d.start_date, 
       d.end_date
FROM produk p
LEFT JOIN diskon d ON p.produk_id = d.produk_id 
    AND d.status = 'active' 
    AND NOW() BETWEEN d.start_date AND d.end_date
WHERE p.id_kategori = 3
";
$result_kaos = mysqli_query($koneksi, $query_kaos);
$result_kemeja = mysqli_query($koneksi, $query_kemeja);
$result_jaket = mysqli_query($koneksi, $query_jaket);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Best Seller</title>
    <link rel="stylesheet" href="../../STYLESHEET/produk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
     <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Bebas Neue' rel='stylesheet'>
</head>

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


<body>

    <nav>
        
        <div class="navbg">
            <!-- GAMBAR NAVIGASI -->
             <a href="../index.php"><img src="../../image/AGESA.png" alt="" srcset=""></a>


             <!-- LINK NAVIGASI -->
            <div class="navlink">
                <ul>
                    <li><a href="all-produk.php">Shop</a></li>  <!-- SEMENTARA SEBELUM DROPDoWN LINK -->
                    <li><a href="../produk/colection.php">Collection</a></li>
                    <li><a href="../about.html">About</a></li>
                    <li><a href="../index.php #footer">Contack</a></li>
                    <li><a href="riwayat-transaksi.php">Transaksi</a></li>
                </ul>
            </div>


            <!-- SEARCH BAR -->
            <div class="searchBar">
                <form action="search.php" method="GET">
                <input type="text" name="query" placeholder="   Search  " required>
                <i class="fas fa-search"></i>
                <!-- <button type="submit">Cari</button> -->
                </form>
                
            </div>


            <!-- ICON LINK -->
             <div class="iconLink">
             <ul>
                <li><a href="../keranjang.php" class="fa-solid fa-cart-shopping"></a></li> <!-- CART SHOPING LINK -->
                <li><a href="http://" class="fa-solid fa-user"></a></li> <!-- ACCOUNT LINK -->
             </ul>
             </div>
        </div>
    </nav>


<header class="pricing-header">
        <div class="container">
            <div class="pricing-label">Product</div>
            <h1 class="header-title">Items Terlaris Saat Ini<br>Jangan Ketinggalan</h1>
        </div>
    </header>


<div class="label-produk">T-shirt <i class="fa-solid fa-chevron-down"></i></div>
<!-- PRODUK -->
<div class="product-grid">
    <?php while ($produk = mysqli_fetch_assoc($result_kaos)): ?>
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

<div class="label-produk">Kemeja <i class="fa-solid fa-chevron-down"></i></div>
<div class="product-grid">
   <!-- PRODUK -->
<div class="product-grid">
    <?php while ($produk = mysqli_fetch_assoc($result_kemeja)): ?>
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

<div class="label-produk">Jacket <i class="fa-solid fa-chevron-down"></i></div>
<!-- PRODUK -->
<div class="product-grid">
    <?php while ($produk = mysqli_fetch_assoc($result_jaket)): ?>
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
