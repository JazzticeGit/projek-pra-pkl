<?php
// Koneksi database
include '../../koneksi.php';

// Ambil ID pembayaran dari parameter URL
$pembayaran_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($pembayaran_id === 0) {
    die("ID pembayaran tidak valid");
}

// Query untuk mengambil detail pembayaran dan user
$query_pembayaran = "
    SELECT 
        p.*,
        u.username,
        u.email,
        mp.nama as metode_nama,
        mp.norek,
        pm.user_id,
        pm.alamat,
        pm.alamat_lengkap
    FROM pembayaran p
    LEFT JOIN pemesanan pm ON p.id = pm.id
    LEFT JOIN users u ON pm.user_id = u.id
    LEFT JOIN metode_pembayaran mp ON p.id_metode_pembayaran = mp.id
    WHERE p.id = $pembayaran_id
";

$result_pembayaran = mysqli_query($koneksi, $query_pembayaran);
$pembayaran = mysqli_fetch_assoc($result_pembayaran);

if (!$pembayaran) {
    die("Data pembayaran tidak ditemukan");
}

// Query untuk mengambil detail pesanan (barang yang dibeli)
$query_detail = "
    SELECT 
        dp.*,
        pr.name as nama_produk,
        pr.harga,
        pr.image,
        k.jenis_produk as nama_kategori
    FROM detail_pesanan dp
    LEFT JOIN produk pr ON dp.produk_id = pr.produk_id
    LEFT JOIN kategori k ON pr.id_kategori = k.id
    WHERE dp.pemesanan_id = " . $pembayaran['id'] . "
";

$result_detail = mysqli_query($koneksi, $query_detail);
$detail_pesanan = [];
while ($row = mysqli_fetch_assoc($result_detail)) {
    $detail_pesanan[] = $row;
}

// Fungsi untuk menentukan status badge
function getStatusBadge($status) {
    switch ($status) {
        case 'berhasil':
            return '<span class="badge badge-success">Berhasil</span>';
        case 'gagal':
            return '<span class="badge badge-danger">Gagal</span>';
        case 'pending':
        default:
            return '<span class="badge badge-warning">Pending</span>';
    }
}

// Fungsi untuk format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Verifikasi Pembayaran</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .status-card {
            border-left: 4px solid;
        }
        .status-berhasil {
            border-left-color: #28a745;
        }
        .status-gagal {
            border-left-color: #dc3545;
        }
        .status-pending {
            border-left-color: #ffc107;
        }
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2><i class="fas fa-file-invoice-dollar me-2"></i>Detail Verifikasi Pembayaran</h2>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Pembayaran Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card status-card status-<?= $pembayaran['status'] ?>">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="card-title mb-2">
                                    <i class="fas fa-user-circle me-2"></i><?= htmlspecialchars($pembayaran['username']) ?>
                                </h4>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($pembayaran['email']) ?>
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-calendar me-2"></i><?= date('d F Y H:i', strtotime($pembayaran['tgl_pembayaran'])) ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h3 class="mb-2"><?= getStatusBadge($pembayaran['status']) ?></h3>
                                <h4 class="text-primary mb-0"><?= formatRupiah($pembayaran['total_bayar']) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Informasi Pembayaran -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Informasi Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-5 info-label">ID Pembayaran:</div>
                            <div class="col-7">#<?= $pembayaran['id'] ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Metode Bayar:</div>
                            <div class="col-7"><?= htmlspecialchars($pembayaran['metode_nama']) ?></div>
                        </div>
                        <?php if ($pembayaran['norek']): ?>
                        <div class="row mb-3">
                            <div class="col-5 info-label">No. Rekening:</div>
                            <div class="col-7"><?= htmlspecialchars($pembayaran['norek']) ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Total Bayar:</div>
                            <div class="col-7"><strong><?= formatRupiah($pembayaran['total_bayar']) ?></strong></div>
                        </div>
                        <?php if ($pembayaran['bukti_transfer']): ?>
                        <div class="row mb-3">
                            <!-- <div class="col-5 info-label">Bukti Transfer:</div>
                            <div class="col-7">
                                <a href="../bukti_transfer/<?= $pembayaran['bukti_transfer'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Lihat Bukti
                                </a>
                            </div> -->
                        </div>
                        <?php endif; ?>
                        <?php if ($pembayaran['catatan']): ?>
                        <div class="row mb-3">
                            <div class="col-5 info-label">Catatan:</div>
                            <div class="col-7"><?= htmlspecialchars($pembayaran['catatan']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Informasi Status -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Status Transaksi</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <?php if ($pembayaran['status'] == 'berhasil'): ?>
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                                <h4 class="text-success mt-3">Transaksi Berhasil</h4>
                                <p class="text-muted">Pembayaran telah diverifikasi dan pesanan sedang diproses</p>
                            <?php elseif ($pembayaran['status'] == 'gagal'): ?>
                                <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                                <h4 class="text-danger mt-3">Transaksi Gagal</h4>
                                <p class="text-muted">Pembayaran ditolak atau tidak valid</p>
                            <?php else: ?>
                                <i class="fas fa-clock text-warning" style="font-size: 4rem;"></i>
                                <h4 class="text-warning mt-3">Menunggu Verifikasi</h4>
                                <p class="text-muted">Pembayaran sedang dalam proses verifikasi</p>
                            <?php endif; ?>
                        </div>

                        <?php if ($pembayaran['status'] == 'pending'): ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i>
                            Pembayaran ini masih menunggu verifikasi admin
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Barang yang Dibeli -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Barang yang Dibeli</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($detail_pesanan)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Gambar</th>
                                            <th>Nama Produk</th>
                                            <th>Kategori</th>
                                            <th>Harga Satuan</th>
                                            <th>Jumlah</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total_keseluruhan = 0;
                                        foreach ($detail_pesanan as $item): 
                                            $total_item = $item['harga'] * $item['jumlah'];
                                            $total_keseluruhan += $total_item;
                                        ?>
                                        <tr>
                                            <td>
                                                <?php if ($item['image']): ?>
                                                    <img src="uploads/produk/<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>" class="product-img">
                                                <?php else: ?>
                                                    <div class="product-img bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($item['nama_produk']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($item['nama_kategori']) ?></span>
                                            </td>
                                            <td><?= formatRupiah($item['harga']) ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?= $item['jumlah'] ?></span>
                                            </td>
                                            <td><strong><?= formatRupiah($total_item) ?></strong></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="5" class="text-end">Total Keseluruhan:</th>
                                            <th><?= formatRupiah($total_keseluruhan) ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-cart text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 text-muted">Tidak ada detail barang ditemukan</h5>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>