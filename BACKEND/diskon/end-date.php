<?php
include '../../koneksi.php';

$now = date('Y-m-d H:i:s');

// Update status menjadi "expired" jika tanggal berakhir sudah lewat
$query_expired = "UPDATE diskon SET status = 'expired' WHERE end_date < '$now' AND status = 'active'";
mysqli_query($koneksi, $query_expired);

// Update status menjadi "active" jika tanggal mulai sudah tiba dan belum berakhir
$query_activate = "UPDATE diskon SET status = 'active' WHERE start_date <= '$now' AND end_date > '$now' AND status IN ('expired', 'active')";
mysqli_query($koneksi, $query_activate);

// Fungsi untuk mendapatkan status diskon real-time
function getDiscountStatus($start_date, $end_date, $current_status) {
    $now = date('Y-m-d H:i:s');
    
    if ($now < $start_date) {
        return 'expired'; // Belum dimulai (menggunakan expired sebagai pending)
    } elseif ($now >= $start_date && $now <= $end_date) {
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
        $data['days'] = $interval->days;
        $data['hours'] = $interval->h;
        $data['minutes'] = $interval->i;
        $data['seconds'] = $interval->s;
        $data['total_seconds'] = ($interval->days * 24 * 60 * 60) + ($interval->h * 60 * 60) + ($interval->i * 60) + $interval->s;
    } elseif ($now >= $start && $now <= $end) {
        // Countdown ke akhir diskon
        $interval = $now->diff($end);
        $data['type'] = 'ends_in';
        $data['message'] = 'Berakhir dalam';
        $data['days'] = $interval->days;
        $data['hours'] = $interval->h;
        $data['minutes'] = $interval->i;
        $data['seconds'] = $interval->s;
        $data['total_seconds'] = ($interval->days * 24 * 60 * 60) + ($interval->h * 60 * 60) + ($interval->i * 60) + $interval->s;
    } else {
        // Diskon sudah berakhir
        $data['type'] = 'expired';
        $data['message'] = 'Sudah berakhir';
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
    
    return implode(', ', $parts);
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

// Cek dan update diskon yang baru saja berakhir untuk logging
$check_expired = mysqli_query($koneksi, "
    SELECT d.*, p.name as product_name 
    FROM diskon d 
    JOIN produk p ON d.produk_id = p.produk_id 
    WHERE d.end_date < '$now' AND d.status = 'active'
");

while ($expired = mysqli_fetch_assoc($check_expired)) {
    logDiscountActivity('expired', $expired['diskon_id'], $expired['product_name']);
}

// Cek dan update diskon yang baru saja dimulai untuk logging
$check_started = mysqli_query($koneksi, "
    SELECT d.*, p.name as product_name 
    FROM diskon d 
    JOIN produk p ON d.produk_id = p.produk_id 
    WHERE d.start_date <= '$now' AND d.end_date > '$now' AND d.status = 'pending'
");

while ($started = mysqli_fetch_assoc($check_started)) {
    logDiscountActivity('started', $started['diskon_id'], $started['product_name']);
}
?>