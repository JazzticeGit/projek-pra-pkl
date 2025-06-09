<?php
session_start();
include '../koneksi.php';

$user_id = $_SESSION['user_id'] ?? 1;
$pesanan_id = $_GET['id'] ?? 0;

// Validasi pesanan milik user
$queryValidasi = "SELECT id FROM pemesanan WHERE id = $pesanan_id AND user_id = $user_id";
$validasiResult = mysqli_query($koneksi, $queryValidasi);

if (mysqli_num_rows($validasiResult) === 0) {
    echo '<div class="alert alert-danger">Pesanan tidak ditemukan atau tidak memiliki akses.</div>';
    exit;
}

// Query untuk mengambil detail pesanan lengkap
$queryDetail = "
    SELECT 
        p.*,
        u.username,
        u.email,
        u.phone,
        mp.nama as metode_pembayaran,
        mp.norek,
        pay.status as status_pembayaran,
        pay.tgl_pembayaran,
        pay.total_bayar,
        pay.catatan as catatan_pembayaran,
        pay.bukti_transfer,
        k.nama_kurir,
        k.no_tlp as tlp_kurir,
        a.nama_agensi,
        a.ongkir
    FROM pemesanan p
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN pembayaran pay ON p.pembayaran_id = pay.id
    LEFT JOIN metode_pembayaran mp ON pay.id_metode_pembayaran = mp.id
    LEFT JOIN kurir k ON p.kurir_id = k.id
    LEFT JOIN agensi a ON k.kurir_id = a.id
    WHERE p.id = $pesanan_id
";

$detailResult = mysqli_query($koneksi, $queryDetail);
$pesanan = mysqli_fetch_assoc($detailResult);

// Query untuk mengambil detail produk
$queryProduk = "
    SELECT 
        dp.*,
        pr.name as nama_produk,
        pr.harga as harga_satuan,
        pr.image as gambar_produk,
        pr.deskripsi,
        k.jenis_produk
    FROM detail_pesanan dp
    LEFT JOIN produk pr ON dp.produk_id = pr.produk_id
    LEFT JOIN kategori k ON pr.id_kategori = k.id
    WHERE dp.pemesanan_id = $pesanan_id
";

$produkResult = mysqli_query($koneksi, $queryProduk);

// Query untuk mengambil riwayat diskon jika ada
$queryDiskon = "
    SELECT 
        d.*
    FROM diskon d
    WHERE d.produk_id IN (
        SELECT dp.produk_id 
        FROM detail_pesanan dp 
        WHERE dp.pemesanan_id = $pesanan_id
    )
    AND d.status = 'active'
    AND NOW() BETWEEN d.start_date AND d.end_date
";

$diskonResult = mysqli_query($koneksi, $queryDiskon);

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function formatTanggal($tanggal) {
    if (!$tanggal) return '-';
    return date('d M Y, H:i', strtotime($tanggal));
}

