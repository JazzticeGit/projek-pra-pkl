<?php
session_start();

if ($_POST['otp_input'] == $_SESSION['otp']) {
    // echo "<h2>✅ OTP valid. Registrasi berhasil!</h2>";
    header("Location: register-pw.php?otp=berhasil");
    exit();
    // lanjut ke register-password.php atau simpan ke database
} else {
    $error = urlencode("OTP salah, silakan coba lagi.");
    header("Location: register-otp.php?error=$error");
    exit();
}
