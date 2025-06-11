<?php
session_start();
include '../koneksi.php';

// if (!isset($_SESSION['user_id'])) {
//     header("Location: ../login.php");
//     exit;
// }

$user_id = $_SESSION['user_id'];

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $user_id = $_SESSION['user_id'];

    // Soft delete: ubah status jadi 'hapus'
    $query = "UPDATE keranjang SET status = 'hapus' WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['success'] = "Produk berhasil dihapus dari keranjang";
    header("Location: keranjang.php");
    exit;
}

// Tambah jumlah
if (isset($_GET['tambah'])) {
    $id = (int)$_GET['tambah'];
    
    // Get current item data
    $get_query = "SELECT keranjang.*, produk.harga, 
                         COALESCE(d.persen_diskon, 0) as persen_diskon
                  FROM keranjang 
                  JOIN produk ON keranjang.produk_id = produk.produk_id
                  LEFT JOIN diskon d ON produk.produk_id = d.produk_id 
                       AND d.status = 'active' 
                       AND NOW() BETWEEN d.start_date AND d.end_date
                  WHERE keranjang.id = ? AND keranjang.user_id = ?";
    $get_stmt = mysqli_prepare($koneksi, $get_query);
    mysqli_stmt_bind_param($get_stmt, "ii", $id, $user_id);
    mysqli_stmt_execute($get_stmt);
    $result = mysqli_stmt_get_result($get_stmt);
    $item = mysqli_fetch_assoc($result);
    mysqli_stmt_close($get_stmt);
    
    if ($item) {
        $new_jumlah = $item['jumlah'] + 1;
        
        // Calculate new total with discount
        $harga_final = ($item['persen_diskon'] > 0) 
            ? $item['harga'] * (1 - ($item['persen_diskon'] / 100)) 
            : $item['harga'];
        $new_total = (int)round($harga_final * $new_jumlah);
        
        $update_query = "UPDATE keranjang SET jumlah = ?, total = ? WHERE id = ? AND user_id = ?";
        $update_stmt = mysqli_prepare($koneksi, $update_query);
        mysqli_stmt_bind_param($update_stmt, "iiii", $new_jumlah, $new_total, $id, $user_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
    }
    
    header("Location: keranjang.php");
    exit;
}

// Kurang jumlah
if (isset($_GET['kurang'])) {
    $id = (int)$_GET['kurang'];
    
    // Get current item data
    $get_query = "SELECT keranjang.*, produk.harga, 
                         COALESCE(d.persen_diskon, 0) as persen_diskon
                  FROM keranjang 
                  JOIN produk ON keranjang.produk_id = produk.produk_id
                  LEFT JOIN diskon d ON produk.produk_id = d.produk_id 
                       AND d.status = 'active' 
                       AND NOW() BETWEEN d.start_date AND d.end_date
                  WHERE keranjang.id = ? AND keranjang.user_id = ?";
    $get_stmt = mysqli_prepare($koneksi, $get_query);
    mysqli_stmt_bind_param($get_stmt, "ii", $id, $user_id);
    mysqli_stmt_execute($get_stmt);
    $result = mysqli_stmt_get_result($get_stmt);
    $item = mysqli_fetch_assoc($result);
    mysqli_stmt_close($get_stmt);
    
    if ($item && $item['jumlah'] > 1) {
        $new_jumlah = $item['jumlah'] - 1;
        
        // Calculate new total with discount
        $harga_final = ($item['persen_diskon'] > 0) 
            ? $item['harga'] * (1 - ($item['persen_diskon'] / 100)) 
            : $item['harga'];
        $new_total = (int)round($harga_final * $new_jumlah);
        
        $update_query = "UPDATE keranjang SET jumlah = ?, total = ? WHERE id = ? AND user_id = ?";
        $update_stmt = mysqli_prepare($koneksi, $update_query);
        mysqli_stmt_bind_param($update_stmt, "iiii", $new_jumlah, $new_total, $id, $user_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
    }
    
    header("Location: keranjang.php");
    exit;
}

// Ambil data keranjang user (hanya yang status-nya 'aktif') with discount calculation
$query = mysqli_query($koneksi, "
SELECT keranjang.*, produk.name, produk.harga, produk.image,
       COALESCE(d.persen_diskon, 0) as persen_diskon
FROM keranjang
JOIN produk ON keranjang.produk_id = produk.produk_id
LEFT JOIN diskon d ON produk.produk_id = d.produk_id 
     AND d.status = 'active' 
     AND NOW() BETWEEN d.start_date AND d.end_date
WHERE keranjang.user_id = '$user_id' AND keranjang.status = 'aktif'
ORDER BY keranjang.created_at DESC
");

$total = 0;
$ongkir = 10000;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../STYLESHEET/keranjang.css">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-shopping-cart me-2"></i>Keranjang Belanja</h2>
                    <a href="../FRONTEND/produk/produk.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Lanjut Belanja
                    </a>
                </div>

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

                <?php if (mysqli_num_rows($query) > 0): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Produk</th>
                                            <th>Ukuran</th>
                                            <th>Harga</th>
                                            <th>Jumlah</th>
                                            <th>Subtotal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($item = mysqli_fetch_assoc($query)): 
                                            // Calculate with discount
                                            $harga_asli = $item['harga'];
                                            $persen_diskon = $item['persen_diskon'];
                                            $harga_final = ($persen_diskon > 0) 
                                                ? $harga_asli * (1 - ($persen_diskon / 100)) 
                                                : $harga_asli;
                                            $subtotal = $harga_final * $item['jumlah'];
                                            $total += $subtotal;
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                         class="me-3 rounded" 
                                                         style="width: 80px; height: 80px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                        <?php if ($persen_diskon > 0): ?>
                                                            <small class="badge bg-success">Diskon <?php echo $persen_diskon; ?>%</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo $item['size'] ?: 'Default'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($persen_diskon > 0): ?>
                                                    <div class="text-decoration-line-through text-muted small">
                                                        Rp<?php echo number_format($harga_asli, 0, ',', '.'); ?>
                                                    </div>
                                                    <div class="fw-bold text-success">
                                                        Rp<?php echo number_format($harga_final, 0, ',', '.'); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="fw-bold">
                                                        Rp<?php echo number_format($harga_asli, 0, ',', '.'); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <a href="?kurang=<?php echo $item['id']; ?>" 
                                                       class="btn btn-outline-secondary btn-sm me-2"
                                                       <?php echo $item['jumlah'] <= 1 ? 'style="opacity:0.5; pointer-events:none;"' : ''; ?>>
                                                        <i class="fas fa-minus"></i>
                                                    </a>
                                                    <span class="fw-bold mx-2" style="min-width: 30px; text-align: center;">
                                                        <?php echo $item['jumlah']; ?>
                                                    </span>
                                                    <a href="?tambah=<?php echo $item['id']; ?>" 
                                                       class="btn btn-outline-secondary btn-sm ms-2">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-primary">
                                                    Rp<?php echo number_format($subtotal, 0, ',', '.'); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="?hapus=<?php echo $item['id']; ?>" 
                                                   class="btn btn-outline-danger btn-sm"
                                                   onclick="return confirm('Hapus produk ini dari keranjang?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Card -->
                    <div class="row mt-4">
                        <div class="col-lg-8"></div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Ringkasan Belanja</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span class="fw-bold">Rp<?php echo number_format($total, 0, ',', '.'); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Ongkos Kirim:</span>
                                        <span>Rp<?php echo number_format($ongkir, 0, ',', '.'); ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="h5">Total:</span>
                                        <span class="h5 text-primary fw-bold">
                                            Rp<?php echo number_format($total + $ongkir, 0, ',', '.'); ?>
                                        </span>
                                    </div>
                                    <div class="d-grid">
                                        <a href="checkout.php" class="btn btn-primary btn-lg">
                                            <i class="fas fa-credit-card me-2"></i>Checkout
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Empty Cart -->
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-shopping-cart fa-5x text-muted"></i>
                        </div>
                        <h4 class="text-muted mb-3">Keranjang Belanja Kosong</h4>
                        <p class="text-muted mb-4">Yuk, isi keranjang belanja Anda dengan produk-produk menarik dari toko kami!</p>
                        <a href="produk.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('show')) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 300);
                }
            });
        }, 5000);

        // Loading state for quantity buttons
        document.querySelectorAll('a[href*="tambah"], a[href*="kurang"]').forEach(btn => {
            btn.addEventListener('click', function() {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.style.pointerEvents = 'none';
            });
        });

        // Confirmation for delete with better UX
        document.querySelectorAll('a[href*="hapus"]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>