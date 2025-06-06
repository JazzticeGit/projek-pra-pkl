<?php
session_start();
require_once '../../koneksi.php';

// Pada bagian add to cart handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $produk_id = (int)$_POST['produk_id'];
    $size = $_POST['size'] ?? '';
    
    // Validasi
    if ($produk_id <= 0 || empty($size)) {
        die("Data tidak valid");
    }

    // Pastikan user sudah login
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Silakan login terlebih dahulu";
        header("Location: login.php");
        exit;
    }

    // Query produk dengan nama tabel yang benar
    $query = "SELECT p.*, d.persen_diskon 
              FROM produk p 
              LEFT JOIN diskon d ON p.id = d.produk_id 
                  AND d.status = 'active' 
                  AND NOW() BETWEEN d.start_date AND d.end_date 
              WHERE p.id = ?";
    
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $produk_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $produk = mysqli_fetch_assoc($result);
    
    if (!$produk) {
        die("Produk tidak ditemukan");
    }

    // Hitung harga
    $harga_final = ($produk['persen_diskon'] ?? 0) > 0 
        ? $produk['harga'] * (1 - ($produk['persen_diskon'] / 100)) 
        : $produk['harga'];

    // Simpan ke database
    $insert_query = "INSERT INTO keranjang 
                    (produk_id, user_id, size, jumlah, total) 
                    VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $insert_query);
    $user_id = $_SESSION['user_id'];
    $jumlah = 1;
    $total = $harga_final * $jumlah;
    
    mysqli_stmt_bind_param($stmt, "iisid", 
        $produk_id, 
        $user_id, 
        $size, 
        $jumlah, 
        $total
    );
    
    if (mysqli_stmt_execute($stmt)) {
        // Update session
        $cart_key = $produk_id . '_' . $size;
        $_SESSION['keranjang'][$cart_key] = [
            'id' => $produk_id,
            'nama' => $produk['name'],
            'harga' => $harga_final,
            'gambar' => $produk['image'],
            'jumlah' => $jumlah,
            'size' => $size,
            'cart_id' => mysqli_insert_id($koneksi)
        ];
        
        header("Location: keranjang.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}

// Get product ID from URL
$produk_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($produk_id == 0) {
    header("Location: produk.php");
    exit;
}

// Query for product details with discount
$query = "
    SELECT p.*, k.jenis_produk,
           d.persen_diskon, d.start_date, d.end_date
    FROM produk p
    LEFT JOIN kategori k ON p.id_kategori = k.id
    LEFT JOIN diskon d ON p.produk_id = d.produk_id 
         AND d.status = 'active' 
         AND NOW() BETWEEN d.start_date AND d.end_date
    WHERE p.produk_id = $produk_id
";

$result = mysqli_query($koneksi, $query);
$produk = mysqli_fetch_assoc($result);

if (!$produk) {
    header("Location: produk.php");
    exit;
}

// Calculate prices
$harga_asli = $produk['harga'];
$persen_diskon = $produk['persen_diskon'];
$harga_diskon = ($persen_diskon && $persen_diskon > 0) ? $harga_asli * (1 - $persen_diskon / 100) : $harga_asli;

// Parse size options from enum
$size_options = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'];

// Get related products
$related_query = "SELECT * FROM produk WHERE id_kategori = {$produk['id_kategori']} AND produk_id != $produk_id LIMIT 4";
$related_result = mysqli_query($koneksi, $related_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produk['name']); ?> - Toko Online</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../STYLESHEET/detail-produk.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="produk.php">Produk</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($produk['name']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Product Image -->
            <div class="col-lg-6 mb-4">
                <div class="position-relative">
                    <?php if ($persen_diskon && $persen_diskon > 0): ?>
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="discount-badge">-<?php echo $persen_diskon; ?>%</span>
                        </div>
                    <?php endif; ?>
                    <img src="../../<?php echo htmlspecialchars($produk['image']); ?>" 
                         alt="<?php echo htmlspecialchars($produk['name']); ?>" 
                         class="product-image">
                </div>
            </div>

            <!-- Product Details -->
            <div class="col-lg-6">
                <h1 class="h2 mb-3"><?php echo htmlspecialchars($produk['name']); ?></h1>
                
                <div class="mb-3">
                    <span class="badge bg-secondary"><?php echo htmlspecialchars($produk['jenis_produk']); ?></span>
                    <span class="badge bg-success ms-2">
                        <i class="fas fa-check"></i> Stok: <?php echo $produk['stok']; ?>
                    </span>
                </div>

                <!-- Price -->
                <div class="mb-4">
                    <?php if ($persen_diskon && $persen_diskon > 0): ?>
                        <div class="price-original mb-1">Rp<?php echo number_format($harga_asli, 0, ',', '.'); ?></div>
                        <div class="price-discount">Rp<?php echo number_format($harga_diskon, 0, ',', '.'); ?></div>
                        <small class="text-success">Hemat Rp<?php echo number_format($harga_asli - $harga_diskon, 0, ',', '.'); ?></small>
                    <?php else: ?>
                        <div class="price-discount">Rp<?php echo number_format($harga_asli, 0, ',', '.'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Add to Cart Form -->
                <form method="POST" id="addToCartForm">
                    <input type="hidden" name="produk_id" value="<?php echo $produk['produk_id']; ?>">
                    
                    <!-- Size Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih Ukuran:</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach($size_options as $size): ?>
                                <div class="size-option btn btn-outline-secondary btn-sm" 
                                     data-size="<?php echo $size; ?>">
                                    <?php echo $size; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="size" id="selected-size" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-add-cart btn-lg">
                            <i class="fas fa-cart-plus me-2"></i> Tambah ke Keranjang
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="buyNow()">
                            <i class="fas fa-bolt me-2"></i> Beli Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Information Tabs -->
        <div class="row mt-5">
            <div class="col-12">
                <ul class="nav nav-tabs product-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab" 
                                data-bs-target="#description" type="button" role="tab">
                            Deskripsi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" 
                                data-bs-target="#specifications" type="button" role="tab">
                            Spesifikasi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" 
                                data-bs-target="#reviews" type="button" role="tab">
                            Ulasan
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content mt-3" id="productTabsContent">
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <div class="p-3">
                            <p><?php echo nl2br(htmlspecialchars($produk['deskripsi'])); ?></p>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="specifications" role="tabpanel">
                        <div class="p-3">
                            <table class="table table-striped">
                                <tr><td><strong>Kategori</strong></td><td><?php echo htmlspecialchars($produk['jenis_produk']); ?></td></tr>
                                <tr><td><strong>Stok</strong></td><td><?php echo $produk['stok']; ?> pcs</td></tr>
                                <tr><td><strong>Berat</strong></td><td>~500g</td></tr>
                                <tr><td><strong>Material</strong></td><td>Cotton 100%</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <div class="p-3">
                            <div class="text-center text-muted">
                                <i class="fas fa-star-o fa-3x mb-3"></i>
                                <p>Belum ada ulasan untuk produk ini.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (mysqli_num_rows($related_result) > 0): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">Produk Terkait</h3>
                <div class="row">
                    <?php while($related = mysqli_fetch_assoc($related_result)): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card related-product-card h-100">
                            <img src="<?php echo htmlspecialchars($related['image']); ?>" 
                                 class="card-img-top" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($related['name']); ?></h6>
                                <p class="card-text text-primary fw-bold">
                                    Rp<?php echo number_format($related['harga'], 0, ',', '.'); ?>
                                </p>
                                <a href="detail_produk.php?id=<?php echo $related['produk_id']; ?>" 
                                   class="btn btn-outline-primary btn-sm">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Size selection
        document.querySelectorAll('.size-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.size-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selected-size').value = this.dataset.size;
            });
        });

        // Form validation and submission
        document.getElementById('addToCartForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const size = document.getElementById('selected-size').value;
            
            if (!size) {
                alert('Silakan pilih ukuran terlebih dahulu!');
                return;
            }
            
            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menambahkan...';
            submitBtn.disabled = true;
            
            // Send to cart via GET request (to match keranjang.php structure)
            const produkId = document.querySelector('input[name="produk_id"]').value;
            window.location.href = `../keranjang.php?action=add&id=${produkId}&size=${encodeURIComponent(size)}`;
        });
        
        function buyNow() {
            const size = document.getElementById('selected-size').value;
            
            if (!size) {
                alert('Silakan pilih ukuran terlebih dahulu!');
                return;
            }
            
            const produkId = document.querySelector('input[name="produk_id"]').value;
            // First add to cart, then redirect to checkout
            window.location.href = `keranjang.php?action=add&id=${produkId}&size=${encodeURIComponent(size)}&redirect=checkout`;
        }
    </script>
</body>
</html>