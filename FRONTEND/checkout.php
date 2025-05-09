<?php
session_start();
$total = isset($_SESSION['total_harga']) ? $_SESSION['total_harga'] : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-0KijI4JUjVxvvbYV"></script>
</head>
<body>
    <h2>Konfirmasi Pembayaran</h2>
    <p>Total yang harus dibayar: <strong>Rp<?= number_format($total, 0, ',', '.') ?></strong></p>
    
    <button id="pay-button">Bayar Sekarang</button>

    <script>
    document.getElementById('pay-button').addEventListener('click', function () {
        fetch('../BACKEND/get-snap-token.php')
            .then(response => response.json())
            .then(data => {
                snap.pay(data.token, {
                    onSuccess: function(result) {
                        alert("Pembayaran berhasil!");
                        console.log(result);
                        window.location.href = 'success.php'; // redirect jika sukses
                    },
                    onPending: function(result) {
                        alert("Menunggu pembayaran...");
                        console.log(result);
                    },
                    onError: function(result) {
                        alert("Pembayaran gagal.");
                        console.log(result);
                    }
                });
            });
    });
    </script>
</body>
</html>
