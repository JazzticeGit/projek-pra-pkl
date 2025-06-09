<?php
session_start();
include '../koneksi.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='../login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
if (!isset($_POST['id_pemesanan']) || empty($_POST['id_pemesanan'])) {
    echo "<script>alert('ID Pemesanan tidak valid!'); window.history.back();</script>";
    exit;
}

$id_pemesanan = (int)$_POST['id_pemesanan'];
$catatan = mysqli_real_escape_string($koneksi, $_POST['catatan'] ?? '');
$queryValidasi = mysqli_query($koneksi, "SELECT id FROM pemesanan WHERE id = $id_pemesanan AND user_id = $user_id");
if (mysqli_num_rows($queryValidasi) == 0) {
    echo "<script>alert('Pemesanan tidak ditemukan atau bukan milik Anda!'); window.location.href='keranjang.php';</script>";
    exit;
}
if (!isset($_FILES['bukti_transfer']) || $_FILES['bukti_transfer']['error'] !== UPLOAD_ERR_OK) {
    echo "<script>alert('File bukti transfer harus diupload!'); window.history.back();</script>";
    exit;
}

$file = $_FILES['bukti_transfer'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedExtensions)) {
    echo "<script>alert('Format file tidak diizinkan! Gunakan JPG, PNG, atau PDF.'); window.history.back();</script>";
    exit;
}

$maxFileSize = 5 * 1024 * 1024; // 5MB 
if ($fileSize > $maxFileSize) {
    echo "<script>alert('Ukuran file terlalu besar! Maksimal 5MB.'); window.history.back();</script>";
    exit;
}
$newFileName = 'bukti_' . $id_pemesanan . '_' . time() . '.' . $fileExtension;
$uploadPath = '../uploads/bukti_transfer/';
if (!file_exists($uploadPath)) {
    mkdir($uploadPath, 0777, true);
}

$fullUploadPath = $uploadPath . $newFileName;
if (!move_uploaded_file($fileTmpName, $fullUploadPath)) {
    echo "<script>alert('Gagal mengupload file!'); window.history.back();</script>";
    exit;
}

try {
    mysqli_autocommit($koneksi, false);
        $queryKeranjang = mysqli_query($koneksi, "SELECT k.id as keranjang_id 
        FROM keranjang k 
        JOIN pemesanan p ON k.user_id = p.user_id 
        WHERE p.id = $id_pemesanan AND k.user_id = $user_id 
        ORDER BY k.id DESC LIMIT 1");
    
    $keranjang = mysqli_fetch_assoc($queryKeranjang);
    
    if (!$keranjang) {
        throw new Exception('Data keranjang tidak ditemukan!');
    }
    
    $keranjang_id = $keranjang['keranjang_id'];
    
    // Cek apakah sudah ada record pembayaran
    $queryPembayaranCek = mysqli_query($koneksi, "SELECT id FROM pembayaran WHERE keranjang_id = $keranjang_id");
    
    if (mysqli_num_rows($queryPembayaranCek) > 0) {
        // Update record yang sudah ada
        $queryUpdate = "UPDATE pembayaran SET 
            bukti_transfer = '$newFileName',
            catatan = '$catatan',
            status = 'pending',
            tgl_pembayaran = NOW()
            WHERE keranjang_id = $keranjang_id";
        
        if (!mysqli_query($koneksi, $queryUpdate)) {
            throw new Exception('Gagal mengupdate data pembayaran: ' . mysqli_error($koneksi));
        }
    } else {
        // Insert record baru
        // Ambil data metode pembayaran default atau yang dipilih sebelumnya
        $queryMetode = mysqli_query($koneksi, "SELECT id FROM metode_pembayaran ORDER BY id ASC LIMIT 1");
        $metode = mysqli_fetch_assoc($queryMetode);
        $id_metode_pembayaran = $metode['id'] ?? 1;
        
        // Ambil total dari pemesanan
        $queryTotal = mysqli_query($koneksi, "SELECT total_harga FROM pemesanan WHERE id = $id_pemesanan");
        $pemesanan = mysqli_fetch_assoc($queryTotal);
        $total_bayar = $pemesanan['total_harga'];
        
        $queryInsert = "INSERT INTO pembayaran (
            keranjang_id, 
            id_metode_pembayaran, 
            total_bayar, 
            bukti_transfer, 
            catatan, 
            status, 
            tgl_pembayaran
        ) VALUES (
            $keranjang_id, 
            $id_metode_pembayaran, 
            $total_bayar, 
            '$newFileName', 
            '$catatan', 
            'pending', 
            NOW()
        )";
        
        if (!mysqli_query($koneksi, $queryInsert)) {
            throw new Exception('Gagal menyimpan data pembayaran: ' . mysqli_error($koneksi));
        }
    }
    
    // Update status pemesanan
    $queryUpdatePemesanan = "UPDATE pemesanan SET 
        status = 'pending'
        WHERE id = $id_pemesanan";
    
    if (!mysqli_query($koneksi, $queryUpdatePemesanan)) {
        throw new Exception('Gagal mengupdate status pemesanan: ' . mysqli_error($koneksi));
    }
    
    // Commit transaksi
    mysqli_commit($koneksi);
    
    // Redirect ke halaman sukses atau kembali ke waiting page
    echo "<script>
        alert('Bukti transfer berhasil diupload! Menunggu verifikasi admin.');
        window.location.href='menunggu-pembayaran.php?id=$id_pemesanan';
    </script>";
    
} catch (Exception $e) {
    // Rollback transaksi jika ada error
    mysqli_rollback($koneksi);
    
    // Hapus file yang sudah diupload jika ada error database
    if (file_exists($fullUploadPath)) {
        unlink($fullUploadPath);
    }
    
    echo "<script>
        alert('Error: " . addslashes($e->getMessage()) . "');
        window.history.back();
    </script>";
}

// Kembalikan autocommit ke true
mysqli_autocommit($koneksi, true);
?>