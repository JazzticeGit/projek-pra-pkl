<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['phone']) || !isset($_SESSION['password'])) {
    header("Location: register.php?error=" . urlencode("Data belum lengkap."));
    exit;
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
      <form action="proses-register.php" method="POST">
          <input type="text" name="username" placeholder="Enter your name" required>
          <input type="date" name="birth" required>
          <button type="submit" class="enter-btn">Enter</button>
      </form>

      <p class="login-text">Have an account? <a href="login.php">Login</a></p>
    </div>
  </div>
</body>
</html>
