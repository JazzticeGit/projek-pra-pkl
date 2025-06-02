<?php
include '../../koneksi.php';

$now = date('Y-m-d H:i:s');

// 1. Update status menjadi "menunggu" jika belum dimulai
$query_pending = "UPDATE diskon SET status = 'menunggu' WHERE start_date > '$now'";
mysqli_query($koneksi, $query_pending);

// 2. Update status menjadi "active" jika sudah dimulai dan belum berakhir
$query_active = "UPDATE diskon SET status = 'active' WHERE start_date <= '$now' AND end_date > '$now'";
mysqli_query($koneksi, $query_active);

// 3. Update status menjadi "expired" jika sudah berakhir
$query_expired = "UPDATE diskon SET status = 'expired' WHERE end_date <= '$now'";
mysqli_query($koneksi, $query_expired);

// Fungsi untuk mendapatkan status diskon real-time
function getDiscountStatus($start_date, $end_date) {
    $now = date('Y-m-d H:i:s');
    
    if ($now < $start_date) {
        return 'menunggu'; // Belum dimulai
    } elseif ($now >= $start_date && $now < $end_date) {
        return 'active'; // Sedang berlangsung
    } else {
        return 'expired'; // Sudah berakhir
    }
}

// Fungsi untuk mendapatkan countdown timer
function getCountdownData($start_date, $end_date) {
    $now = new DateTime();
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    
    $data = array();
    
    if ($now < $start) {
        // Countdown ke mulai diskon
        $interval = $now->diff($start);
        $data['type'] = 'starts_in';
        $data['message'] = 'Dimulai dalam';
        $data['status'] = 'menunggu';
        $data['days'] = $interval->days;
        $data['hours'] = $interval->h;
        $data['minutes'] = $interval->i;
        $data['seconds'] = $interval->s;
        $data['total_seconds'] = ($interval->days * 24 * 60 * 60) + ($interval->h * 60 * 60) + ($interval->i * 60) + $interval->s;
    } elseif ($now >= $start && $now < $end) {
        // Countdown ke akhir diskon
        $interval = $now->diff($end);
        $data['type'] = 'ends_in';
        $data['message'] = 'Berakhir dalam';
        $data['status'] = 'active';
        $data['days'] = $interval->days;
        $data['hours'] = $interval->h;
        $data['minutes'] = $interval->i;
        $data['seconds'] = $interval->s;
        $data['total_seconds'] = ($interval->days * 24 * 60 * 60) + ($interval->h * 60 * 60) + ($interval->i * 60) + $interval->s;
    } else {
        // Diskon sudah berakhir
        $data['type'] = 'expired';
        $data['message'] = 'Sudah berakhir';
        $data['status'] = 'expired';
        $data['days'] = 0;
        $data['hours'] = 0;
        $data['minutes'] = 0;
        $data['seconds'] = 0;
        $data['total_seconds'] = 0;
    }
    
    return $data;
}

// Fungsi untuk format countdown display
function formatCountdown($days, $hours, $minutes, $seconds) {
    $parts = array();
    
    if ($days > 0) {
        $parts[] = $days . ' hari';
    }
    if ($hours > 0) {
        $parts[] = $hours . ' jam';
    }
    if ($minutes > 0) {
        $parts[] = $minutes . ' menit';
    }
    if ($seconds > 0 && $days == 0) { 
        $parts[] = $seconds . ' detik';
    }
    
    if (empty($parts)) {
        return '0 detik';
    }
    
    return implode(', ', $parts);
}

// Fungsi untuk mendapatkan semua diskon dengan status real-time
function getAllDiscountsWithStatus() {
    global $koneksi;
    
    $query = "SELECT d.*, p.name as product_name 
              FROM diskon d 
              LEFT JOIN produk p ON d.produk_id = p.produk_id 
              ORDER BY d.start_date ASC";
    
    $result = mysqli_query($koneksi, $query);
    $discounts = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $row['real_status'] = getDiscountStatus($row['start_date'], $row['end_date']);
        $row['countdown'] = getCountdownData($row['start_date'], $row['end_date']);
        $row['countdown_text'] = formatCountdown(
            $row['countdown']['days'],
            $row['countdown']['hours'],
            $row['countdown']['minutes'],
            $row['countdown']['seconds']
        );
        $discounts[] = $row;
    }
    
    return $discounts;
}

// Fungsi untuk mendapatkan diskon berdasarkan status
function getDiscountsByStatus($status) {
    global $koneksi;
    
    $now = date('Y-m-d H:i:s');
    $where_condition = "";
    
    switch ($status) {
        case 'menunggu':
            $where_condition = "start_date > '$now'";
            break;
        case 'active':
            $where_condition = "start_date <= '$now' AND end_date > '$now'";
            break;
        case 'expired':
            $where_condition = "end_date <= '$now'";
            break;
        default:
            return array();
    }
    
    $query = "SELECT d.*, p.name as product_name 
              FROM diskon d 
              LEFT JOIN produk p ON d.produk_id = p.produk_id 
              WHERE $where_condition
              ORDER BY d.start_date ASC";
    
    $result = mysqli_query($koneksi, $query);
    $discounts = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $row['real_status'] = $status;
        $row['countdown'] = getCountdownData($row['start_date'], $row['end_date']);
        $row['countdown_text'] = formatCountdown(
            $row['countdown']['days'],
            $row['countdown']['hours'],
            $row['countdown']['minutes'],
            $row['countdown']['seconds']
        );
        $discounts[] = $row;
    }
    
    return $discounts;
}

// Log aktivitas diskon (opsional untuk debugging)
function logDiscountActivity($action, $discount_id, $product_name) {
    global $koneksi;
    $timestamp = date('Y-m-d H:i:s');
    $log_query = "INSERT INTO discount_logs (action, diskon_id, product_name, timestamp) 
                  VALUES ('$action', '$discount_id', '$product_name', '$timestamp')";
    
    // Hanya jalankan jika tabel discount_logs ada
    $table_check = mysqli_query($koneksi, "SHOW TABLES LIKE 'discount_logs'");
    if (mysqli_num_rows($table_check) > 0) {
        mysqli_query($koneksi, $log_query);
    }
}

// Logging untuk perubahan status (opsional)
// Cek diskon yang baru berakhir
$check_expired = mysqli_query($koneksi, "
    SELECT d.*, p.name as product_name 
    FROM diskon d 
    LEFT JOIN produk p ON d.produk_id = p.produk_id 
    WHERE d.end_date <= '$now' AND d.status != 'expired'
");

while ($expired = mysqli_fetch_assoc($check_expired)) {
    logDiscountActivity('expired', $expired['diskon_id'], $expired['product_name']);
}

// Cek diskon yang baru dimulai
$check_started = mysqli_query($koneksi, "
    SELECT d.*, p.name as product_name 
    FROM diskon d 
    LEFT JOIN produk p ON d.produk_id = p.produk_id 
    WHERE d.start_date <= '$now' AND d.end_date > '$now' AND d.status != 'active'
");

while ($started = mysqli_fetch_assoc($check_started)) {
    logDiscountActivity('started', $started['diskon_id'], $started['product_name']);
}

// Cek diskon yang masih menunggu
$check_pending = mysqli_query($koneksi, "
    SELECT d.*, p.name as product_name 
    FROM diskon d 
    LEFT JOIN produk p ON d.produk_id = p.produk_id 
    WHERE d.start_date > '$now' AND d.status != 'menunggu'
");

while ($pending = mysqli_fetch_assoc($check_pending)) {
    logDiscountActivity('pending', $pending['diskon_id'], $pending['product_name']);
}

?>