<?php include '../../koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar User - Admin Panel</title>
    <link rel="stylesheet" href="../../STYLESHEET/user.css">
</head>
<body>

<!-- NAV BE -->

   <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: -0.5px;
        }

        /* Hamburger Button */
        .hamburger {
            display: flex;
            flex-direction: column;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.1);
            border: none;
        }

        .hamburger:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.05);
        }

        .hamburger span {
            width: 24px;
            height: 3px;
            background: white;
            margin: 2px 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }

        /* Navigation Overlay */
        .nav-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1001;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .nav-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Navigation Panel */
        .nav-panel {
            position: fixed;
            top: 0;
            right: -100%;
            width: 350px;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 20px rgba(0,0,0,0.15);
            transition: right 0.3s ease;
            z-index: 1002;
            overflow-y: auto;
        }

        .nav-panel.active {
            right: 0;
        }

        .nav-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .nav-header h2 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .nav-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        /* Navigation Menu */
        .nav-menu {
            padding: 1rem 0;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section-title {
            padding: 0.5rem 2rem;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 0.5rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 2rem;
            color:rgb(255, 255, 255);
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .nav-item:hover {
            background: #f1f5f9;
            border-left-color: #667eea;
            color: #1e293b;
        }

        .nav-item.active {
            background: #e0e7ff;
            border-left-color: #667eea;
            color: #3730a3;
            font-weight: 500;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            margin-right: 1rem;
            opacity: 0.7;
        }

        /* Close Button */
        .close-btn {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.2s ease;
        }

        .close-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }

        /* Main Content */
        .main-content {
            margin-top: 80px;
            padding: 2rem;
            min-height: calc(100vh - 80px);
        }

        .content-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .content-card h1 {
            color: #1e293b;
            margin-bottom: 1rem;
            font-size: 2rem;
        }

        .content-card p {
            color: #64748b;
            margin-bottom: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-panel {
                width: 100%;
                max-width: 320px;
            }
            
            .header {
                padding: 1rem;
            }
            
            .main-content {
                padding: 1rem;
            }
        }

        /* Animation untuk smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Prevent body scroll when nav is open */
        body.nav-open {
            overflow: hidden;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">Admin Panel</div>
        <button class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </header>

    <!-- Navigation Overlay -->
    <div class="nav-overlay" id="navOverlay"></div>

    <!-- Navigation Panel -->
    <nav class="nav-panel" id="navPanel">
        <div class="nav-header">
            <button class="close-btn" id="closeBtn">&times;</button>
            <h2>Navigation</h2>
            <p>Backend Administration</p>
        </div>
        
        <div class="nav-menu">
            <!-- Dashboard Section -->
            <div class="nav-section">
                <div class="nav-section-title">Dashboard</div>
                <a href="#" class="nav-item active">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                    Overview
                </a>
                <a href="#" class="nav-item">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                    </svg>
                    Analytics
                </a>
            </div>

            <!-- Content Management -->
            <div class="nav-section">
                <div class="nav-section-title">Content Management</div>
                <a href="#" class="nav-item">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z"/>
                    </svg>
                    Posts
                </a>
                <a href="#" class="nav-item">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/>
                    </svg>
                    Media
                </a>
                <a href="#" class="nav-item">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 11-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z"/>
                    </svg>
                    Categories
                </a>
            </div>

            <!-- User Management -->
            <div class="nav-section">
                <div class="nav-section-title">User Management</div>
                <a href="#" class="nav-item">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                    Users
                </a>
                <a href="#" class="nav-item">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z"/>
                    </svg>
                    Permissions
                </a>
            </div>

            <!-- Settings -->
            <div class="nav-section">
                <div class="nav-section-title">System</div>
                <a href="#" class="nav-item">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"/>
                    </svg>
                    Settings
                </a>
                <a href="#" class="nav-item">
                    <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    Backup
                </a>
            </div>
        </div>
    </nav>


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

        const hamburger = document.getElementById('hamburger');
        const navOverlay = document.getElementById('navOverlay');
        const navPanel = document.getElementById('navPanel');
        const closeBtn = document.getElementById('closeBtn');
        const body = document.body;

        // Open navigation
        function openNav() {
            hamburger.classList.add('active');
            navOverlay.classList.add('active');
            navPanel.classList.add('active');
            body.classList.add('nav-open');
        }

        // Close navigation
        function closeNav() {
            hamburger.classList.remove('active');
            navOverlay.classList.remove('active');
            navPanel.classList.remove('active');
            body.classList.remove('nav-open');
        }

        // Event listeners
        hamburger.addEventListener('click', openNav);
        closeBtn.addEventListener('click', closeNav);
        navOverlay.addEventListener('click', closeNav);

        // Close nav on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeNav();
            }
        });

        // Handle nav item clicks
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Remove active class from all items
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                // Add active class to clicked item
                this.classList.add('active');
                
                // Close navigation on mobile after selection
                if (window.innerWidth <= 768) {
                    setTimeout(closeNav, 300);
                }
            });
        });

    </script>
</body>
</html>