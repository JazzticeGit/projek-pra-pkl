<?php
session_start();
include '../../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pembayaran_id = intval($_POST['pembayaran_id']);
    $pemesanan_id = intval($_POST['pemesanan_id']);

    // Ubah status pembayaran dan pemesanan
    $update_pembayaran = mysqli_query($koneksi, "UPDATE pembayaran SET status = 'berhasil' WHERE id = $pembayaran_id");
    $update_pemesanan  = mysqli_query($koneksi, "UPDATE pemesanan SET status = 'berhasil' WHERE id = $pemesanan_id");

    if ($update_pembayaran && $update_pemesanan) {
        header("Location: admin-verifikasi.php?msg=success");
        exit;
    } else {
        echo "Gagal memverifikasi pembayaran.";
    }
} else {
    die("Akses tidak valid.");
}
?>
