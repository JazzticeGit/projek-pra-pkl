<?php
session_start();
include '../FRONTEND/session_config.php';
include '../../koneksi.php';

// Validasi admin session
validateAdminSession($koneksi);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["pembayaran_id"])) {
    $id = (int) $_POST["pembayaran_id"];
    $status = mysqli_real_escape_string($koneksi, $_POST["status"]);

    // Mulai transaksi 
    mysqli_autocommit($koneksi, false);

    try {
        // Update status pembayaran 
        $query = "UPDATE pembayaran SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
            throw new Exception("Gagal update status di tabel pembayaran");
        }

        // Update juga status di tabel pemesanan
        $updatePemesanan = "UPDATE pemesanan SET status = ? WHERE pembayaran_id = ?";
        $stmt2 = mysqli_prepare($koneksi, $updatePemesanan);
        mysqli_stmt_bind_param($stmt2, "si", $status, $id);
        $result2 = mysqli_stmt_execute($stmt2);

        if (!$result2) {
            throw new Exception("Gagal update status di tabel pemesanan");
        }

        // Commit transaksi jika berhasil semua
        mysqli_commit($koneksi);
        echo "<script>alert('Status berhasil diperbarui'); window.location='admin-verifikasi.php';</script>";
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($koneksi);
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.location='admin-verifikasi.php';</script>";
    }

    // Kembalikan ke autocommit normal
    mysqli_autocommit($koneksi, true);
}

// Ambil data pembayaran yang perlu diverifikasi
$filter_status = mysqli_real_escape_string($koneksi, $_GET['status'] ?? 'all');
$search = mysqli_real_escape_string($koneksi, $_GET['search'] ?? '');

$whereClause = "";
if ($filter_status !== 'all') {
    $whereClause = "WHERE pb.status = '$filter_status'";
} else {
    $whereClause = "WHERE 1=1"; // kondisi selalu true untuk menampilkan semua
}

if (!empty($search)) {
    if ($filter_status !== 'all') {
        $whereClause .= " AND (u.username LIKE '%$search%' OR pb.id LIKE '%$search%')";
    } else {
        $whereClause .= " AND (u.username LIKE '%$search%' OR pb.id LIKE '%$search%')";
    }
}


// QUERY YANG DIPERBAIKI - menghilangkan JOIN yang menyebabkan duplikasi
$queryPembayaran = "SELECT 
    pb.id as pembayaran_id,
    pb.keranjang_id,
    pb.total_bayar,
    pb.bukti_transfer,
    pb.catatan,
    pb.status,
    pb.tgl_pembayaran,
    u.username,
    u.email,
    mp.nama as metode_nama,
    mp.norek
FROM pembayaran pb
JOIN keranjang k ON pb.keranjang_id = k.id
JOIN users u ON k.user_id = u.id
LEFT JOIN metode_pembayaran mp ON pb.id_metode_pembayaran = mp.id
$whereClause
ORDER BY pb.tgl_pembayaran DESC, pb.id DESC";

$resultPembayaran = mysqli_query($koneksi, $queryPembayaran);

// Hitung statistik
$queryStats = "SELECT 
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
    COUNT(CASE WHEN status = 'berhasil' THEN 1 END) as berhasil,
    COUNT(CASE WHEN status = 'gagal' THEN 1 END) as gagal,
    COUNT(CASE WHEN status = 'dikirim' THEN 1 END) as dikirim,
    COUNT(CASE WHEN status = 'diterima' THEN 1 END) as diterima,
    COUNT(*) as total
FROM pembayaran";
$resultStats = mysqli_query($koneksi, $queryStats);
$stats = mysqli_fetch_assoc($resultStats);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Verifikasi Pembayaran</title>
    <link rel="stylesheet" href="../../STYLESHEET/admin-veriv.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../STYLESHEET/nav-admin.css">

</head>
<body>
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Admin Panel</h3>
        <p>Sistem Manajemen Backend</p>
    </div>
    
    <div class="nav-menu">
        <div class="nav-section">
            <div class="nav-section-title">Dashboard</div>
            <a href="../dashboard.php" class="nav-item">
                <i>📊</i> Dashboard Utama
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Manajemen User</div>
            <a href="../user/user.php" class="nav-item">
                <i>👥</i> Daftar User
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Produk</div>
            <a href="../produk/index-produk.php" class="nav-item active">
                <i>📦</i> Daftar Produk
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Verifikasi</div>
            <a href="../admin-veriv/admin-verifikasi.php" class="nav-item">
                <i>✅</i> Admin Verifikasi
            </a>
        </div>
    </div>
</nav>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

<div class="main-content">
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-credit-card"></i> Verifikasi Pembayaran</h1>
            <p>Kelola dan verifikasi bukti pembayaran dari pelanggan</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success_message ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= $error_message ?>
            </div>
        <?php endif; ?>

        <!-- Statistik -->
        <div class="stats-cards">
    <div class="stat-card pending">
        <div class="stat-number"><?= $stats['pending'] ?></div>
        <div>Pending</div>
    </div>
    <div class="stat-card berhasil">
        <div class="stat-number"><?= $stats['berhasil'] ?></div>
        <div>Berhasil</div>
    </div>
    <div class="stat-card gagal">
        <div class="stat-number"><?= $stats['gagal'] ?></div>
        <div>Gagal</div>
    </div>
    <div class="stat-card dikirim">
        <div class="stat-number"><?= $stats['dikirim'] ?></div>
        <div>Dikirim</div>
    </div>
    <div class="stat-card diterima">
        <div class="stat-number"><?= $stats['diterima'] ?></div>
        <div>Diterima</div>
    </div>
    <div class="stat-card total">
        <div class="stat-number"><?= $stats['total'] ?></div>
        <div>Total</div>
    </div>
