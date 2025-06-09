<?php

session_start();

include '../../koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../FRONTEND/login.php");
    exit;
}


// Cek apakah admin sudah login
// if (!isset($_SESSION['admin_id'])) {
//     echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
//     exit;
// }

// Validasi input
if (!isset($_POST['pembayaran_id']) || !isset($_POST['status_baru'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$pembayaran_id = (int)$_POST['pembayaran_id'];
$status_baru = mysqli_real_escape_string($koneksi, $_POST['status_baru']);

// Validasi status
$allowed_status = ['pending', 'berhasil', 'gagal'];
if (!in_array($status_baru, $allowed_status)) {
    echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
    exit;
}

try {
    mysqli_autocommit($koneksi, false); 
    
    // Update status pembayaran
    $queryUpdatePembayaran = "UPDATE pembayaran SET 
        status = '$status_baru'
        WHERE id = $pembayaran_id";
    
    if (!mysqli_query($koneksi, $queryUpdatePembayaran)) {
        throw new Exception('Gagal mengupdate status pembayaran: ' . mysqli_error($koneksi));
    }
    
    // Jika status berhasil, update juga status pemesanan dan keranjang
    if ($status_baru === 'berhasil') {
        // Ambil data pembayaran untuk mendapatkan keranjang_id
        $queryPembayaran = mysqli_query($koneksi, "SELECT keranjang_id FROM pembayaran WHERE id = $pembayaran_id");
        $pembayaran = mysqli_fetch_assoc($queryPembayaran);
        
        if ($pembayaran) {
            $keranjang_id = $pembayaran['keranjang_id'];
            
            // Update status keranjang menjadi 'checkout' atau 'selesai'
            $queryUpdateKeranjang = "UPDATE keranjang SET 
                status = 'checkout' 
                WHERE id = $keranjang_id";
            
            if (!mysqli_query($koneksi, $queryUpdateKeranjang)) {
                throw new Exception('Gagal mengupdate status keranjang');
            }
            
            // Update status pemesanan menjadi 'berhasil'
            $queryUpdatePemesanan = "UPDATE pemesanan p 
                JOIN keranjang k ON p.user_id = k.user_id 
                SET p.status = 'berhasil' 
                WHERE k.id = $keranjang_id 
                AND p.status IN ('pending', 'belum dibayar')
                ORDER BY p.created_at DESC 
                LIMIT 1";
            
            if (!mysqli_query($koneksi, $queryUpdatePemesanan)) {
                throw new Exception('Gagal mengupdate status pemesanan');
            }
            
            // Log aktivitas admin (opsional)
            $admin_id = $_SESSION['admin_id'];
            $queryLog = "INSERT INTO log_admin (admin_id, aksi, keterangan, tanggal) 
                VALUES ($admin_id, 'verifikasi_pembayaran', 'Verifikasi pembayaran ID: $pembayaran_id - Status: $status_baru', NOW())";
            mysqli_query($koneksi, $queryLog); // Tidak perlu throw error untuk log
        }
    }
    
    // Jika status gagal, bisa dikembalikan ke status pending di pemesanan
    if ($status_baru === 'gagal') {
        $queryPembayaran = mysqli_query($koneksi, "SELECT keranjang_id FROM pembayaran WHERE id = $pembayaran_id");
        $pembayaran = mysqli_fetch_assoc($queryPembayaran);
        
        if ($pembayaran) {
            $keranjang_id = $pembayaran['keranjang_id'];
            
            // Update status pemesanan kembali ke 'belum dibayar'
            $queryUpdatePemesanan = "UPDATE pemesanan p 
                JOIN keranjang k ON p.user_id = k.user_id 
                SET p.status = 'belum dibayar' 
                WHERE k.id = $keranjang_id 
                ORDER BY p.created_at DESC 
                LIMIT 1";
            
            mysqli_query($koneksi, $queryUpdatePemesanan);
        }
    }
    
    mysqli_commit($koneksi);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Status pembayaran berhasil diupdate menjadi: ' . ucfirst($status_baru)
    ]);
    
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_autocommit($koneksi, true);
?>