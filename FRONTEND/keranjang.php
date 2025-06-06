<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $user_id = $_SESSION['user_id'];

    // Soft delete: ubah status jadi 'hapus'
    $query = "UPDATE keranjang SET status = 'hapus' WHERE id = '$id' AND user_id = '$user_id'";
    mysqli_query($koneksi, $query);

    header("Location: keranjang.php");
    exit;
}


// Tambah jumlah
if (isset($_GET['tambah'])) {
    $id = $_GET['tambah'];
    mysqli_query($koneksi, "UPDATE keranjang SET jumlah = jumlah + 1 WHERE id = '$id' AND user_id = '$user_id'");
    header("Location: keranjang.php");
    exit;
}

// Kurang jumlah
if (isset($_GET['kurang'])) {
    $id = $_GET['kurang'];
    $cek = mysqli_query($koneksi, "SELECT jumlah FROM keranjang WHERE id = '$id' AND user_id = '$user_id'");
    $data = mysqli_fetch_assoc($cek);
    if ($data['jumlah'] > 1) {
        mysqli_query($koneksi, "UPDATE keranjang SET jumlah = jumlah - 1 WHERE id = '$id' AND user_id = '$user_id'");
    }
    header("Location: keranjang.php");
    exit;
}

// Ambil data keranjang user (hanya yang status-nya 'aktif')
$query = mysqli_query($koneksi, "
SELECT keranjang.*, produk.name, produk.harga, produk.image
FROM keranjang
JOIN produk ON keranjang.produk_id = produk.produk_id
WHERE keranjang.user_id = '$user_id' AND keranjang.status = 'aktif'
");

$total = 0;
$ongkir = 10000;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja</title>
    <link rel="stylesheet" href="../STYLESHEET/keranjang.css">
</head>
<body>
    <div class="container">
        <h2>Keranjang Belanja</h2>
        <table>
            <tr>
                <th>Gambar</th>
                <th>Produk</th>
                <th>Ukuran</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
                <th>Aksi</th>
            </tr>
            <?php while ($item = mysqli_fetch_assoc($query)):
                $subtotal = $item['harga'] * $item['jumlah'];
                $total += $subtotal;
            ?>
            <tr>
                <td><img src="../<?php echo $item['image']; ?>" width="60"></td>
                <td><?php echo $item['name']; ?></td>
                <td><?php echo $item['size']; ?></td>
                <td>Rp<?php echo number_format($item['harga']); ?></td>
                <td>
                    <a href="?kurang=<?php echo $item['id']; ?>">-</a>
                    <?php echo $item['jumlah']; ?>
                    <a href="?tambah=<?php echo $item['id']; ?>">+</a>
                </td>
                <td>Rp<?php echo number_format($subtotal); ?></td>
                <td><a href="?hapus=<?php echo $item['id']; ?>" onclick="return confirm('Hapus produk ini dari keranjang?')">Hapus</a></td>
            </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="5" style="text-align: right;"><strong>Ongkir</strong></td>
                <td colspan="2">Rp<?php echo number_format($ongkir); ?></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: right;"><strong>Total</strong></td>
                <td colspan="2">Rp<?php echo number_format($total + $ongkir); ?></td>
            </tr>
        </table>
        <a href="checkout.php" class="checkout-btn">Checkout</a>
    </div>
</body>
</html>
