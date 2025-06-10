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

$query = "SELECT * FROM produk WHERE new_arrival = 1";
$result = mysqli_query($koneksi, $query);
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
                    <li><a href="../FRONTEND/riwayat-transaksi.php">Transaksi</a></li>
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
                <li><a href="../FRONTEND/profile.php" class="fa-solid fa-user"></a></li> <!-- ACCOUNT LINK -->
             </ul>
             </div>
        </div>
    </nav>


<header class="pricing-header">
        <div class="container">
            <div class="pricing-label">New arrival</div>
            <h1 class="header-title">Items terbaru kami<br>stok terbatas!</h1>
        </div>
    </header>


<div class="product-grid">
    <?php while ($produk = mysqli_fetch_assoc($result)): ?>
        <div><a href="detail-produk.php?<?= $produk['produk_id'] ?>">
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
                    <a href="../keranjang.php?action=add&id=<?= $produk['produk_id'] ?>" class="add-to-cart-btn">
                        <i class="fa-solid fa-cart-plus"></i> Add to cart
                    </a>
                </div>
            </div>
        </div>
    </a></div>
    <?php endwhile; ?>
</div>

</body>
</html>
