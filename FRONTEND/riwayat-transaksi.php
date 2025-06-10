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
        case 'gagal':
            return '<span class="badge badge-danger">Gagal</span>';
        default:
            return '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }
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
        
    </style>
</head>
<body>
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
                    <?php while ($pesanan = mysqli_fetch_assoc($result)): ?>
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
                                        <?php elseif ($pesanan['status'] === 'berhasil'): ?>
                                            <button class="btn btn-outline-success btn-action"
                                                    onclick="reorder(<?= $pesanan['id'] ?>)">
                                                <i class="fas fa-redo me-1"></i>Beli Lagi
                                            </button>
                                            <!-- <button class="btn btn-outline-warning btn-action"
                                                    onclick="rateOrder(<?= $pesanan['id'] ?>)">
                                                <i class="fas fa-star me-1"></i>Rating
                                            </button> -->
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

        function rateOrder(pesananId) {
            const rating = prompt('Berikan rating (1-5):');
            if (rating && rating >= 1 && rating <= 5) {
                const review = prompt('Berikan ulasan (opsional):') || '';
                
                fetch(`rating-pesanan.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        pesanan_id: pesananId,
                        rating: rating,
                        review: review
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Terima kasih atas rating Anda!');
                    } else {
                        alert('Gagal menyimpan rating: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan');
                });
            }
        }
    </script>
</body>
</html>