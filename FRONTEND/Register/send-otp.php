<?php
session_start();

// Ambil nomor dari form
$nomor = $_POST['phone'];

// Validasi format nomor Indonesia
if (!preg_match('/^62[0-9]{9,}$/', $nomor)) {
    $error = urlencode("Nomor tidak valid. Gunakan format 628xxx...");
    header("Location: register-number.php?error=$error");
    exit();
}


$_SESSION['phone'] = $nomor;

// Generate OTP nya
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;

$pesan = "Kode OTP Anda adalah: $otp\nJangan bagikan kode ini ke siapa pun.";

$token = "3FzSTETdbWe7WESA0vNRmTjaKZfJ6QdAm2gPeRD4eEa4fom61QHhMaB";
$secret = "BmOxPTMc";
$auth_header = "$token.$secret";

$data = [
    'phone' => $nomor,
    'message' => $pesan,
];

//  Wablas lokalpride
$curl = curl_init();
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    "Authorization: $auth_header",
]);
curl_setopt($curl, CURLOPT_URL, "https://kirim.pesanapi.com/api/v2/send-message");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

$response = curl_exec($curl);
curl_close($curl);


header("Location: register-otp.php");
exit();
