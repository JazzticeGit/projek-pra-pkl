<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang'])) {
    echo "<h2>Keranjang kamu kosong</h2>";
    exit;
}

// HAPUS PRODUK DARI KERANJANG
if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['id'])) {
    $hapus_id = (int)$_GET['id'];
    if (isset($_SESSION['keranjang'][$hapus_id])) {
        unset($_SESSION['keranjang'][$hapus_id]);
    }
    header("Location: keranjang.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja</title>
    <link rel="stylesheet" href="../../STYLESHEET/keranjang-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="container">
    <h1>Keranjang Belanja Kamu</h1>

    <table class="keranjang-table">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Nama</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_semua = 0;

foreach ($_SESSION['keranjang'] as $produk_id => $jumlah):
    // Pastikan $produk_id dan $jumlah adalah angka
    $produk_id = (int) $produk_id;
    $jumlah = (int) $jumlah;

    // Ambil data produk dari database
    $query = mysqli_query($koneksi, "SELECT * FROM produk WHERE produk_id = $produk_id");
    $produk = mysqli_fetch_assoc($query);

    if (!$produk) continue; // jika produk tidak ditemukan, skip

    // Pastikan harga adalah angka
    $harga = (int) $produk['harga'];
    $total = $harga * $jumlah;
    $total_semua += $total;
?>
    <tr>
       <tr>
    <td><img src="../<?= htmlspecialchars($produk['image']) ?>" width="80"></td>
    <td><?= htmlspecialchars($produk['name']) ?></td>
    <td>Rp<?= number_format($harga, 0, ',', '.') ?></td>
    <td><?= $jumlah ?></td>
    <td>Rp<?= number_format($total, 0, ',', '.') ?></td>
    <td>
        <a href="keranjang.php?action=hapus&id=<?= $produk_id ?>" onclick="return confirm('Yakin ingin menghapus produk ini dari keranjang?')" class="hapus-btn">Hapus</a>
    </td>
</tr>

    </tr>
<?php endforeach; ?>
        </tbody>
    </table>

    <div class="checkout-box">
        <h3>Total Belanja: Rp<?= number_format($total_semua, 0, ',', '.') ?></h3>
        <a href="checkout.php" class="checkout-btn">Checkout Sekarang</a>
    </div>
</div>

</body>
</html>
