<?php
session_start();
include '../koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$id_pemesanan = $_POST['id_pemesanan'] ?? 0;
$id_metode_pembayaran = $_POST['id_metode_pembayaran'] ?? 0;
$total = $_POST['total'] ?? 0;
$catatan = $_POST['catatan'] ?? '';

// Validasi data
if (!$id_pemesanan || !$id_metode_pembayaran || !$total) {
    die("Data tidak lengkap.");
}

// Validasi pemesanan milik user
$queryValidasi = mysqli_query($koneksi, "SELECT * FROM pemesanan WHERE id = $id_pemesanan AND user_id = $user_id");
if (!$queryValidasi || mysqli_num_rows($queryValidasi) == 0) {
    die("Pemesanan tidak valid atau bukan milik Anda.");
}

// Validasi file upload
if (!isset($_FILES['bukti_transfer']) || $_FILES['bukti_transfer']['error'] !== UPLOAD_ERR_OK) {
    die("File bukti transfer harus diupload.");
}

$file = $_FILES['bukti_transfer'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];

// Validasi tipe file
$allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedTypes)) {
    die("Format file tidak valid. Hanya JPG, PNG, dan PDF yang diizinkan.");
}

// Validasi ukuran file (max 5MB)
if ($fileSize > 5 * 1024 * 1024) {
    die("Ukuran file terlalu besar. Maksimal 5MB.");
}

// Buat direktori upload jika belum ada
$uploadDir = '../uploads/bukti_transfer/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate nama file unik
$newFileName = 'bukti_' . $id_pemesanan . '_' . time() . '.' . $fileExtension;
$uploadPath = $uploadDir . $newFileName;

// Upload file
if (!move_uploaded_file($fileTmpName, $uploadPath)) {
    die("Gagal mengupload file.");
}

try {
    // Mulai transaksi database
    mysqli_begin_transaction($koneksi);
    
    // ** PERBAIKAN: Hindari subquery dengan LIMIT dalam IN clause **
    // Cari keranjang IDs yang terkait dengan pemesanan ini terlebih dahulu
    $queryKeranjangIds = mysqli_query($koneksi, "
        SELECT DISTINCT k.id as keranjang_id
        FROM keranjang k 
        INNER JOIN detail_pesanan dp ON k.produk_id = dp.produk_id 
        WHERE dp.pemesanan_id = $id_pemesanan AND k.user_id = $user_id
        ORDER BY k.id ASC
    ");
    
    if (!$queryKeranjangIds) {
        throw new Exception("Error mencari keranjang: " . mysqli_error($koneksi));
    }
    
    $keranjang_ids = [];
    while ($row = mysqli_fetch_assoc($queryKeranjangIds)) {
        $keranjang_ids[] = $row['keranjang_id'];
    }
    
    if (empty($keranjang_ids)) {
        throw new Exception("Tidak ditemukan keranjang yang terkait dengan pemesanan ini.");
    }
    
    // Ambil keranjang_id pertama untuk referensi pembayaran
    $keranjang_id_ref = $keranjang_ids[0];
    
    // Cek apakah sudah ada pembayaran untuk keranjang ini
    $queryFindPembayaran = mysqli_query($koneksi, "
        SELECT * FROM pembayaran 
        WHERE keranjang_id = $keranjang_id_ref
    ");
    
    if (!$queryFindPembayaran) {
        throw new Exception("Error mencari pembayaran: " . mysqli_error($koneksi));
    }
    
    $catatan_escaped = mysqli_real_escape_string($koneksi, $catatan);
    
    if (mysqli_num_rows($queryFindPembayaran) > 0) {
        // Update pembayaran yang sudah ada
        $queryUpdatePembayaran = "UPDATE pembayaran SET 
                                 status = 'berhasil', 
                                 tgl_pembayaran = NOW()
                                 WHERE keranjang_id = $keranjang_id_ref";
    } else {
        // Insert pembayaran baru jika belum ada
        $queryUpdatePembayaran = "INSERT INTO pembayaran (
                                 keranjang_id, id_metode_pembayaran, status, tgl_pembayaran
                                 ) VALUES (
                                 $keranjang_id_ref, $id_metode_pembayaran, 'berhasil', NOW()
                                 )";
    }
    
    if (!mysqli_query($koneksi, $queryUpdatePembayaran)) {
        throw new Exception("Gagal update pembayaran: " . mysqli_error($koneksi));
    }
    
    // Update status pemesanan menjadi 'berhasil'
    $queryUpdatePemesanan = "UPDATE pemesanan SET 
                            status = 'berhasil',
                            updated_at = NOW()
                            WHERE id = $id_pemesanan AND user_id = $user_id";
    
    if (!mysqli_query($koneksi, $queryUpdatePemesanan)) {
        throw new Exception("Gagal update pemesanan: " . mysqli_error($koneksi));
    }
    
    // Update status keranjang menjadi 'hapus' untuk semua keranjang terkait
    // Gunakan temporary table untuk menghindari masalah subquery
    $tempTableName = 'temp_keranjang_' . $user_id . '_' . time();
    
    // Buat temporary table
    $queryCreateTemp = "CREATE TEMPORARY TABLE $tempTableName AS
                        SELECT DISTINCT k.id
                        FROM keranjang k
                        INNER JOIN detail_pesanan dp ON k.produk_id = dp.produk_id 
                        WHERE dp.pemesanan_id = $id_pemesanan AND k.user_id = $user_id";
    
    if (!mysqli_query($koneksi, $queryCreateTemp)) {
        throw new Exception("Gagal membuat temporary table: " . mysqli_error($koneksi));
    }
    
    // Update keranjang menggunakan temporary table
    $queryUpdateKeranjang = "UPDATE keranjang 
                            SET status = 'hapus'
                            WHERE id IN (SELECT id FROM $tempTableName)";
    
    if (!mysqli_query($koneksi, $queryUpdateKeranjang)) {
        throw new Exception("Gagal update keranjang: " . mysqli_error($koneksi));
    }
    
    // Hapus temporary table
    mysqli_query($koneksi, "DROP TEMPORARY TABLE IF EXISTS $tempTableName");
    
    // Simpan informasi file bukti transfer (opsional - buat tabel bukti_transfer jika diperlukan)
    $queryBukti = "INSERT INTO bukti_transfer (
                   pemesanan_id, nama_file, ukuran_file, catatan, created_at
                   ) VALUES (
                   $id_pemesanan, '$newFileName', $fileSize, '$catatan_escaped', NOW()
                   )";
    
    // Jika tabel bukti_transfer belum ada, buat terlebih dahulu atau skip query ini
    mysqli_query($koneksi, $queryBukti); // Tidak throw error jika tabel tidak ada
    
    // Commit transaksi
    mysqli_commit($koneksi);
    
    // Redirect ke halaman sukses
    header('Location: sukses.php?id=' . $id_pemesanan);
    exit;
    
} catch (Exception $e) {
    // Rollback transaksi jika terjadi error
    mysqli_rollback($koneksi);
    
    // Hapus file yang sudah diupload jika terjadi error
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    
    die("Terjadi kesalahan: " . $e->getMessage());
}
?>