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
      <form action="register-next.php" method="POST">
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
 