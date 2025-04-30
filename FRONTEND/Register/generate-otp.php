<?php
session_start();

$nomor = $_POST['phone'];

// Validasi nomor
if (!preg_match('/^62[0-9]{9,}$/', $nomor)) {
    $error = urlencode("Nomor tidak valid. Gunakan format 628xxx...");
    header("Location: register-number.php?error=$error");
    exit();
}

$_SESSION['phone'] = $nomor;
$_SESSION['otp'] = rand(100000, 999999); // Generate OTP

header("Location: register-otp.php");
exit();
