<?php
// Simulasikan data user dari database
$user = [
    'id' => 1,
    'username' => 'johndoe',
    'email' => 'john@example.com',
    'birth' => '1990-05-15',
    'tgl_daftar' => '2023-01-10',
    'phone' => '081234567890',
    'created_at' => '2023-01-10 10:30:00',
    'updated_at' => '2023-06-03 15:45:00',
    'icon' => 'profile.jpg'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile Overlay</title>
    <link rel="stylesheet" href="../STYLESHEET/profile.css">
</head>
<body>
   
    <!-- Overlay Structure -->
    <div id="userOverlay" class="overlay">
        <div class="overlay-content">
            <div class="user-header">
                <div class="user-avatar">
                    <img src="<?php echo htmlspecialchars($user['icon']); ?>" alt="User Avatar" onerror="this.src='default-avatar.jpg'">
                </div>
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <span class="close-btn">&times;</span>
            </div>
            
            <div class="user-details">
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tanggal Lahir:</span>
                    <span class="detail-value"><?php echo date('d F Y', strtotime($user['birth'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Telepon:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['phone']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Bergabung:</span>
                    <span class="detail-value"><?php echo date('d F Y', strtotime($user['tgl_daftar'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Terakhir Diupdate:</span>
                    <span class="detail-value"><?php echo date('d F Y H:i', strtotime($user['updated_at'])); ?></span>
                </div>
            </div>
            
            <div class="user-actions">
                <button class="action-btn cart-btn">Keranjang Saya</button>
                <button class="action-btn logout-btn">Logout</button>
            </div>
        </div>
    </div>

    <script src="../javascript/profil.js"></script>
</body>
</html>