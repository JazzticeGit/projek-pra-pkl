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
            <p>Selamat datang di web agesa shop</p>

            <!-- Tampilkan pesan error jika ada -->
            <?php if (isset($error_message)): ?>
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
            <p class="login-text">don't have an account? <a href="../FRONTEND/Register/register.php">Register</a></p>
        </div>
    </div>
</body>
</html>