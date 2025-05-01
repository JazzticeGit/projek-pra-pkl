<?php
session_start();

if (isset($_GET['error'])) {
    $error_message = urldecode($_GET['error']);
}

if (isset($_GET['email'])) {
    $email = $_GET['email'];
} else {
    // Jika email tidak ada, redirect ke halaman sebelumnya atau tampilkan pesan error
    header("Location: register.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Masukkan Nomor WhatsApp</title>
    <link rel="stylesheet" href="../../STYLESHEET/register-style.css">
</head>
<body>
    <div class="flex-container">
        <div class="register-box">
            <h2>Masukkan Nomor WhatsApp</h2>
            
            <!-- Tampilkan error jika ada -->
            <?php if (isset($error_message)): ?>
                <p style="color: red;"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form method="POST" action="register-pw.php">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="tel" name="phone" placeholder="Masukkan nomor WhatsApp (08...)" required>
                <button type="submit">Enter</button>
            </form>
        </div>
    </div>
</body>
</html>
