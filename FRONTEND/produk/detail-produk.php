<?php
session_start();
include '../../koneksi.php';
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
//     header("Location: ../FRONTEND/login.php");
//     exit;
// }

// Get product ID from URL first (moved to top)
$produk_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($produk_id == 0) {
    header("Location: produk.php");
    exit;
}

// Query for product details with discount (synchronized with database)
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

// DEBUG: Handle add to cart - DETAILED DEBUGGING VERSION
echo "<!-- DEBUG: REQUEST_METHOD = " . $_SERVER['REQUEST_METHOD'] . " -->";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<!-- DEBUG: POST REQUEST DETECTED -->";
    echo "<!-- DEBUG: POST DATA: " . print_r($_POST, true) . " -->";
    echo "<!-- DEBUG: SESSION DATA: " . print_r($_SESSION, true) . " -->";
    
    if (isset($_POST['add_to_cart'])) {
        echo "<!-- DEBUG: ADD_TO_CART BUTTON CLICKED -->";
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo "<!-- DEBUG: USER NOT LOGGED IN -->";
            $_SESSION['error'] = 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang';
            header("Location: ../../FRONTEND/login.php");
            exit;
        }
        
        echo "<!-- DEBUG: USER LOGGED IN - ID: " . $_SESSION['user_id'] . " -->";
        
        $user_id = $_SESSION['user_id'];
        $produk_id_post = (int)$_POST['produk_id'];
        $size = mysqli_real_escape_string($koneksi, $_POST['size']);
        $jumlah = (int)$_POST['jumlah'];
        
        echo "<!-- DEBUG: PROCESSED DATA - User ID: $user_id, Produk ID: $produk_id_post, Size: $size, Jumlah: $jumlah -->";
        
        // Validate input
        if ($produk_id_post <= 0 || $jumlah <= 0 || empty($size)) {
            echo "<!-- DEBUG: VALIDATION FAILED -->";
            $_SESSION['error'] = 'Data produk tidak valid';
        } else {
            echo "<!-- DEBUG: VALIDATION PASSED -->";
            
            // Check if item already exists in cart
            $check_query = "SELECT * FROM keranjang WHERE user_id = $user_id AND produk_id = $produk_id_post AND size = '$size' AND status = 'aktif'";
            echo "<!-- DEBUG: CHECK QUERY: $check_query -->";
            
            $check_result = mysqli_query($koneksi, $check_query);
            
            if (!$check_result) {
                echo "<!-- DEBUG: CHECK QUERY FAILED: " . mysqli_error($koneksi) . " -->";
                $_SESSION['error'] = 'Database error: ' . mysqli_error($koneksi);
            } else {
                echo "<!-- DEBUG: CHECK QUERY SUCCESS - ROWS: " . mysqli_num_rows($check_result) . " -->";
                
                if (mysqli_num_rows($check_result) > 0) {
                    echo "<!-- DEBUG: ITEM EXISTS - UPDATING -->";
                    // Update existing item
                    $existing = mysqli_fetch_assoc($check_result);
                    $new_jumlah = $existing['jumlah'] + $jumlah;
                    $new_total = $new_jumlah * $produk['harga'];
                    
                    $update_query = "UPDATE keranjang SET jumlah = $new_jumlah, total = $new_total WHERE id = {$existing['id']}";
                    echo "<!-- DEBUG: UPDATE QUERY: $update_query -->";
                    
                    if (mysqli_query($koneksi, $update_query)) {
                        echo "<!-- DEBUG: UPDATE SUCCESS -->";
                        $_SESSION['success'] = 'Produk berhasil ditambahkan ke keranjang';
                    } else {
                        echo "<!-- DEBUG: UPDATE FAILED: " . mysqli_error($koneksi) . " -->";
                        $_SESSION['error'] = 'Gagal menambahkan produk ke keranjang: ' . mysqli_error($koneksi);
                    }
                } else {
                    echo "<!-- DEBUG: NEW ITEM - INSERTING -->";
                    // Insert new item
                    $total = $jumlah * $produk['harga'];
                    
                    $insert_query = "INSERT INTO keranjang (produk_id, size, jumlah, total, user_id, status) 
                                   VALUES ($produk_id_post, '$size', $jumlah, $total, $user_id, 'aktif')";
                    echo "<!-- DEBUG: INSERT QUERY: $insert_query -->";
                    
                    if (mysqli_query($koneksi, $insert_query)) {
                        echo "<!-- DEBUG: INSERT SUCCESS -->";
                        $_SESSION['success'] = 'Produk berhasil ditambahkan ke keranjang';
                    } else {
                        echo "<!-- DEBUG: INSERT FAILED: " . mysqli_error($koneksi) . " -->";
                        $_SESSION['error'] = 'Gagal menambahkan produk ke keranjang: ' . mysqli_error($koneksi);
                    }
                }
            }
        }
        
        echo "<!-- DEBUG: PROCESSING COMPLETE - REDIRECTING -->";
        
        // Handle redirect
        if (isset($_POST['redirect']) && $_POST['redirect'] === 'checkout') {
            header("Location: ../../FRONTEND/checkout.php");
        } else {
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $produk_id);
        }
        exit;
    } else {
        echo "<!-- DEBUG: POST REQUEST BUT NO add_to_cart PARAMETER -->";
    }
} else {
    echo "<!-- DEBUG: NOT A POST REQUEST -->";
}

