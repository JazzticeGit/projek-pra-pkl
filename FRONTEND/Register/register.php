<?php
// Pastikan koneksi database sudah di-include
include '../../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil email dari form
    $email = $_POST['email'];

    // Cek apakah email sudah digunakan
    $check = mysqli_query($koneksi, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $error_message = "Email sudah terdaftar. Silakan gunakan email lain.";
    } else {
        // Jika email belum terdaftar, lanjutkan ke register-number.php dengan email sebagai parameter GET
        header("Location: register-number.php?email=$email");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="../../STYLESHEET/register-style.css">
</head>
<body>
    <div class="flex-container">
        <div class="register-box">
            <h2>Register</h2>
            <p>Please create your account</p>

            <!-- Tampilkan pesan error jika ada -->
            <?php if (isset($error_message)): ?>
                <p style="color: red;"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="email" name="email" placeholder="Enter your email" required>
                <div class="buttons">
                    <a href="https://accounts.google.com/o/oauth2/auth" class="google-btn">
                        <img src="../../image/gambar.png" alt="Google">
                    </a>
                    <button type="submit" class="enter-btn">Enter</button>
                </div>
            </form>
            <p class="login-text">Have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</body>
</html>
