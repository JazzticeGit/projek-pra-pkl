<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_otp = $_POST['otp'];
    if ($input_otp == $_SESSION['otp']) {
        // Redirect ke halaman password
        header("Location: register-pw.php");
        exit();
    } else {
        $error = "OTP salah, coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi OTP</title>
    <link rel="stylesheet" href="../../STYLESHEET/register-style.css">
</head>
<body>
    <div class="flex-container">
        <div class="register-box">
            <h2>Verifikasi OTP</h2>
            <p>Masukkan kode OTP yang telah dikirim ke WhatsApp Anda</p>
            <form method="POST">
                <input type="number" name="otp" id="otp" class="otp" placeholder="Masukkan OTP" required>
                <p id="error-message" style="color: #ff7b7b; font-size: 13px; margin-bottom: 10px;">
                    <?php if (isset($error)) echo $error; ?>
                </p>
                <div class="enter-btn_1">
                    <button type="submit">Verifikasi OTP</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>