<?php
session_start();
include '../koneksi.php';

$user_id = $_SESSION['user_id'] ?? 1;

// Filter status dari parameter GET
$filter_status = $_GET['status'] ?? 'all';

// Query untuk mengambil riwayat pesanan
$where_clause = "WHERE p.user_id = $user_id";
if ($filter_status !== 'all') {
    $filter_status = mysqli_real_escape_string($koneksi, $filter_status);
    $where_clause .= " AND p.status = '$filter_status'";
}

$queryPemesanan = "
    SELECT 
        p.*,
        u.username,
        mp.nama as metode_pembayaran,
        mp.norek,
        pay.status as status_pembayaran,
        pay.tgl_pembayaran,
        pay.total_bayar as bayar_total,
        COUNT(dp.id) as jumlah_produk,
        GROUP_CONCAT(CONCAT(pr.name, ' (', dp.jumlah, 'x)') SEPARATOR ', ') as list_produk
    FROM pemesanan p
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN pembayaran pay ON p.pembayaran_id = pay.id
    LEFT JOIN metode_pembayaran mp ON pay.id_metode_pembayaran = mp.id
    LEFT JOIN detail_pesanan dp ON p.id = dp.pemesanan_id
    LEFT JOIN produk pr ON dp.produk_id = pr.produk_id
    $where_clause
    GROUP BY p.id
    ORDER BY p.created_at DESC
";

$result = mysqli_query($koneksi, $queryPemesanan);

// Hitung statistik untuk filter
$queryStats = " 
    SELECT 
        status,
        COUNT(*) as count
    FROM pemesanan 
    WHERE user_id = $user_id 
    GROUP BY status
";
$statsResult = mysqli_query($koneksi, $queryStats);
$stats = [];
$total_count = 0;
while ($row = mysqli_fetch_assoc($statsResult)) {
    $stats[$row['status']] = $row['count'];
    $total_count += $row['count'];
}

