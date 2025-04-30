<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];

    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        // Lanjut ke proses berikutnya atau simpan data
        header("Location: register-data-diri.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Password</title>
  <link rel="stylesheet" href="../../STYLESHEET/register-style.css">
  <script defer src="../../JS/validation.js"></script>
</head>
<body>
  <div class="flex-container">
    <div class="register-box">
      <h2>Register</h2>
      <p>Create your password</p>
      <form method="POST" id="passwordForm">
        <input type="password" name="password" id="password"  class="pw" placeholder="Enter your password" required>
        <p id="error-message" style="color: #ff7b7b; font-size: 13px; margin-bottom: 10px;">
          <?php if (isset($error)) echo $error; ?>
        </p>
        <input type="submit" value="Enter" class="enter_btn_1">
      </form>
    </div>
  </div>
</body>
</html>
