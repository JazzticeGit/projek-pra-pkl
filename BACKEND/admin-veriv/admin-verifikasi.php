<?php
session_start();
include '../../koneksi.php';

// Cek jika admin login, bisa kamu sesuaikan logika validasinya
// if (!isset($_SESSION['admin'])) {
//     header("Location: login.php");
//     exit;
//}

$query = "SELECT pembayaran.*, users.username, users.email
          FROM pembayaran
          JOIN keranjang ON pembayaran.keranjang_id = keranjang.id
          JOIN users ON keranjang.user_id = users.id
          WHERE pembayaran.status = 'pending'";


$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Verifikasi Pembayaran</title>
    <link rel="stylesheet" href="../STYLESHEET/admin-style.css">
</head>
<body>
    <h2>Verifikasi Pembayaran</h2>

    <table border="1" cellpadding="10">
        <tr>
            <th>No</th>
            <th>User</th>
            <th>Email</th>
            <th>Total</th>
            <th>Tanggal</th>
            <th>Bukti Transfer</th>
            <th>Catatan</th>
            <th>Aksi</th>
        </tr>

        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td>Rp<?= number_format($row['total_bayar']) ?></td>
            <td><?= $row['tgl_pembayaran'] ?></td>
            <td>
                <a href="../bukti_transfer/<?= $row['bukti_transfer'] ?>" target="_blank">Lihat</a>
            </td>
            <td><?= htmlspecialchars($row['catatan']) ?></td>
            <td>
                <form action="verifikasi-proses.php" method="POST">
                    <input type="hidden" name="pembayaran_id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="pemesanan_id" value="<?= $row['pemesanan_id'] ?>">
                    <button type="submit" onclick="return confirm('Yakin ingin memverifikasi pembayaran ini?')">Verifikasi</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
