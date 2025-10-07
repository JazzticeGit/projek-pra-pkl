<?php
session_start();
include '../../koneksi.php'; 
include '../session_config.php';

// Validasi admin session
validateAdminSession($koneksi);

if (!isset($_SESSION['email'], $_SESSION['phone'], $_SESSION['password'], $_POST['username'], $_POST['birth'])) {
    die("Data tidak lengkap.");
}

$email = $_SESSION['email'];
$phone = $_SESSION['phone']; 
$password = password_hash($_SESSION['password'], PASSWORD_DEFAULT);
$username = $_POST['username'];
$birth = $_POST['birth'];
$tgl_daftar = date('Y-m-d');

// Simpan ke database
$query = "INSERT INTO users (username, email, phone, password, birth, tgl_daftar) 
          VALUES ('$username', '$email', '$phone', '$password', '$birth', '$tgl_daftar')";

if (mysqli_query($koneksi, $query)) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
} else {
    echo "Gagal menyimpan: " . mysqli_error($koneksi);
}

echo "QUERY: $query<br>";
echo "MYSQL ERROR: " . mysqli_error($koneksi);
?>
