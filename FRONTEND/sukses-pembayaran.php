<?php
session_start();
include '../koneksi.php';

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM pemesanan WHERE user_id = $user_id ORDER BY id DESC LIMIT 1";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

if ($data['status'] !== 'berhasil') {
    header("Location: menunggu-pembayaran.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Berhasil</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            text-align: center;
            max-width: 400px;
        }

        h2 {
            color: #4CAF50;
            margin-bottom: 16px;
        }

        p {
            font-size: 16px;
            margin-bottom: 24px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 6px;
            border-radius: 8px;
            border: none;
            text-decoration: none;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-home {
            background-color: #2196F3;
        }

        .btn-home:hover {
            background-color: #1976D2;
        }

        .btn-history {
            background-color: #4CAF50;
        }

        .btn-history:hover {
            background-color: #388E3C;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Pembayaran Berhasil!</h2>
        <p>Terima kasih, pesanan Anda telah berhasil diproses.</p>
        <a href="../FRONTEND/index.php" class="btn btn-home">Kembali ke Beranda</a>
        <a href="riwayat-transaksi.php" class="btn btn-history">Cek Riwayat Transaksi</a>
    </div>
</body>
</html>
