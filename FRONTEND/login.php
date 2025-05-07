<?php
session_start();
include '../koneksi.php';

// Inisialisasi error
$error_message = '';

// Proses login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php"); // Ganti ke halaman utama
            exit;
        } else {
            $error_message = "Password salah.";
        }
    } else {
        $error_message = "Email tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../STYLESHEET/register-style.css">
</head>
<body>
    <div class="flex-container">
        <div class="register-box">
            <h2>Login</h2>
            <p>Selamat datang di web AGESA SHOP</p>

            <!-- Tampilkan pesan error jika ada -->
            <?php if (!empty($error_message)): ?>
                <p style="color: red;"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="email" name="email" placeholder="Enter your email" required>
                <input type="password" name="password" placeholder="Enter your password" required>
                <div class="buttons">
                    <a href="https://accounts.google.com/o/oauth2/auth" class="google-btn">
                        <img src="../image/gambar.png" alt="Google">
                    </a>
                    <button type="submit" class="enter-btn">Enter</button>
                </div>
            </form>
            <p class="login-text">Don't have an account? <a href="../FRONTEND/Register/register.php">Register</a></p>
        </div>
    </div>
</body>
</html>
