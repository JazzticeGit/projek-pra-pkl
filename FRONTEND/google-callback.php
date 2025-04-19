<?php
require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('758168287383-hi0p2m4kdp0o8k153vdcfh4do3h8mf2r.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/google-callback.php');

if (isset($_GET['code'])) {
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  if (!isset($token['error'])) {
    $client->setAccessToken($token['access_token']);

    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();

    $email = $google_account_info->email;
    $name = $google_account_info->name;
    $picture = $google_account_info->picture;

    // Login berhasil, simpan data ke session atau database
    session_start();
    $_SESSION['email'] = $email;
    $_SESSION['name'] = $name;
    $_SESSION['picture'] = $picture;

    // Redirect ke halaman setelah login
    header('Location: dashboard.php');
    exit;
  } else {
    echo "Login error: " . $token['error'];
  }
} else {
  echo "No code provided!";
}
