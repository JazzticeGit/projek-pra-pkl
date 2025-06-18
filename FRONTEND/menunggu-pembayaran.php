<?php
session_start();
include '../koneksi.php';

// Debug info - tambahkan jika diperlukan
if (isset($_GET['debug'])) {
    echo "<h2>DEBUG INFO:</h2>";
    echo "<h3>POST Data:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    echo "<h3>SESSION Data:</h3>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    exit;
}

// Ambil ID pemesanan dari parameter URL atau session
$id_pemesanan = $_GET['id'] ?? $_SESSION['last_pemesanan_id'] ?? null;

if (!$id_pemesanan) {
    echo "<script>alert('ID Pemesanan tidak ditemukan!'); window.location.href='keranjang.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'] ?? 1;

// Ambil data pemesanan
$queryPemesanan = mysqli_query($koneksi, "SELECT p.*, mp.nama as metode_nama, mp.norek 
    FROM pemesanan p 
    LEFT JOIN pembayaran pb ON p.id = pb.keranjang_id 
    LEFT JOIN metode_pembayaran mp ON pb.id_metode_pembayaran = mp.id 
    WHERE p.id = $id_pemesanan AND p.user_id = $user_id");

$pemesanan = mysqli_fetch_assoc($queryPemesanan);

if (!$pemesanan) {
    echo "<script>alert('Pemesanan tidak ditemukan!'); window.location.href='keranjang.php';</script>";
    exit;
}

// Ambil detail pesanan
$queryDetail = mysqli_query($koneksi, "SELECT dp.*, pr.name, pr.image 
    FROM detail_pesanan dp 
    JOIN produk pr ON dp.produk_id = pr.produk_id 
    WHERE dp.pemesanan_id = $id_pemesanan");

$detail_pesanan = [];
while ($row = mysqli_fetch_assoc($queryDetail)) {
    $detail_pesanan[] = $row;
}

// Cek status pembayaran
$queryPembayaran = mysqli_query($koneksi, "SELECT * FROM pembayaran 
    WHERE keranjang_id IN (SELECT id FROM keranjang WHERE user_id = $user_id) 
    ORDER BY id DESC LIMIT 1");
$pembayaran = mysqli_fetch_assoc($queryPembayaran);

$bukti_uploaded = $pembayaran && !empty($pembayaran['bukti_transfer']);
$status_pembayaran = $pembayaran['status'] ?? 'pending';

$id_pesanan_view = 'ORD-' . date('Y') . '-' . str_pad($id_pemesanan, 3, '0', STR_PAD_LEFT);
$tanggal_tampil = date('d F Y', strtotime($pemesanan['tanggal_pemesanan']));
$subtotal = $pemesanan['total_harga'] - 10000;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Menunggu Pembayaran - Toko Baju</title>
    <link rel="stylesheet" href="../STYLESHEET/pay-waiting-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
   <?php if ($status_pembayaran === 'berhasil'): ?>
    <script>
        window.location.href = 'sukses-pembayaran.php?id=<?= $id_pemesanan ?>';
    </script>
<?php elseif ($status_pembayaran === 'gagal'): ?>
    <script>
        window.location.href = 'gagal.php?id=<?= $id_pemesanan ?>';
    </script>
<?php endif; ?>


    <?php if ($bukti_uploaded && $status_pembayaran === 'pending'): ?>
        <div style="background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #bee5eb;">
            <i class="fas fa-info-circle"></i> <strong>Bukti pembayaran sudah dikirim!</strong> 
            Menunggu verifikasi dari admin.
        </div>
    <?php else: ?>
        <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ffeaa7;">
            <i class="fas fa-exclamation-triangle"></i> <strong>Menunggu pembayaran</strong> 
            Silakan upload bukti transfer Anda.
        </div>
    <?php endif; ?>
    
    <div class="header">
        <h1><i class="fas fa-clock"></i> Status Pembayaran</h1>
        <p>Pesanan Anda sedang menunggu pembayaran</p>
    </div>

    <div class="countdown" id="countdown">
        <i class="fas fa-hourglass-half"></i> Batas waktu pembayaran: <span id="timeLeft">23:59:45</span>
    </div>

    <div class="order-info">
        <div class="info-card">
            <h3><i class="fas fa-shopping-bag"></i> Detail Pesanan</h3>
            <div class="info-item">
                <span>ID Pesanan:</span>
                <span id="orderId"><?= htmlspecialchars($id_pesanan_view) ?></span>
            </div>
            <div class="info-item">
                <span>Tanggal:</span>
                <span id="orderDate"><?= htmlspecialchars($tanggal_tampil) ?></span>
            </div>
            <div class="info-item">
                <span>Status:</span>
                <span id="orderStatus">
                    <?php if ($status_pembayaran === 'berhasil'): ?>
                        <span style="color: green;">Pembayaran Berhasil</span>
                    <?php elseif ($bukti_uploaded): ?>
                        <span style="color: orange;">Menunggu Verifikasi</span>
                    <?php else: ?>
                        <span style="color: red;">Menunggu Pembayaran</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-item">
                <span>Total Item:</span>
                <span><?= count($detail_pesanan) ?> produk</span>
            </div>
        </div>

        <div class="info-card">
            <h3><i class="fas fa-calculator"></i> Rincian Biaya</h3>
            <div class="info-item">
                <span>Subtotal:</span>
                <span id="subtotal">Rp <?= number_format($subtotal) ?></span>
            </div>
            <div class="info-item">
                <span>Ongkir:</span>
                <span id="shipping">Rp 10.000</span>
            </div>
            <div class="info-item">
                <span>Total Bayar:</span>
                <span id="totalAmount" style="font-weight: bold; color: #007bff;">Rp <?= number_format($pemesanan['total_harga']) ?></span>
            </div>
        </div>
    </div>

    <div class="payment-section">
        <div class="bank-selection">
            <h3><i class="fas fa-university"></i> Rekening Tujuan</h3>
            <div class="bank-cards">
                <div class="bank-card selected">
                    <div class="bank-logo">🏦</div>
                    <h4><?= htmlspecialchars($pemesanan['metode_nama'] ?? 'Bank Transfer') ?></h4>
                    <p><strong><?= htmlspecialchars($pemesanan['norek'] ?? '-') ?></strong></p>
                    <p>a.n. AgesaShop_Id</p>
                </div>
            </div>
        </div>

        <?php if (!$bukti_uploaded): ?>
        <div class="upload-section">
            <h3><i class="fas fa-upload"></i> Upload Bukti Transfer</h3>
            <form action="proses-upload-bukti.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_pemesanan" value="<?= $id_pemesanan ?>">

                <p style="color: #555; margin-bottom: 10px;">Format file: JPG, PNG, PDF (max 5MB)</p>
                <input type="file" name="bukti_transfer" accept=".jpg,.jpeg,.png,.pdf" required><br><br>
                <textarea name="catatan" placeholder="Catatan tambahan (opsional)" style="width:100%;height:80px;padding:10px;"></textarea><br><br>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Kirim Bukti Transfer
                </button>
            </form>
        </div>
        <?php else: ?>
        <div class="upload-section">
            <h3><i class="fas fa-check-circle" style="color: green;"></i> Bukti Transfer Sudah Dikirim</h3>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                <p style="color: #666; margin-bottom: 15px;">Bukti pembayaran Anda telah diterima dan sedang diverifikasi oleh admin.</p>
                <button type="button" onclick="location.reload()" class="submit-btn" style="background: #28a745;">
                    <i class="fas fa-sync-alt"></i> Refresh Status
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="keranjang.php" style="color: #666; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
        </a>
        <span style="margin: 0 20px;">|</span>
        <a href="riwayat-pesanan.php" style="color: #666; text-decoration: none;">
            <i class="fas fa-history"></i> Riwayat Pesanan
        </a>
        <br><br>
        <small><a href="?debug=1&id=<?= $id_pemesanan ?>" style="color: #666;">Debug Info</a></small>
    </div>
</div>

<script>
    let timeLeft = 86400; // 24 jam
    const countdownElement = document.getElementById('timeLeft');

    setInterval(() => {
        if (timeLeft <= 0) {
            countdownElement.innerText = "Waktu habis";
            return;
        }

        const hours = String(Math.floor(timeLeft / 3600)).padStart(2, '0');
        const minutes = String(Math.floor((timeLeft % 3600) / 60)).padStart(2, '0');
        const seconds = String(timeLeft % 60).padStart(2, '0');

        countdownElement.innerText = `${hours}:${minutes}:${seconds}`;
        timeLeft--;
    }, 1000);

    // Auto refresh untuk cek status pembayaran setiap 30 detik
    <?php if ($bukti_uploaded && $status_pembayaran === 'pending'): ?>
    setInterval(() => {
        location.reload();
    }, 30000);
    <?php endif; ?>
</script>
</body>
</html>