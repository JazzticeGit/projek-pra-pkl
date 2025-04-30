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
        <!-- <input type="email" name="email" placeholder="Enter your email" required> -->
        <input type="text" name="email" placeholder="Enter your name" required>
        <input type="date" name="email" placeholder="Enter your birth" required>
        <div class="buttons">
          <div class="enter-btn"><a href="../FRONTEND/index.php">Enter</a></div>
        </div>
      </form>
      <p class="login-text">Have an account? <a href="login.php">Login</a></p>
    </div>
  </div>
</body>
</html>
