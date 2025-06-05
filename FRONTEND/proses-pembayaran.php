<?php
session_start();
include '../koneksi.php';

$user_id = $_SESSION['user_id'] ?? 1; // Sesuaikan dengan sistem login kamu
$alamat_lengkap = mysqli_real_escape_string($koneksi, $_POST['alamat_lengkap']);
$id_metode = intval($_POST['id_metode_pembayaran']);
$total_bayar = floatval($_POST['total_bayar']);

// Ambil data keranjang user
$queryKeranjang = mysqli_query($koneksi, "SELECT * FROM keranjang WHERE user_id = $user_id");
if (mysqli_num_rows($queryKeranjang) == 0) {
    echo "Keranjang kosong.";
    exit;
}

// Simpan keranjang ke variabel sementara
$keranjang_id = null;
$items = [];
$subtotal = 0;

while ($item = mysqli_fetch_assoc($queryKeranjang)) {
    $items[] = $item;
    $subtotal += $item['total'];
    $keranjang_id = $item['id']; // Ambil salah satu id keranjang (disimpan di pemesanan/pembayaran)
}

// Simpan ke tabel pembayaran
mysqli_query($koneksi, "INSERT INTO pembayaran (keranjang_id, id_metode_pembayaran, status, tgl_pembayaran)
  VALUES ($keranjang_id, $id_metode, 'pending', NOW())");
$pembayaran_id = mysqli_insert_id($koneksi);

// Buat pesanan baru
mysqli_query($koneksi, "INSERT INTO pemesanan 
  (user_id, keranjang_id, alamat, alamat_lengkap, kurir_id, agensi_id, pembayaran_id, total_harga, status, created_at, updated_at, tanggal_pemesanan)
  VALUES 
  ($user_id, $keranjang_id, '', '$alamat_lengkap', 1, 1, $pembayaran_id, $total_bayar, 'pending', NOW(), NOW(), CURDATE())");

$pemesanan_id = mysqli_insert_id($koneksi);

// Simpan ke detail_pesanan
foreach ($items as $item) {
    $produk_id = $item['produk_id'];
    $jumlah = $item['jumlah'];
    $total = $item['total'];

    mysqli_query($koneksi, "INSERT INTO detail_pesanan (pemesanan_id, produk_id, jumlah, total) 
        VALUES ($pemesanan_id, $produk_id, $jumlah, $total)");
}

// Kosongkan keranjang user
mysqli_query($koneksi, "DELETE FROM keranjang WHERE user_id = $user_id");

// Arahkan ke halaman menunggu pembayaran
header("Location: menunggu-pembayaran.php?id_pesanan=$pemesanan_id");
exit;