// Calculate prices
$harga_asli = $produk['harga'];
$persen_diskon = $produk['persen_diskon'];
$harga_diskon = ($persen_diskon && $persen_diskon > 0) ? $harga_asli * (1 - $persen_diskon / 100) : $harga_asli;

// Parse size options from enum
$size_options = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'];

// Get related products (fixed column name)
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

    <nav>
        
        <div class="navbg">
            <!-- GAMBAR NAVIGASI -->
             <a href="index.php"><img src="../../image/AGESA.png" alt="" srcset=""></a>


             <!-- LINK NAVIGASI -->
            <div class="navlink">
                <ul>
                    <li><a href="produk.php">Shop</a></li>  <!-- SEMENTARA SEBELUM DROPDoWN LINK -->
                    <li><a href="colection.php">Collection</a></li>
                    <li><a href="../about.html">About</a></li>
                    <li><a href="http://">Contack</a></li>
                </ul>
            </div>


            <!-- SEARCH BAR -->
            <div class="searchBar">
                <form action="../search.php" method="GET">
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

    <div class="container mt-4">
        <!-- Error/Success Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Breadcrumb -->


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
                    <span class="badge <?php echo $produk['stok'] > 0 ? 'bg-success' : 'bg-danger'; ?> ms-2">
                        <i class="fas fa-<?php echo $produk['stok'] > 0 ? 'check' : 'times'; ?>"></i> 
                        Stok: <?php echo $produk['stok']; ?>
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
                <?php if ($produk['stok'] > 0): ?>
                <!-- DEBUG: Form akan submit ke halaman ini -->
                <!-- <div class="alert alert-info">
                    <small><strong>DEBUG:</strong> Form action: <?php echo $_SERVER['PHP_SELF'] . '?id=' . $produk_id; ?></small>
                </div> -->
                
                <form method="POST" action="" id="addToCartForm" onsubmit="return validateForm()">
                    <input type="hidden" name="produk_id" value="<?php echo $produk['produk_id']; ?>">
                    <input type="hidden" name="add_to_cart" value="1">

                    <!-- Ukuran -->
                    <div class="form-group mb-3">
                        <label for="size" class="form-label">Ukuran:</label>
                        <select name="size" id="size" class="form-select" required>
                            <option value="">Pilih Ukuran</option>
                            <?php foreach($size_options as $size_opt): ?>
                                <option value="<?php echo $size_opt; ?>"><?php echo $size_opt; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="size-error" class="text-danger" style="display: none;">Silakan pilih ukuran!</div>
                    </div>

                    <!-- Jumlah -->
                    <div class="form-group mb-3">
                        <label for="jumlah" class="form-label">Jumlah:</label>
                        <input type="number" name="jumlah" id="jumlah" class="form-control" 
                               value="1" min="1" max="<?php echo $produk['stok']; ?>" required>
                        <div id="jumlah-error" class="text-danger" style="display: none;">Jumlah harus minimal 1!</div>
                    </div>

                    <!-- Tombol -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary me-2" id="addToCartBtn">
                            <i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang
                        </button>
                        <button type="button" class="btn btn-success" onclick="buyNow()" id="buyNowBtn">
                            <i class="fas fa-bolt me-2"></i>Beli Sekarang
                        </button>
                    </div>
                </form>
                <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Produk ini sedang tidak tersedia
                </div>
                <?php endif; ?>
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
                        <!-- <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" 
                                data-bs-target="#reviews" type="button" role="tab">
                            Ulasan
                        </button> -->
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
                                <tr><td><strong>Stok</strong></td>< td><?php echo $produk['stok']; ?> pcs</td></tr>
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
                            <img src="../../<?php echo htmlspecialchars($related['image']); ?>" 
                                 class="card-img-top" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($related['name']); ?></h6>
                                <p class="card-text text-primary fw-bold">
                                    Rp<?php echo number_format($related['harga'], 0, ',', '.'); ?>
                                </p>
                                <a href="detail-produk.php?id=<?php echo $related['produk_id']; ?>" 
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
        // DEBUG: Check if JavaScript is loaded
        console.log('JavaScript loaded successfully');
        
        // Validation function
        function validateForm() {
            console.log('validateForm() called');
            
            const size = document.getElementById('size').value;
            const jumlah = document.getElementById('jumlah').value;
            
            console.log('Size:', size);
            console.log('Jumlah:', jumlah);
            
            let isValid = true;
            
            // Reset error messages
            document.getElementById('size-error').style.display = 'none';
            document.getElementById('jumlah-error').style.display = 'none';
            
            // Validate size
            if (!size) {
                document.getElementById('size-error').style.display = 'block';
                isValid = false;
            }
            
            // Validate jumlah
            if (!jumlah || jumlah < 1) {
                document.getElementById('jumlah-error').style.display = 'block';
                isValid = false;
            }
            
            if (isValid) {
                // Show loading state
                const submitBtn = document.getElementById('addToCartBtn');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menambahkan...';
                submitBtn.disabled = true;
                
                console.log('Form validation passed, submitting...');
            } else {
                console.log('Form validation failed');
            }
            
            return isValid;
        }
        
        function buyNow() {
            console.log('buyNow() called');
            
            // Check if user is logged in first
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert('Silakan login terlebih dahulu');
                window.location.href = '../../FRONTEND/login.php';
                return;
            <?php endif; ?>

            const size = document.getElementById("size").value;
            const jumlah = document.getElementById("jumlah").value;
            
            if (!size) {
                alert("Silakan pilih ukuran terlebih dahulu.");
                return;
            }
            
            if (!jumlah || jumlah < 1) {
                alert("Silakan masukkan jumlah yang valid.");
                return;
            }

            // Show loading state
            const buyBtn = document.getElementById('buyNowBtn');
            buyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            buyBtn.disabled = true;
            
            // Create form data
            const form = document.getElementById('addToCartForm');
            const redirectInput = document.createElement("input");
            redirectInput.type = "hidden";
            redirectInput.name = "redirect";
            redirectInput.value = "checkout";
            form.appendChild(redirectInput);
            
            console.log('Submitting buy now form...');
            
            // Submit form
            form.submit();
        }

        // Add form submit event listener for debugging
        document.getElementById('addToCartForm').addEventListener('submit', function(e) {
            console.log('Form submit event triggered');
            console.log('Form data:', new FormData(this));
            
            // Log all form data
            const formData = new FormData(this);
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-info)');
            alerts.forEach(alert => {
                if (alert.classList.contains('show')) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 300);
                }
            });
        }, 5000);
        
        // Test form submission - DEBUG ONLY
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            console.log('Form element:', document.getElementById('addToCartForm'));
            console.log('Submit button:', document.getElementById('addToCartBtn'));
        });
    </script>
</body>
</html>