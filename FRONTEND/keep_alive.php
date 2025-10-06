<?php
// keep_alive.php - File untuk menjaga session tetap aktif
header('Content-Type: application/json');

include 'session_config.php';
include '../koneksi.php';

// Cek apakah session masih valid
if (!checkAndUpdateSession()) {
    echo json_encode(['status' => 'expired']);
    exit;
}

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'unauthorized']);
    exit;
}

// Update last activity
$_SESSION['last_activity'] = time();

echo json_encode([
    'status' => 'active',
    'last_activity' => $_SESSION['last_activity'],
    'time' => date('Y-m-d H:i:s')
]);
?>