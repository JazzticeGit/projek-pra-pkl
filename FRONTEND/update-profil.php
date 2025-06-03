<?php
session_start();
require_once '../koneksi.php';

$error = '';
$success = '';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$id = $_SESSION['user_id'];

// Ambil data user dari database
$query = "SELECT * FROM users WHERE id = $id";
$result = mysqli_query($koneksi, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("User tidak ditemukan.");
}

$user = mysqli_fetch_assoc($result);

// Proses ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);

    // Cek apakah ada file diupload
    if (!empty($_FILES['icon']['name'])) {
        $upload_dir = '../image/';
        $filename = 'profile_' . $id . '_' . time() . '.' . pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION);
        $destination = $upload_dir . $filename;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (move_uploaded_file($_FILES['icon']['tmp_name'], $destination)) {
            $icon = 'image/' . $filename;
            $update_query = "UPDATE users SET username = '$username', email = '$email', icon = '$icon' WHERE id = $id";
        } else {
            $error = "Gagal mengunggah foto profil.";
        }
    } else {
        // Jika tidak upload foto
        $update_query = "UPDATE users SET username = '$username', email = '$email' WHERE id = $id";
    }

    // Jalankan query update jika tidak ada error
    if (empty($error) && mysqli_query($koneksi, $update_query)) {
        $success = "Profil berhasil diperbarui.";
        // Refresh data user
        $user['username'] = $username;
        $user['email'] = $email;
        if (!empty($icon)) {
            $user['icon'] = $icon;
        }
    } elseif (empty($error)) {
        $error = "Gagal memperbarui profil.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fafafa;
            padding: 40px;
        }
        .container {
            max-width: 500px;
            background: #fff;
            padding: 25px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .foto img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        label {
            display: block;
            margin-top: 12px;
        }
        input[type="text"], input[type="email"], input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #0284c7;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .alert {
            padding: 10px;
            background-color: #fde047;
            margin-top: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #a7f3d0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Profil</h2>

        <?php if (!empty($error)): ?>
            <div class="alert"><?= $error ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!empty($user['icon'])): ?>
            <div class="foto">
                <img src="../<?= htmlspecialchars($user['icon']) ?>" alt="Foto Profil">
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label for="icon">Foto Profil</label>
            <input type="file" id="icon" name="icon" accept="image/*">

            <button type="submit">Simpan Perubahan</button>
        </form>
    </div>
</body>
</html>
