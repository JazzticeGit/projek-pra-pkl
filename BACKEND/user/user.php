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
 ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar User - Admin Panel</title>
    <link rel="stylesheet" href="../../STYLESHEET/user.css">
    <link rel="stylesheet" href="../../STYLESHEET/nav-admin.css">
</head>
<body>

<!-- NAV BE -->

   <!-- Sidebar Navigation - Always Active -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <!-- <h3>Admin Panel</h3> -->
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
                <a href="../user/user.php" class="nav-item active">
                    <i>👥</i> Daftar User
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Produk</div>
                <a href="../produk/index-produk.php" class="nav-item">
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

    <!-- Main Content -->
    <div class="main-content">
        <h2>Daftar User</h2>
        
        <!-- Search and Controls Section -->
        <div class="controls">
            <input type="text" class="search-box" placeholder="Cari user berdasarkan username atau email..." id="searchInput">
        </div>
        
        <div class="clearfix" id="userGrid">
            <?php
            $query = $koneksi->query("SELECT * FROM users ORDER BY username ASC");
            $userCount = 0;
            while($user = $query->fetch_assoc()) {
                $userCount++;
            ?>
                <div class="user-box" onclick="location.href='detail_user.php?id=<?= $user['id'] ?>'" data-username="<?= strtolower($user['username']) ?>" data-email="<?= strtolower($user['email']) ?>">
                    <strong><?= htmlspecialchars($user['username']) ?></strong>
                    <div style="color: #7f8c8d; margin-top: 0.5rem;">
                        <?= htmlspecialchars($user['email']) ?>
                    </div>
                    <?php if (!empty($user['phone'])): ?>
                        <div style="color: #95a5a6; font-size: 0.9rem; margin-top: 0.25rem;">
                            📞 <?= htmlspecialchars($user['phone']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php } ?>
            
            <?php if ($userCount == 0): ?>
                <div class="empty-state">
                    <p>Tidak ada user yang terdaftar</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- User Statistics -->
        <div style="margin-top: 2rem; padding: 1rem; background: white; border-radius: 8px; border-left: 4px solid #3498db;">
            <strong>Total User: <?= $userCount ?></strong>
        </div>
    </div>

    <script>
        // Simple search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const userBoxes = document.querySelectorAll('.user-box');
            
            userBoxes.forEach(box => {
                const username = box.getAttribute('data-username');
                const email = box.getAttribute('data-email');
                
                if (username.includes(searchTerm) || email.includes(searchTerm)) {
                    box.style.display = 'block';
                } else {
                    box.style.display = 'none';
                }
            });
        });

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