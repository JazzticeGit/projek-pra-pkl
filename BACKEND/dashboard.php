<?php
session_start();
include '../../koneksi.php'; 
include '../session_config.php';

// Validasi admin session
validateAdminSession($koneksi);
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../FRONTEND/login.php");
    exit;
}


 

//  user
$result_user = mysqli_query($koneksi, "SELECT COUNT(*) AS total_user FROM users");
$data_user = mysqli_fetch_assoc($result_user);
$total_user = $data_user['total_user'];

//  produk
$result_produk = mysqli_query($koneksi, "SELECT COUNT(*) AS total_produk FROM produk");
$data_produk = mysqli_fetch_assoc($result_produk);
$total_produk = $data_produk['total_produk'];

//  transaksi
$result_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) AS total_transaksi FROM pembayaran");
$data_transaksi = mysqli_fetch_assoc($result_transaksi);
$total_transaksi = $data_transaksi['total_transaksi'];
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Backend</title>
    <link rel="stylesheet" href="../STYLESHEET/admin-dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

 <style>
       * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            overflow-x: hidden;
        }

        /* Header dengan hamburger button */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1000;
        }

        .hamburger {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 4px;
            transition: all 0.3s ease;
            margin-right: 1rem;
        }

        .hamburger:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .hamburger span {
            display: block;
            width: 25px;
            height: 3px;
            background: white;
            margin: 5px 0;
            transition: 0.3s;
            border-radius: 2px;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        /* Sidebar Navigation */
        .sidebar {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            transition: left 0.3s ease;
            z-index: 999;
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar-header {
            background: rgba(0,0,0,0.2);
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h3 {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .sidebar-header p {
            color: #bdc3c7;
            font-size: 0.9rem;
        }

        .nav-menu {
            padding: 1rem 0;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            color: #95a5a6;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 1.5rem;
            margin-bottom: 0.5rem;
        }

        .nav-item {
            display: block;
            color: #ecf0f1;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }

        .nav-item:hover::before {
            left: 100%;
        }

        .nav-item:hover {
            background-color: rgba(52, 152, 219, 0.2);
            color: #3498db;
            padding-left: 2rem;
        }

        .nav-item.active {
            background-color: rgba(52, 152, 219, 0.3);
            color: #3498db;
            border-right: 3px solid #3498db;
        }

        .nav-item i {
            margin-right: 0.75rem;
            width: 18px;
            text-align: center;
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0,0,0,0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 998;
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        .content-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .content-card h2 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .content-card p {
            color: #7f8c8d;
            line-height: 1.6;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            opacity: 0.9;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                padding: 1rem;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .sidebar {
                width: 100vw;
            }
        }
    </style>





   <!-- <div class="header">
        <button class="hamburger" id="hamburgerBtn">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <h1>Admin Dashboard</h1>
    </div> -->

    <!-- Sidebar Navigation -->
    <!-- <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Admin Panel</h3>
            <p>Sistem Manajemen Backend</p>
        </div>
        
        <div class="nav-menu">
            <div class="nav-section">
                <div class="nav-section-title">Dashboard</div>
                <a href="index.php" class="nav-item active">
                    <i>📊</i> Dashboard Utama
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Manajemen User</div>
                <a href="user/index.php" class="nav-item">
                    <i>👥</i> Daftar User
                </a>
                <a href="user/user.php" class="nav-item">
                    <i>👤</i> Kelola User
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Produk</div>
                <a href="produk/index-produk.php" class="nav-item">
                    <i>📦</i> Daftar Produk
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Verifikasi</div>
                <a href="admin-verif/admin-verifikasi.php" class="nav-item">
                    <i>✅</i> Admin Verifikasi
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Sistem</div>
                <a href="diskon/index.php" class="nav-item">
                    <i>🏷️</i> Manajemen Diskon
                </a>
            </div>
        </div>
    </nav> -->

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <div class="container-h">
        <header class="header-h">
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h1>
            <p>Kelola sistem backend dengan mudah</p>
        </header>

        <div class="dashboard-grid">
            <!-- Admin Verifikasi Section -->
            <div class="section-card">
                <h3><i class="fas fa-user-shield"></i> Admin Verifikasi</h3>
                <div class="button-group">
                    <button class="nav-button" onclick="navigate('../BACKEND/admin-veriv/admin-verifikasi.php')">
                        <i class="fas fa-check-circle"></i>
                        Admin Verifikasi
                    </button>
                    <!-- <button class="nav-button" onclick="navigate('proses-update-status.php')">
                        <i class="fas fa-sync-alt"></i>
                        Update Status
                    </button> -->
                </div>
            </div>

            <!-- Diskon Section -->
            <!-- <div class="section-card">
                <h3><i class="fas fa-percent"></i> Manajemen Diskon</h3>
                <div class="button-group">
                    <button class="nav-button" onclick="navigate('end-date.php')">
                        <i class="fas fa-calendar-times"></i>
                        Atur Tanggal Berakhir
                    </button>
                    <button class="nav-button" onclick="navigate('index.php')">
                        <i class="fas fa-list"></i>
                        Daftar Diskon
                    </button>
                </div>
            </div> -->

            <!-- Produk Section -->
            <div class="section-card">
                <h3><i class="fas fa-box"></i> Manajemen Produk</h3>
                <div class="button-group">
                    <!-- <button class="nav-button" onclick="navigate('../BACKEND/produk/edit.php')">
                        <i class="fas fa-edit"></i>
                        Edit Produk
                    </button> -->
                    <!-- <button class="nav-button" onclick="navigate('hapus.php')">
                        <i class="fas fa-trash-alt"></i>
                        Hapus Produk
                    </button> -->
                    <button class="nav-button" onclick="navigate('../BACKEND/produk/index-produk.php')">
                        <i class="fas fa-th-large"></i>
                        Daftar Produk
                    </button>
                </div>
            </div>

            <!-- User Section -->
            <div class="section-card">
                <h3><i class="fas fa-users"></i> Manajemen User</h3>
                <div class="button-group">
                    <!-- <button class="nav-button" onclick="navigate('detail_user.php')">
                        <i class="fas fa-user-circle"></i>
                        Detail User
                    </button> -->
                    <button class="nav-button" onclick="navigate('../BACKEND/user/user.php')">
                        <i class="fas fa-users-cog"></i>
                        Kelola User
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats cpt -->
        <div class="stats-section">
            <h3><i class="fas fa-chart-bar"></i> Statistik Cepat</h3>
            <div class="stats-grid">
                <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-info">
                    <h4>Total User</h4>
                    <span class="stat-number"><?php echo $total_user; ?></span>
                </div>
            </div>
                <div class="stat-card">
    <i class="fas fa-box"></i>
    <div class="stat-info">
        <h4>Total Produk</h4>
        <span class="stat-number"><?php echo $total_produk; ?></span>
    </div>
</div>
                <div class="stat-card">
    <i class="fas fa-money-check-alt"></i>
    <div class="stat-info">
        <h4>Total Transaksi</h4>
        <span class="stat-number"><?php echo $total_transaksi; ?></span>
    </div>
</div>
            </div>
        </div>
    </div>

    <script>
        function navigate(page) {
            // Simulasi navigasi - dalam implementasi nyata, ini akan redirect ke halaman yang sesuai
            alert(`Navigasi ke: ${page}`);
            window.location.href = page;
        }

        // Animasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.section-card, .stat-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Efek hover pada tombol
        document.querySelectorAll('.nav-button').forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

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
    </script>
</body>
</html>