</div>

        <!-- Filter -->
        <div class="filters">
    <form method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
        <select name="status">
    <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>Semua Status</option>
    <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
    <option value="berhasil" <?= $filter_status === 'berhasil' ? 'selected' : '' ?>>Berhasil</option>
    <option value="gagal" <?= $filter_status === 'gagal' ? 'selected' : '' ?>>Gagal</option>
    <option value="dikirim" <?= $filter_status === 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
    <option value="diterima" <?= $filter_status === 'diterima' ? 'selected' : '' ?>>Diterima</option>
</select>
        <input type="text" name="search" placeholder="Cari username atau ID..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><i class="fas fa-search"></i> Filter</button>
        <a href="?" class="btn btn-info"><i class="fas fa-refresh"></i> Reset</a>
    </form>
</div>

        <!-- Tabel Pembayaran -->
        <div class="payments-table">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Bukti</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($resultPembayaran) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($resultPembayaran)): ?>
                                <tr>
                                    <td>#<?= $row['pembayaran_id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['username']) ?></strong><br>
                                        <small><?= htmlspecialchars($row['email']) ?></small>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['tgl_pembayaran'])) ?></td>
                                    <td>Rp <?= number_format($row['total_bayar']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($row['metode_nama'] ?? 'Transfer Bank') ?><br>
                                        <small><?= htmlspecialchars($row['norek'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= $row['status'] ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['bukti_transfer']): ?>
                                            <button class="btn btn-info" onclick="lihatBukti('<?= $row['bukti_transfer'] ?>')">
                                                <i class="fas fa-eye"></i> Lihat
                                            </button>
                                        <?php else: ?>
                                            <span style="color: #999;">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary" onclick="updateStatus(<?= $row['pembayaran_id'] ?>, '<?= $row['status'] ?>')">
                                            <i class="fas fa-edit"></i> Update
                                        </button>
                                    <a href="detail_verifikasi.php?id=<?= $row['pembayaran_id'] ?>" class="btn btn-info">
                                        <i class="fas fa-info"></i> Detail
                                    </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 10px;"></i><br>
                                    Tidak ada data pembayaran
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div> 

    <!-- Modal Update Status -->
    <div id="modalUpdateStatus" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('modalUpdateStatus')">&times;</span>
            <h2><i class="fas fa-edit"></i> Update Status Pembayaran</h2>
            <form method="POST">
                <input type="hidden" id="pembayaran_id" name="pembayaran_id">
                
                <div class="form-group">
    <label>Status Baru:</label>
    <select name="status" id="status_baru" required>
        <option value="pending">Pending</option>
        <option value="berhasil">Berhasil</option>
        <option value="gagal">Gagal</option>
        <option value="dikirim">Dikirim</option>
        <option value="diterima">Diterima</option>
    </select>
</div>
                
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Lihat Bukti -->
    <div id="modalLihatBukti" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('modalLihatBukti')">&times;</span>
            <h2><i class="fas fa-image"></i> Bukti Transfer</h2>
            <div id="buktiContent" style="text-align: center;"></div>
        </div>
    </div>
</div>

    <script>
        function updateStatus(pembayaranId, currentStatus) {
            document.getElementById('pembayaran_id').value = pembayaranId;
            document.getElementById('status_baru').value = currentStatus;
            document.getElementById('modalUpdateStatus').style.display = 'block';
        }

        function lihatBukti(fileName) {
            const buktiPath = '../../uploads/bukti_transfer/' + fileName;
            const fileExt = fileName.split('.').pop().toLowerCase();
            
            let content = '';
            if (['jpg', 'jpeg', 'png'].includes(fileExt)) {
                content = `<img src="${buktiPath}" class="bukti-image" alt="Bukti Transfer" style="max-width: 100%; height: auto;">`;
            } else if (fileExt === 'pdf') {
                content = `<embed src="${buktiPath}" width="100%" height="500px" type="application/pdf">`;
            } else {
                content = `<p>File tidak dapat ditampilkan. <a href="${buktiPath}" target="_blank">Download File</a></p>`;
            }
            
            document.getElementById('buktiContent').innerHTML = content;
            document.getElementById('modalLihatBukti').style.display = 'block';
        }

        function lihatDetail(pembayaranId) {
            // Implementasi untuk melihat detail pembayaran
            alert('Detail pembayaran ID: ' + pembayaranId);
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = ['modalUpdateStatus', 'modalLihatBukti'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Auto refresh setiap 30 detik untuk status pending
        <?php if ($filter_status === 'pending'): ?>
        setInterval(() => {
            location.reload();
        }, 30000);
        <?php endif; ?>

         // Hamburger menu functionality
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const mainContent = document.getElementById('mainContent');

        function toggleMenu() {
            hamburgerBtn.classList.toggle('active');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        hamburgerBtn.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);

        // Close menu when clicking nav item (for better UX on mobile)
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    toggleMenu();
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                hamburgerBtn.classList.remove('active');
            }
        });

        // Active nav item highlighting
        const currentPage = window.location.pathname;
        navItems.forEach(item => {
            if (item.getAttribute('href') === currentPage || 
                currentPage.includes(item.getAttribute('href'))) {
                item.classList.add('active');
            }
        });

        <?php if ($filter_status === 'pending'): ?>
setInterval(() => {
    location.reload();
}, 30000);
<?php endif; ?>
    </script>
</body>
</html>