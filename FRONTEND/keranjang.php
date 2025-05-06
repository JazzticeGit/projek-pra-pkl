<?php
include '../../koneksi.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM keranjang WHERE id = $id_hapus AND user_id = $user_id");
    header("Location: keranjang.php");
    exit;
}

// Update jumlah
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id_keranjang = intval($_POST['id_keranjang']);
    $jumlah = intval($_POST['jumlah']);
    if ($jumlah > 0) {
        // Ambil harga produk
        $q = mysqli_query($koneksi, "
            SELECT p.harga FROM keranjang k 
            JOIN produk p ON p.produk_id = k.produk_id 
            WHERE k.id = $id_keranjang
        ");
        $harga = mysqli_fetch_assoc($q)['harga'];
        $total = $jumlah * $harga;

        mysqli_query($koneksi, "
            UPDATE keranjang SET jumlah = $jumlah, total = $total 
            WHERE id = $id_keranjang AND user_id = $user_id
        ");
    }
    header("Location: keranjang.php");
    exit;
}

// Ambil data keranjang user
$data = mysqli_query($koneksi, "
    SELECT k.*, p.name, p.image, p.harga 
    FROM keranjang k 
    JOIN produk p ON p.produk_id = k.produk_id 
    WHERE k.user_id = $user_id
");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja</title>
    <link rel="stylesheet" href="../STYLESHEET/keranjang.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1>Keranjang Belanja</h1>
        <?php if (mysqli_num_rows($data) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $grand_total = 0; ?>
                    <?php while ($item = mysqli_fetch_assoc($data)): ?>
                        <?php $grand_total += $item['total']; ?>
                        <tr>
                            <td>
                                <img src="../<?= $item['image'] ?>" width="80" alt="<?= htmlspecialchars($item['name']) ?>">
                                <p><?= htmlspecialchars($item['name']) ?></p>
                            </td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="id_keranjang" value="<?= $item['id'] ?>">
                                    <input type="number" name="jumlah" value="<?= $item['jumlah'] ?>" min="1">
                                    <button type="submit" name="update"><i class="fa fa-refresh"></i></button>
                                </form>
                            </td>
                            <td>Rp<?= number_format($item['harga'], 0, ',', '.') ?></td>
                            <td>Rp<?= number_format($item['total'], 0, ',', '.') ?></td>
                            <td>
                                <a href="keranjang.php?hapus=<?= $item['id'] ?>" onclick="return confirm('Yakin ingin menghapus item ini?')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total Belanja</th>
                        <th colspan="2">Rp<?= number_format($grand_total, 0, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>

            <div class="checkout-btn">
                <a href="checkout.php" class="btn">Checkout Sekarang</a>
            </div>

        <?php else: ?>
            <p>Keranjang kamu kosong!</p>
        <?php endif; ?>
    </div>
</body>
</html>
