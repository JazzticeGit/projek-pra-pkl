<?php
session_start();
include '../koneksi.php';

if (!isset($_GET['order_id']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Ambil data user
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id = $user_id"));

// Ambil keranjang
$keranjang = mysqli_query($koneksi, "SELECT * FROM keranjang WHERE user_id = $user_id");
$total = 0;
while ($item = mysqli_fetch_assoc($keranjang)) {
    $produk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM produk WHERE produk_id = " . $item['produk_id']));
    $subtotal = $produk['harga'] * $item['jumlah'];
    $total += $subtotal;
}

// Simpan pemesanan
mysqli_query($koneksi, "INSERT INTO pemesanan (user_id, alamat_lengkap, ongkir, created_at) VALUES (
    $user_id, 'Alamat Dummy', 0, NOW()
)");
$pemesanan_id = mysqli_insert_id($koneksi);

// Simpan detail pesanan
mysqli_data_seek($keranjang, 0);
while ($item = mysqli_fetch_assoc($keranjang)) {
    $produk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM produk WHERE produk_id = " . $item['produk_id']));
    $subtotal = $produk['harga'] * $item['jumlah'];

    mysqli_query($koneksi, "INSERT INTO detail_pesanan (pemesanan_id, produk_id, jumlah, total) VALUES (
        $pemesanan_id, {$item['produk_id']}, {$item['jumlah']}, $subtotal
    )");
}

// Simpan pembayaran
mysqli_query($koneksi, "INSERT INTO pembayaran (id_metode_pembayaran, status, tgl_pembayaran, pemesanan_id) VALUES (
    1, 'berhasil', NOW(), $pemesanan_id
)");

// Kosongkan keranjang
mysqli_query($koneksi, "DELETE FROM keranjang WHERE user_id = $user_id");

echo "<h2>Transaksi Berhasil!</h2>";
echo "<p>Terima kasih telah berbelanja di AGESA.</p>";
