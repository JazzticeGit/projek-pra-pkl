<?php
session_start();

if (!isset($_SESSION['otp'])) {
    echo "OTP tidak ditemukan.";
    exit();
}

$otp = $_SESSION['otp'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>OTP</title>
</head>
<body>
  <h2>Kode OTP</h2>
  <p>Nomor Anda: <?php echo htmlspecialchars($_SESSION['phone']); ?></p>
  <p style="font-size: 20px; color: green;">Kode OTP Anda: <strong><?php echo $otp; ?></strong></p>

  <form action="cek-otp.php" method="POST">
    <input type="text" name="otp_input" placeholder="Masukkan OTP" required>
    <button type="submit">Verifikasi</button>
  </form>

  <?php if (isset($_GET['error'])): ?>
    <p style="color:red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
  <?php endif; ?>
</body>
</html>
