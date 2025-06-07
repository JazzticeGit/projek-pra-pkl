<?php
session_start();
include '../koneksi.php';

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM pemesanan WHERE user_id = $user_id ORDER BY id DESC LIMIT 1";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

if ($data['status'] !== 'berhasil') {
    header("Location: menunggu-pembayaran.php"); // atau halaman sebelumnya
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Berhasil</title>
</head>
<body>
    <h2>Pembayaran Berhasil!</h2>
    <p>Terima kasih, pesanan Anda telah berhasil diproses.</p>
</body>
</html>
