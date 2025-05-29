<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil Pop-up</title>
  <link rel="stylesheet" href="../STYLESHEET/profile.css">
</head>
<body>

  <!-- Tombol Profil -->
  <div class="profile-button" onclick="togglePopup()">
    <span>N</span>
  </div>

  <!-- Pop-up Profil -->
  <div id="profilePopup" class="popup hidden">
    <div class="popup-header">
      <div class="circle">N</div>
      <h2>nathan</h2>
      <p>Menyinkronkan dan mempersonalisasi Chrome...</p>
      <button class="sync-btn">Aktifkan sinkronisasi</button>
    </div>
    <div class="popup-menu">
      <ul>
        <li>Sandi dan info lainnya</li>
        <li>Kelola Akun Google Anda</li>
        <li>Setelan profil</li>
        <li>Setelan layanan Google</li>
        <li>Logout dari Chrome</li>
        <li>Tampilkan profil Chrome</li>
        <li>Buka Profil Tamu</li>
        <li>Kelola profil Chrome</li>
      </ul>
    </div>
  </div>

  <script src="../../javascript/popup.js"></script>
</body>
</html>