<?php
session_start();
include '../FRONTEND/session_config.php';
include '../../koneksi.php';

// Validasi admin session
validateAdminSession($koneksi);

// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     header("Location: ../../FRONTEND/login.php");
//     exit;
// }


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($koneksi, $_POST['name']);
    $stok = $_POST['stok'];
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $harga = $_POST['harga'];
    $best_seller = isset($_POST['best_seller']) ? 1 : 0;
    $new_arrival = isset($_POST['new_arrival']) ? 1 : 0;
    $id_kategori = $_POST['id_kategori'];

    $upload_dir = "../../image/";
    $original_file_name = $_FILES["image"]["name"];
    $clean_file_name = preg_replace("/[^a-zA-Z0-9\.\-_]/", "_", $original_file_name);
    $target_file = $upload_dir . $clean_file_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image = "image/" . $clean_file_name;

        $query = "INSERT INTO produk (name, stok, deskripsi, harga, image, best_seller, new_arrival, id_kategori)
                  VALUES ('$name', '$stok', '$deskripsi', '$harga', '$image', '$best_seller', '$new_arrival', '$id_kategori')";

        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Produk berhasil ditambahkan!'); window.location.href='index-produk.php';</script>";
        } else {
            echo "Error saat insert: " . mysqli_error($koneksi);
        }
    } else {
        echo "Upload gambar gagal!";
    }
}

$query = "SELECT 
            p.*, 
            k.jenis_produk, 
            d.persen_diskon, 
            d.start_date, 
            d.end_date 
          FROM produk p
          JOIN kategori k ON p.id_kategori = k.id
          LEFT JOIN diskon d 
            ON p.produk_id = d.produk_id 
            AND d.status = 'active' 
            AND NOW() BETWEEN d.start_date AND d.end_date";

$result = mysqli_query($koneksi, $query);

$kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori");
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Produk</title>
    <link rel="stylesheet" href="../../STYLESHEET/dashboard.css">
    <link rel="stylesheet" href="../../STYLESHEET/nav-admin.css">

</head>
<body>


   <!-- Sidebar Navigation - Always Active -->

<!-- Sidebar Navigation - Always Active -->
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
    <h1>Produk</h1> <br><br>
    <h2>Tambah Produk Baru</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Nama Produk" required>

        <input type="number" name="stok" placeholder="Stok" required>

        <textarea name="deskripsi" placeholder="Deskripsi Produk" required></textarea>

        <input type="number" step="0.01" name="harga" placeholder="Harga" required>

        <input type="file" name="image" accept="image/*" required>

        <label><input type="checkbox" name="best_seller"> Best Seller</label>
        <label><input type="checkbox" name="new_arrival"> New Arrival</label>

        <select name="id_kategori" required>
            <option value="">Pilih Kategori</option>
            <?php while($kategori = mysqli_fetch_assoc($kategori_query)): ?>
                <option value="<?= $kategori['id'] ?>"><?= htmlspecialchars($kategori['jenis_produk']) ?></option>
            <?php endwhile; ?>
        </select>
        
        <button type="submit">Tambah Produk</button>
    </form>

    <h2>Daftar Produk</h2>
    <div class="container">
        <?php while($produk = mysqli_fetch_assoc($result)): 
            $harga_asli = $produk['harga'];
            $diskon = isset($produk['persen_diskon']) ? $produk['persen_diskon'] : 0;
            $harga_diskon = $diskon > 0 ? $harga_asli - ($harga_asli * $diskon / 100) : $harga_asli;
        ?>
            <div class="card">
                <?php if ($diskon > 0): ?>
                    <div class="badge-custom">Diskon <?= $diskon ?>%</div>
                <?php endif; ?>
                
                <img src="../../<?= htmlspecialchars($produk['image']) ?>" alt="<?= htmlspecialchars($produk['name']) ?>">
                
                <h3><?= htmlspecialchars($produk['name']) ?></h3>
                
                <p>Stok: <?= $produk['stok'] ?></p>
                
                <?php if ($diskon > 0): ?>
                    <p><del>Rp<?= number_format($harga_asli, 0, ',', '.') ?></del></p>
                    <p><strong style="color:red">Rp<?= number_format($harga_diskon, 0, ',', '.') ?></strong></p>
                <?php else: ?>
                    <p>Rp<?= number_format($harga_asli, 0, ',', '.') ?></p>
                <?php endif; ?>

                <p><?= htmlspecialchars($produk['jenis_produk']) ?></p>
                <p>Ukuran: <?= $produk['size'] ?></p>

                <?php if($produk['best_seller']): ?>
                    <p><span style="color: #e17055;">🔥 Best Seller</span></p>
                <?php endif; ?>
                <?php if($produk['new_arrival']): ?>
                    <p><span style="color: #00cec9;">🆕 New Arrival</span></p>
                <?php endif; ?>

                <div class="actions">
                    <a href="edit.php?id=<?= $produk['produk_id'] ?>" class="btn edit">Edit</a>
                    <!-- <a href="../../BACKEND/diskon/index.php?id=<?= $produk['produk_id'] ?>" class="btn edit">Diskon</a> -->
                    <a href="hapus.php?id=<?= $produk['produk_id'] ?>" class="btn delete" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>


<script>
     // Hamburger menu functionality
       // Mobile navigation (only for mobile devices)
const mobileToggle = document.getElementById('mobileToggle');
const sidebar = document.getElementById('sidebar');
const mobileOverlay = document.getElementById('mobileOverlay');

function toggleMobileMenu() {
    sidebar.classList.toggle('mobile-active');
    mobileOverlay.classList.toggle('active');
}

// Show mobile toggle on small screens
function checkScreenSize() {
    if (window.innerWidth <= 768) {
        mobileToggle.style.display = 'block';
    } else {
        mobileToggle.style.display = 'none';
        sidebar.classList.remove('mobile-active');
        mobileOverlay.classList.remove('active');
    }
}

mobileToggle.addEventListener('click', toggleMobileMenu);
mobileOverlay.addEventListener('click', toggleMobileMenu);

// Check screen size on load and resize
checkScreenSize();
window.addEventListener('resize', checkScreenSize);

// Close mobile menu when clicking nav item
const navItems = document.querySelectorAll('.nav-item');
navItems.forEach(item => {
    item.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            toggleMobileMenu();
        }
    });
});

// Active nav item highlighting
const currentPage = window.location.pathname;
navItems.forEach(item => {
    if (item.getAttribute('href') === currentPage || 
        currentPage.includes(item.getAttribute('href'))) {
        item.classList.add('active');
    }
});
</script>
</body>
</html>
