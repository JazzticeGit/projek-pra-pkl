<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputOtp = $_POST['otp'];
    $realOtp = $_SESSION['otp'] ?? '';

    if ($inputOtp == $realOtp) {
        echo "OTP Benar! Login/register berhasil.";
        // Bisa redirect ke dashboard atau halaman sukses
        // header("Location: dashboard.php");
        // exit();
    } else {
        // Kode OTP salah, kembali ke form
        $error = urlencode("Kode OTP salah.");
        header("Location: register-otp.php?error=$error");
        exit();
    }
}
?>
