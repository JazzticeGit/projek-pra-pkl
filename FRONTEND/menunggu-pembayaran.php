<?php
include '../koneksi.php';

$pembayaran_id = $_GET['pembayaran_id'];

// Get payment data with method info and total from pemesanan table
$query = "SELECT 
    p.id, p.status, p.tgl_pembayaran, 
    m.nama, m.norek, 
    pe.total_harga
FROM pembayaran p
JOIN metode_pembayaran m ON p.id_metode_pembayaran = m.id
JOIN pemesanan pe ON pe.pembayaran_id = p.id
WHERE p.id = $pembayaran_id;
";


$data = mysqli_fetch_assoc(mysqli_query($koneksi, $query));

if ($data['status'] == 'berhasil') {
    header("Location: sukses.php");
    exit;
}

// Format tanggal pemesanan
$tanggal_pemesanan = date('d M Y, H:i', strtotime($data['tgl_pembayaran']));
$order_id = str_pad($data['id'], 6, '0', STR_PAD_LEFT);

// Function to get e-wallet icon
function getEwalletIcon($nama) {
    $nama_lower = strtolower($nama);
    switch($nama_lower) {
        case 'dana':
            return '../image/dana-icon.png';
        case 'gopay':
            return '../image/gopay-icon.png';
        case 'ovo':
            return '../image/ovo-icon.png';
        default:
            return '../image/wallet-icon.png';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menunggu Pembayaran - Santai Aja!</title>
    <link rel="stylesheet" href="../STYLESHEET/pay-waiting-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="payment-card">
            <!-- Header Section -->
            <div class="header">
                <div class="icon-wrapper">
                    <i class="fas fa-clock"></i>
                </div>
                <h1>Tunggu Sebentar Ya! 😊</h1>
                <p class="subtitle">Pembayaranmu lagi diproses nih</p>
            </div>

            <!-- Payment Info -->
            <div class="payment-info">
                <div class="method-section">
                    <div class="method-header">
                        <img src="<?= getEwalletIcon($data['nama']) ?>" alt="<?= $data['nama'] ?>" class="ewallet-icon">
                        <div class="method-details">
                            <h3><?= $data['nama'] ?></h3>
                            <span class="method-label">Metode Pembayaran</span>
                        </div>
                    </div>
                </div>

                <div class="transfer-details">
                    <div class="detail-item">
                        <i class="fas fa-receipt"></i>
                        <div>
                            <span class="label">ID Pesanan</span>
                            <span class="value">#<?= $order_id ?></span>
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('<?= $order_id ?>')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>

                    <div class="detail-item">
                        <i class="fas fa-mobile-alt"></i>
                        <div>
                            <span class="label">Nomor Tujuan</span>
                            <span class="value"><?= $data['norek'] ?></span>
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('<?= $data['norek'] ?>')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>

                    <div class="detail-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <div>
                            <span class="label">Total Pembayaran</span>
                            <span class="value amount">Rp <?= number_format($data['total_harga']) ?></span>
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('<?= $data['total_harga'] ?>')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>

                    <div class="detail-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <span class="label">Waktu Pemesanan</span>
                            <span class="value"><?= $tanggal_pemesanan ?></span>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="status-section">
                    <div class="status-indicator">
                        <div class="pulse-dot"></div>
                        <span class="status-text">Status: <?= ucfirst($data['status']) ?></span>
                    </div>
                    <?php if ($data['status'] == 'pending'): ?>
                        <div class="status-note">
                            <i class="fas fa-hourglass-half"></i>
                            <span>Menunggu pembayaran dari kamu</span>
                        </div>
                    <?php elseif ($data['status'] == 'gagal'): ?>
                        <div class="status-note error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Pembayaran gagal, silakan coba lagi</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Instructions -->
            <div class="instructions">
                <div class="instruction-header">
                    <i class="fas fa-lightbulb"></i>
                    <h3>Cara Bayar Gampang Banget!</h3>
                </div>
                
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Buka Aplikasi <?= $data['nama'] ?></h4>
                            <p>Pastikan saldo kamu cukup ya!</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Transfer ke Nomor di Atas</h4>
                            <p>Copy paste aja biar gak salah</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Tunggu Konfirmasi</h4>
                            <p>Admin bakal konfirmasi dalam hitungan menit kok</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="note">
                    <i class="fas fa-info-circle"></i>
                    <p>Setelah transfer, halaman ini akan otomatis update. Gak perlu refresh manual! ✨</p>
                </div>
                
                <div class="actions">
                    <button class="btn-secondary" onclick="window.history.back()">
                        <i class="fas fa-arrow-left"></i>
                        Kembali
                    </button>
                    <button class="btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i>
                        Cek Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <i class="fas fa-check-circle"></i>
        <span>Berhasil disalin! 📋</span>
    </div>

    <script>
        // Auto refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);

        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast();
            });
        }

        // Show toast notification
        function showToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Add some fun interactions
        document.querySelector('.pulse-dot').addEventListener('click', function() {
            this.style.animation = 'none';
            setTimeout(() => {
                this.style.animation = 'pulse 2s infinite';
            }, 100);
        });
    </script>
</body>
</html>