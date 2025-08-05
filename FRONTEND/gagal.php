<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Gagal</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #ffebee;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 450px;
        }

        .container img {
            width: 150px;
            margin-bottom: 20px;
        }

        h2 {
            color: #c62828;
            margin-bottom: 10px;
        }

        p {
            font-size: 16px;
            color: #666;
            margin-bottom: 25px;
        }

        .btn {
            display: inline-block;
            margin: 8px;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            color: white;
            transition: 0.3s;
        }

        .btn-retry {
            background-color: #e53935;
        }

        .btn-retry:hover {
            background-color: #b71c1c;
        }

        .btn-help {
            background-color: #757575;
        }

        .btn-help:hover {
            background-color: #424242;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="https://media.giphy.com/media/26BRuo6sLetdllPAQ/giphy.gif" alt="Failed GIF">
        <h2>Pembayaran Gagal!</h2>
        <p>Terjadi kesalahan saat memproses pembayaran Anda. Silakan coba kembali atau hubungi bantuan.</p>
        <a href="../FRONTEND/checkout.php" class="btn btn-retry">Coba Lagi</a>
        <a href="../FRONTEND/contact.php" class="btn btn-help">Hubungi Bantuan</a>
    </div>
</body>
</html>