function getStatusBadge($status) {
    switch ($status) {
        case 'berhasil':
            return '<span class="badge badge-success">Berhasil</span>';
        case 'pending':
            return '<span class="badge badge-warning">Menunggu</span>';
        case 'diproses':
            return '<span class="badge badge-info">Diproses</span>';
        case 'dikirim':
            return '<span class="badge badge-primary">Dikirim</span>';
        case 'selesai':
            return '<span class="badge badge-success">Selesai</span>';
        case 'gagal':
            return '<span class="badge badge-danger">Gagal</span>';
        default:
            return '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }
}

    function getTrackingProgress($status) {
    $allSteps = [
        'pending' => 1,
        'diproses' => 2, 
        'dikirim' => 3,
        'selesai' => 4,
        'berhasil' => 4 // berhasil sama dengan selesai
    ];
    
    $currentStep = $allSteps[$status] ?? 0;
    
    return [
        'current_step' => $currentStep,
        'total_steps' => 4,
        'percentage' => ($currentStep / 4) * 100
    ];
}


function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function formatTanggal($tanggal) {
    return date('d M Y, H:i', strtotime($tanggal));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../STYLESHEET/riwayat-transaksi.css">
    <style>
        /* Tracking Progress Styles */
        .tracking-container {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #007bff;
        }
        
        .tracking-progress {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }
        
        .tracking-step {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            z-index: 2;
        }
        
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .step-circle.completed {
            background: #28a745;
            color: white;
            box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.2);
        }
        
        .step-circle.active {
            background: #007bff;
            color: white;
            box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.2);
            animation: pulse 2s infinite;
        }
        
        .step-circle.pending {
            background: #e9ecef;
            color: #6c757d;
            border: 2px solid #dee2e6;
        }
        
        .step-text {
            text-align: center;
            font-size: 12px;
            font-weight: 500;
            max-width: 80px;
            line-height: 1.2;
        }
        
        .step-text.completed {
            color: #28a745;
        }
        
        .step-text.active {
            color: #007bff;
            font-weight: 600;
        }
        
        .step-text.pending {
            color: #6c757d;
        }
        
        .tracking-line {
            position: absolute;
            top: 20px;
            left: 40px;
            right: 40px;
            height: 2px;
            background: #dee2e6;
            z-index: 1;
        }
        
        .tracking-line-progress {
            height: 100%;
            background: linear-gradient(to right, #28a745, #007bff);
            transition: width 0.8s ease;
            border-radius: 1px;
        }
        
        .failed-message {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        
        .failed-message i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .failed-message h4 {
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .failed-message p {
            margin-bottom: 0;
            opacity: 0.9;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
            }
        }
        
        .transaction-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .transaction-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            padding: 12px 20px;
            background: #f8f9fa;
            color: #6c757d;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .filter-tab:hover {
            background: #e9ecef;
            color: #495057;
            text-decoration: none;
        }
        
        .filter-tab.active {
            background: #007bff;
            color: white;
            border-color: #0056b3;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .btn-action {
            margin: 2px;
            border-radius: 20px;
            font-size: 0.875rem;
            padding: 8px 16px;
        }
        
        @media (max-width: 768px) {
            .tracking-step {
                flex: none;
                margin: 0 5px;
            }
            
            .step-circle {
                width: 35px;
                height: 35px;
                font-size: 12px;
            }
            
            .step-text {
                font-size: 10px;
                max-width: 60px;
            }
            
            .tracking-line {
                left: 35px;
                right: 35px;
                top: 17px;
            }
        }
    </style>
</head>
<body>
    <!-- NAVIGASI BAR -->
    <nav>
        <div class="navbg" id="nav">
            <!-- GAMBAR NAVIGASI -->
             <a href="index.php"><img src="../image/AGESA.png" alt="" srcset=""></a>

             <!-- LINK NAVIGASI -->
            <div class="navlink">
                <ul>
                    <li><a href="../FRONTEND/produk/produk.php">Shop</a></li>  
                    <li><a href="../FRONTEND/produk/colection.php">Collection</a></li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="../FRONTEND/about.html#footer">Contact</a></li>
                    <li><a href="riwayat-transaksi.php">Transaksi</a></li>
                </ul>
            </div>

            <!-- SEARCH BAR -->
            <div class="searchBar">
                <form action="search.php" method="GET">
                <input type="text" name="query" placeholder="   Search  " required>
                <i class="fas fa-search"></i>
                </form>
            </div>

            <!-- ICON LINK -->
             <div class="iconLink">
             <ul>
                <li><a href="keranjang.php" class="fa-solid fa-cart-shopping"></a></li>
                <li>
                <a href="#" class="fa-solid fa-user" id="profileTrigger"></a>
                </li>
             </ul>
             </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-2">
                    <i class="fas fa-history text-primary me-2"></i>
                    Riwayat Transaksi
                </h2>
                <p class="text-muted">Kelola dan pantau semua pesanan Anda</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="?status=all" class="filter-tab <?= $filter_status === 'all' ? 'active' : '' ?>">
                Semua (<?= $total_count ?>)
            </a>
            <a href="?status=pending" class="filter-tab <?= $filter_status === 'pending' ? 'active' : '' ?>">
                Menunggu (<?= $stats['pending'] ?? 0 ?>)
            </a>
            <a href="?status=diproses" class="filter-tab <?= $filter_status === 'diproses' ? 'active' : '' ?>">
                Diproses (<?= $stats['diproses'] ?? 0 ?>)
            </a>
            <a href="?status=dikirim" class="filter-tab <?= $filter_status === 'dikirim' ? 'active' : '' ?>">
                Dikirim (<?= $stats['dikirim'] ?? 0 ?>)
            </a>
            <a href="?status=selesai" class="filter-tab <?= $filter_status === 'selesai' ? 'active' : '' ?>">
                Selesai (<?= $stats['selesai'] ?? 0 ?>)
            </a>
            <a href="?status=berhasil" class="filter-tab <?= $filter_status === 'berhasil' ? 'active' : '' ?>">
                Berhasil (<?= $stats['berhasil'] ?? 0 ?>)
            </a>
            <a href="?status=gagal" class="filter-tab <?= $filter_status === 'gagal' ? 'active' : '' ?>">
                Gagal (<?= $stats['gagal'] ?? 0 ?>)
            </a>
        </div>

        <!-- Daftar Transaksi -->
        <div class="row">
            <div class="col-12">
                <?php if (mysqli_num_rows($result) === 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <h4>Tidak ada transaksi</h4>
                        <p>Belum ada transaksi yang sesuai dengan filter yang dipilih.</p>
                        <a href="../index.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
                        </a>
                    </div>
                <?php else: ?>
                  <div class="transaction-card" data-pesanan-id="<?= $pesanan['id'] ?>">  <?php while ($pesanan = mysqli_fetch_assoc($result)): ?> </div>
                        <div class="transaction-card">
                            <div class="card-body p-4">
                                <!-- Header Transaksi -->
                                <div class="row align-items-center mb-3">
                                    <div class="col-md-8">
                                        <h5 class="mb-1">
                                            <i class="fas fa-receipt text-primary me-2"></i>
                                            Pesanan #<?= $pesanan['id'] ?>
                                        </h5>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= formatTanggal($pesanan['created_at']) ?>
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <?= getStatusBadge($pesanan['status']) ?>
                                    </div>
                                </div>

                                <!-- Tracking Progress atau Pesan Gagal -->
                                <?php if ($pesanan['status'] === 'gagal'): ?>
    <div class="failed-message">
        <i class="fas fa-exclamation-triangle"></i>
        <h4>Pembayaran Gagal</h4>
        <p>Maaf, pembayaran Anda tidak dapat diproses. Silakan coba lagi atau hubungi customer service.</p>
    </div>
<?php else: ?>
    <?php 
    $trackingData = getTrackingProgress($pesanan['status']);
    $currentStep = $trackingData['current_step'];
    $progressPercentage = $trackingData['percentage'];
    ?>
    <div class="tracking-container">
        <h6 class="mb-3">
            <i class="fas fa-truck me-2"></i>Status Pengiriman
        </h6>
        <div class="tracking-progress">
            <div class="tracking-line">
                <div class="tracking-line-progress" style="width: <?= $progressPercentage ?>%;"></div>
            </div>
            
            <?php 
            $stepLabels = ['Pesanan Dibuat', 'Pesanan Diproses', 'Pesanan Dikirim', 'Pesanan Selesai'];
            $stepStatuses = ['pending', 'diproses', 'dikirim', 'selesai'];
            
            foreach ($stepLabels as $index => $label): 
                $stepNumber = $index + 1;
                $isCompleted = $stepNumber < $currentStep;
                $isActive = $stepNumber == $currentStep;
                $isPending = $stepNumber > $currentStep;
                
                if ($isCompleted) {
                    $stepClass = 'completed';
                } elseif ($isActive) {
                    $stepClass = 'active';
                } else {
                    $stepClass = 'pending';
                }
            ?>
            <div class="tracking-step">
                <div class="step-circle <?= $stepClass ?>">
                    <?php if ($isCompleted): ?>
                        <i class="fas fa-check"></i>
                    <?php elseif ($isActive): ?>
                        <i class="fas fa-clock"></i>
                    <?php else: ?>
                        <?= $stepNumber ?>
                    <?php endif; ?>
                </div>
                <div class="step-text <?= $stepClass ?>">
                    <?= $label ?>
                </div>
            </div>
            <?php endforeach; ?> 
        </div>
        
        <!-- Status Text -->
        <div class="mt-3 text-center">
            <small class="text-muted">
                Status saat ini: <strong><?= ucfirst($pesanan['status']) ?></strong>
                <?php if ($pesanan['updated_at']): ?>
                    <br>Diperbarui: <?= formatTanggal($pesanan['updated_at']) ?>
                <?php endif; ?>
            </small>
        </div>
    </div>
<?php endif; ?>

                                <!-- Info Produk -->
                                <div class="product-preview">
                                    <strong><?= $pesanan['jumlah_produk'] ?> produk:</strong>
                                    <?= htmlspecialchars($pesanan['list_produk'] ?? 'Tidak ada detail produk') ?>
                                </div>

                                <!-- Info Pembayaran dan Pengiriman -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Metode Pembayaran:</small>
                                        <strong>
                                            <?= htmlspecialchars($pesanan['metode_pembayaran'] ?? 'Tidak ada') ?>
                                            <?php if ($pesanan['norek']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($pesanan['norek']) ?></small>
                                            <?php endif; ?>
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Alamat Pengiriman:</small>
                                        <strong><?= htmlspecialchars(substr($pesanan['alamat_lengkap'], 0, 50)) ?>...</strong>
                                    </div>
                                </div>

                                <!-- Total dan Action -->
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h4 class="text-primary mb-0">
                                            <?= formatRupiah($pesanan['total_harga']) ?>
                                        </h4>
                                        <?php if ($pesanan['tgl_pembayaran']): ?>
                                            <small class="text-success">
                                                <i class="fas fa-check me-1"></i>
                                                Dibayar: <?= formatTanggal($pesanan['tgl_pembayaran']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <!-- Detail Button -->
                                        <button class="btn btn-outline-primary btn-action" 
                                                onclick="showDetail(<?= $pesanan['id'] ?>)">
                                            <i class="fas fa-eye me-1"></i>Detail
                                        </button>

                                        <!-- Action berdasarkan status -->
                                        <?php if ($pesanan['status'] === 'pending'): ?>
                                            <button class="btn btn-outline-warning btn-action"
                                                    onclick="checkPayment(<?= $pesanan['id'] ?>)">
                                                <i class="fas fa-credit-card me-1"></i>Bayar
                                            </button>
                                            <button class="btn btn-outline-danger btn-action"
                                                    onclick="cancelOrder(<?= $pesanan['id'] ?>)">
                                                <i class="fas fa-times me-1"></i>Batalkan
                                            </button>
                                        <?php elseif ($pesanan['status'] === 'gagal'): ?>
                                            <button class="btn btn-outline-warning btn-action"
                                                    onclick="retryPayment(<?= $pesanan['id'] ?>)">
                                                <i class="fas fa-redo me-1"></i>Coba Lagi
                                            </button>
                                        <?php elseif (in_array($pesanan['status'], ['berhasil', 'selesai'])): ?>
                                            <button class="btn btn-outline-success btn-action"
                                                    onclick="reorder(<?= $pesanan['id'] ?>)">
                                                <i class="fas fa-redo me-1"></i>Beli Lagi
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Detail Pesanan -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        function showDetail(pesananId) {
            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            const content = document.getElementById('detailContent');
            
            // Show loading
            content.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            modal.show();
            
            // Load detail via AJAX
            fetch(`detail-pesanan.php?id=${pesananId}`)
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Gagal memuat detail pesanan.
                        </div>
                    `;
                });
        }

        function checkPayment(pesananId) {
            if (confirm('Lanjutkan ke halaman pembayaran?')) {
                window.location.href = `menunggu-pembayaran.php?id=${pesananId}`;
            }
        }

        function retryPayment(pesananId) {
            if (confirm('Coba bayar pesanan ini lagi?')) {
                window.location.href = `menunggu-pembayaran.php?id=${pesananId}`;
            }
        }

        function cancelOrder(pesananId) {
            if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
                fetch(`batalkan-pesanan.php?id=${pesananId}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pesanan berhasil dibatalkan');
                        location.reload();
                    } else {
                        alert('Gagal membatalkan pesanan: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan saat membatalkan pesanan');
                });
            }
        }

        function reorder(pesananId) {
            if (confirm('Tambahkan produk dari pesanan ini ke keranjang?')) {
                fetch(`beli-lagi.php?id=${pesananId}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Produk berhasil ditambahkan ke keranjang');
                        if (confirm('Lihat keranjang sekarang?')) {
                            window.location.href = 'keranjang.php';
                        }
                    } else {
                        alert('Gagal menambahkan ke keranjang: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan');
                });
            }
        }

        // Auto-refresh untuk update status real-time (opsional)
        function checkStatusUpdates() {
    const activeOrders = document.querySelectorAll('[data-pesanan-id]');
    
    if (activeOrders.length > 0) {
        const orderIds = Array.from(activeOrders).map(el => el.dataset.pesananId);
        
        fetch('check-status-update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order_ids: orderIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.updates && data.updates.length > 0) {
                // Ada update status, refresh halaman dengan smooth transition
                showUpdateNotification(data.updates.length);
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }
        })
        .catch(error => {
            console.log('Status check error:', error);
        });
    }
}

function showUpdateNotification(count) {
    // Buat notifikasi update
    const notification = document.createElement('div');
    notification.className = 'alert alert-info position-fixed';
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    `;
    notification.innerHTML = `
        <i class="fas fa-sync-alt me-2"></i>
        ${count} pesanan memiliki update status baru!
        <button type="button" class="btn-close float-end" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove setelah 5 detik
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Jalankan pengecekan setiap 30 detik
setInterval(checkStatusUpdates, 30000);

// Jalankan sekali saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress lines on page load
    const progressLines = document.querySelectorAll('.tracking-line-progress');
    progressLines.forEach(line => {
        const width = line.style.width;
        line.style.width = '0%';
        setTimeout(() => {
            line.style.width = width;
        }, 500);
    });
});
    </script>
</body>
</html>