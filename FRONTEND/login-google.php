<?php
require_once 'vendor/autoload.php'; // Composer autoload

$client = new Google_Client();
$client->setClientId('758168287383-hi0p2m4kdp0o8k153vdcfh4do3h8mf2r.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/google-callback.php');
$client->addScope('email');
$client->addScope('profile');

$login_url = $client->createAuthUrl();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login with Google</title>
</head>
<body>
  <a href="<?= htmlspecialchars($login_url) ?>">
    <img src="image/gambar.png" alt="Login with Google">
  </a>
</body>
</html>
