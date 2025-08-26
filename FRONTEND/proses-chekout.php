<?php
session_start();
include '../koneksi.php';

// Debug info - tambahkan parameter ?debug=1 untuk melihat data
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

$user_id = $_SESSION['user_id'] ?? 1;
$alamat = mysqli_real_escape_string($koneksi, $_POST['alamat_lengkap'] ?? '');
$total_bayar = intval($_POST['total_bayar'] ?? 0);
$id_metode = intval($_POST['id_metode_pembayaran'] ?? 0);

$keranjang_ids = $_POST['keranjang_ids'] ?? [];

// Validasi dengan pesan error yang spesifik
if (empty($keranjang_ids)) {
    die("
    <div style='text-align: center; padding: 50px; font-family: Arial;'>
        <h2 style='color: red;'>❌ Error: Keranjang Kosong</h2>
        <p>Tidak ada produk yang dipilih untuk checkout.</p>
        <a href='keranjang.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Kembali ke Keranjang</a>
        <br><br>
        <small><a href='?debug=1' style='color: #666;'>Debug Info</a></small>
    </div>
    ");
}

if (!$id_metode) {
    die("
    <div style='text-align: center; padding: 50px; font-family: Arial;'>
        <h2 style='color: red;'>❌ Error: Metode Pembayaran</h2>
        <p>Silakan pilih metode pembayaran.</p>
        <a href='checkout.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Kembali ke Checkout</a>
        <br><br>
        <small><a href='?debug=1' style='color: #666;'>Debug Info</a></small>
    </div>
    ");
}

if ($total_bayar <= 0) {
    die("
    <div style='text-align: center; padding: 50px; font-family: Arial;'>
        <h2 style='color: red;'>❌ Error: Total Pembayaran</h2>
        <p>Total pembayaran tidak valid: Rp" . number_format($total_bayar) . "</p>
        <a href='checkout.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Kembali ke Checkout</a>
        <br><br>
        <small><a href='?debug=1' style='color: #666;'>Debug Info</a></small>
    </div>
    ");
}

if (empty($alamat)) {
    die("
    <div style='text-align: center; padding: 50px; font-family: Arial;'>
        <h2 style='color: red;'>❌ Error: Alamat Pengiriman</h2>
        <p>Alamat pengiriman tidak boleh kosong.</p>
        <a href='checkout.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Kembali ke Checkout</a>
        <br><br>
        <small><a href='?debug=1' style='color: #666;'>Debug Info</a></small>
    </div>
    ");
}

// Ambil data metode pembayaran untuk validasi
$queryMetode = mysqli_query($koneksi, "SELECT * FROM metode_pembayaran WHERE id = $id_metode");
$metode = mysqli_fetch_assoc($queryMetode);

if (!$metode) {
    die("
    <div style='text-align: center; padding: 50px; font-family: Arial;'>
        <h2 style='color: red;'>❌ Error: Metode Pembayaran Tidak Ditemukan</h2>
        <p>Metode pembayaran dengan ID $id_metode tidak ditemukan.</p>
        <a href='checkout.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Kembali ke Checkout</a>
        <br><br>
        <small><a href='?debug=1' style='color: #666;'>Debug Info</a></small>
    </div>
    ");
}

mysqli_begin_transaction($koneksi);

try {
    // 1. Buat entri PEMESANAN
    $tanggal = date('Y-m-d');
    $now = date('Y-m-d H:i:s');

    $queryInsertPemesanan = "INSERT INTO pemesanan (
        user_id, agensi_id, alamat, alamat_lengkap, kurir_id, pembayaran_id,
        total_harga, status, tanggal_pemesanan, created_at
    ) VALUES (
        $user_id, 1, '', '$alamat', 1, 1,
        $total_bayar, 'pending', '$tanggal', '$now'
    )";

    if (!mysqli_query($koneksi, $queryInsertPemesanan)) {
        throw new Exception("Error membuat pemesanan: " . mysqli_error($koneksi));
    }

    $id_pemesanan = mysqli_insert_id($koneksi);

    // 2. Ambil dan salin data dari keranjang ke detail_pesanan
    $valid_keranjang_processed = false;
    $total_items = 0;
    $total_harga_detail = 0;

    foreach ($keranjang_ids as $kid) {
        $kid = intval($kid);
        $q = mysqli_query($koneksi, "SELECT k.*, p.harga FROM keranjang k 
            JOIN produk p ON k.produk_id = p.produk_id 
            WHERE k.id = $kid AND k.user_id = $user_id AND k.status = 'aktif'");
        
        if ($row = mysqli_fetch_assoc($q)) {
    $produk_id = $row['produk_id'];
    $jumlah = $row['jumlah'];
    $harga_satuan = $row['harga'];
    $total_item = $harga_satuan * $jumlah;
    $queryDetailPesanan = "INSERT INTO detail_pesanan (
        pemesanan_id, produk_id, jumlah, total
    ) VALUES (
        $id_pemesanan, $produk_id, $jumlah, $total_item
    )";
    
    if (!mysqli_query($koneksi, $queryDetailPesanan)) {
        throw new Exception("Error menyimpan detail pesanan: " . mysqli_error($koneksi));
    }

    $queryUpdateKeranjang = "UPDATE keranjang SET status = 'checkout' WHERE id = $kid";
    if (!mysqli_query($koneksi, $queryUpdateKeranjang)) {
        throw new Exception("Error update keranjang: " . mysqli_error($koneksi));
    }
    $queryKurangiStok = "UPDATE produk SET stok = stok - $jumlah WHERE produk_id = $produk_id";
    if (!mysqli_query($koneksi, $queryKurangiStok)) {
        throw new Exception("Error mengurangi stok produk: " . mysqli_error($koneksi));
    }

    $valid_keranjang_processed = true;
    $total_items++;
    $total_harga_detail += $total_item;
}

    }

    if (!$valid_keranjang_processed) {
        throw new Exception("Tidak ada item keranjang yang valid untuk diproses");
    }

    $keranjang_id_ref = intval($keranjang_ids[0]);
    $queryPembayaran = "INSERT INTO pembayaran (
        keranjang_id, id_metode_pembayaran, status, tgl_pembayaran, total_bayar
    ) VALUES (
        $keranjang_id_ref, $id_metode, 'pending', NULL, $total_bayar
    )";

    if (!mysqli_query($koneksi, $queryPembayaran)) {
        throw new Exception("Error membuat pembayaran: " . mysqli_error($koneksi));
    }

    $id_pembayaran = mysqli_insert_id($koneksi);

    $queryUpdatePemesanan = "UPDATE pemesanan SET pembayaran_id = $id_pembayaran WHERE id = $id_pemesanan";
    if (!mysqli_query($koneksi, $queryUpdatePemesanan)) {
        throw new Exception("Error update pembayaran ID: " . mysqli_error($koneksi));
    }
    mysqli_commit($koneksi);
    $_SESSION['last_pemesanan_id'] = $id_pemesanan;

    // Redirect ke halaman menunggu pembayaran
    echo "<script>
        alert('Pesanan berhasil dibuat! Total $total_items item telah diproses.');
        window.location.href = 'menunggu-pembayaran.php?id=$id_pemesanan';
    </script>";

} catch (Exception $e) {
    // Rollback transaksi jika ada error
    mysqli_rollback($koneksi);
    
    die("
    <div style='text-align: center; padding: 50px; font-family: Arial;'>
        <h2 style='color: red;'>❌ Error: Gagal Memproses Pesanan</h2>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
        <a href='checkout.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Kembali ke Checkout</a>
        <br><br>
        <small><a href='?debug=1' style='color: #666;'>Debug Info</a></small>
    </div>
    ");
}
?>