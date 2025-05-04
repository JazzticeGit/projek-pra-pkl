<?php
include '../../koneksi.php';

$now = date('Y-m-d H:i:s');

// Update status menjadi "inactive" jika tanggal berakhir sudah lewat
$query = "UPDATE diskon SET status = 'inactive' WHERE end_date < '$now' AND status = 'active'";
mysqli_query($koneksi, $query);
?>