function getStatusBadge($status) {
    switch ($status) {
        case 'berhasil':
            return '<span class="badge badge-success"><i class="fas fa-check-circle me-1"></i>Berhasil</span>';
        case 'pending':
            return '<span class="badge badge-warning"><i class="fas fa-clock me-1"></i>Menunggu</span>';
        case 'gagal':
            return '<span class="badge badge-danger"><i class="fas fa-times-circle me-1"></i>Gagal</span>';
        case 'belum dibayar':
            return '<span class="badge badge-secondary"><i class="fas fa-credit-card me-1"></i>Belum Dibayar</span>';
        default:
            return '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }
}

function getStatusPembayaranBadge($status) {
    switch ($status) {
        case 'berhasil':
            return '<span class="badge badge-success"><i class="fas fa-check-circle me-1"></i>Lunas</span>';
        case 'pending':
            return '<span class="badge badge-warning"><i class="fas fa-hourglass-half me-1"></i>Menunggu Konfirmasi</span>';
        case 'gagal':
            return '<span class="badge badge-danger"><i class="fas fa-times-circle me-1"></i>Ditolak</span>';
        default:
            return '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../STYLESHEET/detail-transaksi-pesanan.css">
</head>
<body>
    



<div class="detail-pesanan">
    <!-- Header Detail -->
    <div class="detail-header">
        <div class="row">
            <div class="col-md-8">
                <h4 class="mb-2">
                    <i class="fas fa-receipt text-primary me-2"></i>
                    Detail Pesanan #<?= $pesanan['id'] ?>
                </h4>
                <p class="text-muted mb-1">
                    <i class="fas fa-calendar me-1"></i>
                    Dipesan: <?= formatTanggal($pesanan['created_at']) ?>
                </p>
                <p class="text-muted mb-0">
                    <i class="fas fa-user me-1"></i>
                    <?= htmlspecialchars($pesanan['username']) ?> (<?= htmlspecialchars($pesanan['email']) ?>)
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="mb-2">
                    <?= getStatusBadge($pesanan['status']) ?>
                </div>
                <?php if ($pesanan['status_pembayaran']): ?>
                    <div>
                        <?= getStatusPembayaranBadge($pesanan['status_pembayaran']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Informasi Pengiriman -->
    <div class="info-section">
        <h5 class="section-title">
            <i class="fas fa-shipping-fast text-primary me-2"></i>
            Informasi Pengiriman
        </h5>
        <div class="row">
            <div class="col-md-6">
                <div class="info-item">
                    <label>Alamat Pengiriman:</label>
                    <div class="info-value">
                        <strong><?= htmlspecialchars($pesanan['alamat']) ?></strong><br>
                        <small class="text-muted">
                            <?= htmlspecialchars($pesanan['alamat_lengkap']) ?>
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-item">
                    <label>Kurir:</label>
                    <div class="info-value">
                        <?php if ($pesanan['nama_kurir']): ?>
                            <strong><?= htmlspecialchars($pesanan['nama_kurir']) ?></strong><br>
                            <small class="text-muted">
                                <i class="fas fa-phone me-1"></i>
                                <?= htmlspecialchars($pesanan['tlp_kurir']) ?>
                            </small><br>
                            <small class="text-muted">
                                Ongkir: <?= formatRupiah($pesanan['ongkir']) ?>
                            </small>
                        <?php else: ?>
                            <span class="text-muted">Belum ditentukan</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($pesanan['tanggal_pemesanan']): ?>
        <div class="info-item">
            <label>Tanggal Pengiriman:</label>
            <div class="info-value">
                <i class="fas fa-calendar-alt text-primary me-1"></i>
                <?= formatTanggal($pesanan['tanggal_pemesanan']) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Detail Produk -->
    <div class="info-section">
        <h5 class="section-title">
            <i class="fas fa-box text-primary me-2"></i>
            Detail Produk
        </h5>
        <div class="product-list">
            <?php 
            $subtotal = 0;
            while ($produk = mysqli_fetch_assoc($produkResult)): 
                $total_item = $produk['jumlah'] * $produk['total'];
                $subtotal += $total_item;
            ?>
                <div class="product-item">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <?php if ($produk['gambar_produk']): ?>
                                <img src="../uploads/<?= htmlspecialchars($produk['gambar_produk']) ?>" 
                                     alt="<?= htmlspecialchars($produk['nama_produk']) ?>"
                                     class="product-image">
                            <?php else: ?>
                                <div class="product-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-1"><?= htmlspecialchars($produk['nama_produk']) ?></h6>
                            <small class="text-muted">
                                <?= htmlspecialchars($produk['jenis_produk']) ?>
                            </small>
                            <?php if ($produk['deskripsi']): ?>
                                <p class="product-desc">
                                    <?= htmlspecialchars(substr($produk['deskripsi'], 0, 100)) ?>...
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2 text-center">
                            <span class="quantity-badge"><?= $produk['jumlah'] ?>x</span>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="price-info">
                                <div class="unit-price">
                                    <?= formatRupiah($produk['harga_satuan']) ?>
                                </div>
                                <div class="total-price">
                                    <?= formatRupiah($total_item) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Informasi Pembayaran -->
    <div class="info-section">
        <h5 class="section-title">
            <i class="fas fa-credit-card text-primary me-2"></i>
            Informasi Pembayaran
        </h5>
        <div class="row">
            <div class="col-md-6">
                <div class="info-item">
                    <label>Metode Pembayaran:</label>
                    <div class="info-value">
                        <strong><?= htmlspecialchars($pesanan['metode_pembayaran'] ?? 'Belum dipilih') ?></strong>
                        <?php if ($pesanan['norek']): ?>
                            <br><small class="text-muted">
                                <i class="fas fa-university me-1"></i>
                                <?= htmlspecialchars($pesanan['norek']) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($pesanan['tgl_pembayaran']): ?>
                <div class="info-item">
                    <label>Tanggal Pembayaran:</label>
                    <div class="info-value">
                        <i class="fas fa-calendar-check text-success me-1"></i>
                        <?= formatTanggal($pesanan['tgl_pembayaran']) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($pesanan['catatan_pembayaran']): ?>
                <div class="info-item">
                    <label>Catatan:</label>
                    <div class="info-value">
                        <?= htmlspecialchars($pesanan['catatan_pembayaran']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <?php if ($pesanan['bukti_transfer']): ?>
                <div class="info-item">
                    <label>Bukti Transfer:</label>
                    <div class="info-value">
                        <img src="../uploads/bukti/<?= htmlspecialchars($pesanan['bukti_transfer']) ?>" 
                             alt="Bukti Transfer" 
                             class="bukti-transfer"
                             onclick="showImage(this.src)">
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Ringkasan Biaya -->
    <div class="info-section">
        <h5 class="section-title">
            <i class="fas fa-calculator text-primary me-2"></i>
            Ringkasan Biaya
        </h5>
        <div class="cost-summary">
            <div class="cost-item">
                <span>Subtotal Produk:</span>
                <span><?= formatRupiah($subtotal) ?></span>
            </div>
            <?php if ($pesanan['ongkir'] > 0): ?>
            <div class="cost-item">
                <span>Ongkos Kirim:</span>
                <span><?= formatRupiah($pesanan['ongkir']) ?></span>
            </div>
            <?php endif; ?>
            
            <?php 
            // Hitung diskon jika ada
            $total_diskon = 0;
            if (mysqli_num_rows($diskonResult) > 0): 
            ?>
            <div class="cost-item discount">
                <span>Diskon:</span>
                <span>-<?= formatRupiah($total_diskon) ?></span>
            </div>
            <?php endif; ?>
            
            <hr class="cost-divider">
            <div class="cost-item total">
                <span><strong>Total Pembayaran:</strong></span>
                <span><strong><?= formatRupiah($pesanan['total_harga']) ?></strong></span>
            </div>
            
            <?php if ($pesanan['total_bayar']): ?>
            <div class="cost-item paid">
                <span>Jumlah Dibayar:</span>
                <span class="text-success">
                    <i class="fas fa-check-circle me-1"></i>
                    <?= formatRupiah($pesanan['total_bayar']) ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Timeline Status (jika diperlukan) -->
    <?php if ($pesanan['status'] !== 'pending'): ?>
    <div class="info-section">
        <h5 class="section-title">
            <i class="fas fa-list-alt text-primary me-2"></i>
            Status Timeline
        </h5>
        <div class="timeline">
            <div class="timeline-item completed">
                <div class="timeline-marker">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="timeline-content">
                    <strong>Pesanan Dibuat</strong>
                    <small class="d-block text-muted"><?= formatTanggal($pesanan['created_at']) ?></small>
                </div>
            </div>
            
            <?php if ($pesanan['tgl_pembayaran']): ?>
            <div class="timeline-item completed">
                <div class="timeline-marker">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="timeline-content">
                    <strong>Pembayaran Diterima</strong>
                    <small class="d-block text-muted"><?= formatTanggal($pesanan['tgl_pembayaran']) ?></small>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($pesanan['status'] === 'berhasil'): ?>
            <div class="timeline-item completed">
                <div class="timeline-marker">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <div class="timeline-content">
                    <strong>Pesanan Dikirim</strong>
                    <small class="d-block text-muted"><?= formatTanggal($pesanan['updated_at']) ?></small>
                </div>
            </div>
            
            <div class="timeline-item completed">
                <div class="timeline-marker">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="timeline-content">
                    <strong>Pesanan Selesai</strong>
                    <small class="d-block text-muted"><?= formatTanggal($pesanan['updated_at']) ?></small>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal untuk melihat gambar bukti transfer -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bukti Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Bukti Transfer" class="img-fluid">
            </div>
        </div>
    </div>
</div>
</body>
</html>
<script>
function showImage(src) {
    document.getElementById('modalImage').src = src;
    const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
    imageModal.show();
}
</script>