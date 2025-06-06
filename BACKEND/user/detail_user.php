<?php include '../../koneksi.php'; ?>
<?php
$id_user = intval($_GET['id']);
$user = $koneksi->query("SELECT * FROM users WHERE id=$id_user")->fetch_assoc();

if (!$user) {
    echo "<script>alert('User tidak ditemukan'); window.location.href='users.php';</script>";
    exit;
}

$keranjang = $koneksi->query("SELECT * FROM keranjang WHERE user_id=$id_user AND status IN ('aktif','checkout')");
$keranjangCount = $koneksi->query("SELECT COUNT(*) as count FROM keranjang WHERE user_id=$id_user AND status IN ('aktif','checkout')")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User: <?= htmlspecialchars($user['username']) ?> - Admin Panel</title>
    <link rel="stylesheet" href="../../STYLESHEET/user.css">
</head>
<body>
    <div class="detail-container">
        <h2>Detail User</h2>
        
        <!-- User Information Section -->
        <div class="user-info">
            <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Phone:</strong> <?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : '<span style="color: #bdc3c7;">Tidak ada</span>' ?></p>
            <p><strong>Tanggal Lahir:</strong> <?= !empty($user['birth']) ? date('d F Y', strtotime($user['birth'])) : '<span style="color: #bdc3c7;">Tidak ada</span>' ?></p>
            <p><strong>Bergabung:</strong> <?= !empty($user['created_at']) ? date('d F Y H:i', strtotime($user['created_at'])) : '<span style="color: #bdc3c7;">Tidak diketahui</span>' ?></p>
        </div>

        <h3>Keranjang Belanja (<?= $keranjangCount ?> item)</h3>
        
        <?php if ($keranjangCount > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Jumlah</th>
                        <th>Ukuran</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalValue = 0;
                    while($item = $keranjang->fetch_assoc()) {
                        $produk = $koneksi->query("SELECT name FROM produk WHERE produk_id={$item['produk_id']}")->fetch_assoc();
                        $totalValue += $item['total'];
                    ?>
                    <tr>
                        <td data-label="Produk"><?= htmlspecialchars($produk['name'] ?? 'Produk tidak ditemukan') ?></td>
                        <td data-label="Jumlah"><?= $item['jumlah'] ?></td>
                        <td data-label="Ukuran"><?= htmlspecialchars($item['size']) ?></td>
                        <td data-label="Total">Rp <?= number_format($item['total'], 0, ',', '.') ?></td>
                        <td data-label="Status">
                            <span class="status-badge status-<?= $item['status'] ?>">
                                <?= ucfirst($item['status']) ?>
                            </span>
                        </td>
                        <td data-label="Tanggal">
                            <?= !empty($item['created_at']) ? date('d/m/Y H:i', strtotime($item['created_at'])) : '-' ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            
            <!-- Summary -->
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #27ae60;">
                <strong>Total Nilai Keranjang: Rp <?= number_format($totalValue, 0, ',', '.') ?></strong>
            </div>
            
        <?php else: ?>
            <div class="empty-state">
                <p>User ini belum memiliki item di keranjang</p>
            </div>
        <?php endif; ?>

        <a href="user.php">Kembali ke daftar user</a>
    </div>
</body>
</html>