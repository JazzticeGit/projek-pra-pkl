<?php
session_start();

include '../koneksi.php';

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Aksi: Tambah, Kurang, Hapus
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    switch ($_GET['action']) {
        case 'add':
            $_SESSION['keranjang'][$id]['jumlah'] += 1;
            break;
        case 'minus':
            if ($_SESSION['keranjang'][$id]['jumlah'] > 1) {
                $_SESSION['keranjang'][$id]['jumlah'] -= 1;
            }
            break;
        case 'remove':
            unset($_SESSION['keranjang'][$id]);
            break;
    }
    header("Location: keranjang.php");
    exit;
}

// Hitung total semua item
$total_harga = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $total_harga += $item['harga'] * $item['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja</title>
    <link rel="stylesheet" href="styles.css"> <!-- Ganti sesuai file CSS kamu -->
    <style>
        body { font-family: sans-serif; padding: 30px; }
        .keranjang-container { max-width: 800px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: center; }
        img { width: 60px; }
        .actions a { margin: 0 5px; text-decoration: none; font-weight: bold; }
        .total { text-align: right; margin-top: 20px; font-size: 18px; }
        .empty { text-align: center; padding: 50px; font-size: 20px; }
    </style>
</head>
<body>

<div class="keranjang-container">
    <h2>Keranjang Belanja</h2>

    <?php if (empty($_SESSION['keranjang'])): ?>
        <div class="empty">Keranjang kamu kosong.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['keranjang'] as $id => $item): ?>
                    <tr>
                        <td><img src="../<?= htmlspecialchars($item['gambar']) ?>"></td>
                        <td><?= htmlspecialchars($item['nama']) ?></td>
                        <td>Rp<?= number_format($item['harga'], 0, ',', '.') ?></td>
                        <td>
                            <div class="actions">
                                <a href="?action=minus&id=<?= $id ?>">-</a>
                                <?= $item['jumlah'] ?>
                                <a href="?action=add&id=<?= $id ?>">+</a>
                            </div>
                        </td>
                        <td>Rp<?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></td>
                        <td><a href="?action=remove&id=<?= $id ?>" style="color: red;">Hapus</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total">
            <strong>Total: Rp<?= number_format($total_harga, 0, ',', '.') ?></strong>
        </div>
        <br>
        <a href="checkout.php" style="padding: 10px 20px; background: #000; color: #fff; text-decoration: none;">Checkout</a>
    <?php endif; ?>
</div>

</body>
</html>
