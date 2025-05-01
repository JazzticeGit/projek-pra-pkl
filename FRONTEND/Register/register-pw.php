<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['email']) || !isset($_POST['phone'])) {
    header("Location: register-number.php?error=" . urlencode("Silakan isi email dan nomor WhatsApp terlebih dahulu."));
    exit;
}

$email = $_POST['email'];
$phone = $_POST['phone'];
$password = "";
$error = "";

if (isset($_POST['password'])) {
    $password = $_POST['password'];

    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $_SESSION['email'] = $email;
        $_SESSION['phone'] = $phone;
        $_SESSION['password'] = $password;
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
      <form method="POST">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
        <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
        <input type="password" name="password" placeholder="Enter your password" required>
        <p style="color: red;"><?php echo $error; ?></p>
        <button type="submit" class="enter_btn_1">Enter</button>
      </form>
    </div>
  </div>
</body>
</html>
