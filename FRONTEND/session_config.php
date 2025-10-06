<?php
// session_config.php - Include file ini di setiap halaman yang memerlukan session

// Konfigurasi session PHP
ini_set('session.gc_maxlifetime', 7200); // 2 jam
ini_set('session.cookie_lifetime', 7200);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

// Set cookie parameters
session_set_cookie_params([
    'lifetime' => 7200,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']), // auto-detect HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk cek dan perbarui session
function checkAndUpdateSession() {
    $timeout = 7200; // 2 jam dalam detik
    
    // Regenerate session ID setiap 30 menit
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Cek timeout
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > $timeout) {
            // Session expired
            session_unset();
            session_destroy();
            return false;
        }
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    return true;
}

// Fungsi untuk validasi admin
function validateAdminSession($koneksi) {
    // Cek session masih valid
    if (!checkAndUpdateSession()) {
        header("Location: ../FRONTEND/login.php?expired=1");
        exit;
    }
    
    // Cek role dan user_id
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin' || !isset($_SESSION['user_id'])) {
        header("Location: ../FRONTEND/login.php");
        exit;
    }
    
    // Validasi user masih ada di database
    $user_id = mysqli_real_escape_string($koneksi, $_SESSION['user_id']);
    $check_user = mysqli_query($koneksi, "SELECT id, role FROM users WHERE id = '$user_id' AND role = 'admin' LIMIT 1");
    
    if (!$check_user || mysqli_num_rows($check_user) == 0) {
        session_unset();
        session_destroy();
        header("Location: ../FRONTEND/login.php?invalid=1");
        exit;
    }
    
    return true;
}
?>