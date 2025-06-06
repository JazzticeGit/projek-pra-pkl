<?php
session_start();
include '../koneksi.php';

$user_id = $_SESSION['user_id'] ?? 1;
$alamat = $_POST['alamat_lengkap'] ?? '-';
$total_bayar = $_POST['total_bayar'] ?? 0;
$id_metode = $_POST['id_metode_pembayaran'] ?? 0;
$keranjang_ids = $_POST['keranjang_ids'] ?? [];

if (empty($keranjang_ids) || !$id_metode || $total_bayar <= 0) {
    die("Data tidak lengkap.");
}

// Ambil data metode pembayaran
$queryMetode = mysqli_query($koneksi, "SELECT * FROM metode_pembayaran WHERE id = $id_metode");
$metode = mysqli_fetch_assoc($queryMetode);

// 1. Buat entri PEMESANAN
$tanggal = date('Y-m-d');
$now = date('Y-m-d H:i:s');

mysqli_query($koneksi, "INSERT INTO pemesanan (
    user_id, agensi_id, alamat, alamat_lengkap, kurir_id, pembayaran_id,
    total_harga, status, tanggal_pemesanan, created_at
) VALUES (
    $user_id, 1, '', '$alamat', 1, 1,
    $total_bayar, 'pending', '$tanggal', '$now'
)");

$id_pemesanan = mysqli_insert_id($koneksi);

// 2. Ambil dan salin data dari keranjang ke detail_pesanan
foreach ($keranjang_ids as $kid) {
    $q = mysqli_query($koneksi, "SELECT * FROM keranjang WHERE id = $kid AND user_id = $user_id");
    if ($row = mysqli_fetch_assoc($q)) {
        $produk_id = $row['produk_id'];
        $jumlah = $row['jumlah'];
        $total = $row['total'];

        mysqli_query($koneksi, "INSERT INTO detail_pesanan (
            pemesanan_id, produk_id, jumlah, total
        ) VALUES (
            $id_pemesanan, $produk_id, $jumlah, $total
        )");

        mysqli_query($koneksi, "UPDATE keranjang SET status = 'checkout' WHERE id = $kid");
    }
}

// 3. Simpan ke tabel pembayaran (gunakan id keranjang pertama saja, karena FK-nya masih keranjang_id)
$keranjang_id_ref = $keranjang_ids[0];
mysqli_query($koneksi, "INSERT INTO pembayaran (
    keranjang_id, id_metode_pembayaran, status, tgl_pembayaran
) VALUES (
    $keranjang_id_ref, $id_metode, 'pending', NULL
)");

// Data untuk tampilan
$id_pesanan_view = 'ORD-' . date('Y') . '-' . str_pad($id_pemesanan, 3, '0', STR_PAD_LEFT);
$tanggal_tampil = date('d F Y', strtotime($tanggal));
$subtotal = $total_bayar - 10000;
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
    <div class="header">
        <h1><i class="fas fa-clock"></i> Menunggu Pembayaran</h1>
        <p>Silakan lakukan pembayaran sesuai instruksi di bawah</p>
    </div>

    <div class="countdown" id="countdown">
        <i class="fas fa-hourglass-half"></i> Batas waktu pembayaran: <span id="timeLeft">23:59:45</span>
    </div>

    <div class="order-info">
        <div class="info-card">
            <h3><i class="fas fa-shopping-bag"></i> Detail Pesanan</h3>
            <div class="info-item">
                <span>ID Pesanan:</span>
                <span id="orderId"><?= $id_pesanan_view ?></span>
            </div>
            <div class="info-item">
                <span>Tanggal:</span>
                <span id="orderDate"><?= $tanggal_tampil ?></span>
            </div>
            <div class="info-item">
                <span>Status:</span>
                <span id="orderStatus">Menunggu Pembayaran</span>
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
                <span id="totalAmount">Rp <?= number_format($total_bayar) ?></span>
            </div>
        </div>
    </div>

    <div class="payment-section">
        <div class="bank-selection">
            <h3><i class="fas fa-university"></i> Rekening Tujuan</h3>
            <div class="bank-cards">
                <div class="bank-card selected">
                    <div class="bank-logo">🏦</div>
                    <h4><?= $metode['nama'] ?></h4>
                    <p><strong><?= $metode['norek'] ?></strong></p>
                    <p>a.n. AgesaShop_Id</p>
                </div>
            </div>
        </div>

        <div class="upload-section">
            <h3><i class="fas fa-upload"></i> Upload Bukti Transfer</h3>
            <form action="upload-bukti.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_pemesanan" value="<?= $id_pemesanan ?>">
                <input type="hidden" name="id_metode_pembayaran" value="<?= $id_metode ?>">
                <input type="hidden" name="total" value="<?= $total_bayar ?>">

                <p style="color: #555; margin-bottom: 10px;">Format file: JPG, PNG, PDF (max 5MB)</p>
                <input type="file" name="bukti_transfer" accept=".jpg,.jpeg,.png,.pdf" required><br><br>
                <textarea name="catatan" placeholder="Catatan tambahan (opsional)" style="width:100%;height:80px;padding:10px;"></textarea><br><br>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Kirim Bukti Transfer
                </button>
            </form>
        </div>
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
</script>
</body>
</html>
