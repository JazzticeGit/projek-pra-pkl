<?php
include '../../koneksi.php'; 
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "ID produk tidak ditemukan.";
    exit;
}

$query = mysqli_query($koneksi, "SELECT * FROM produk WHERE produk_id = $id");
$produk = mysqli_fetch_assoc($query);

if (!$produk) {
    echo "Produk tidak ditemukan.";
    exit;
}

$kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $stok = $_POST['stok'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $best_seller = isset($_POST['best_seller']) ? 1 : 0;
    $new_arrival = isset($_POST['new_arrival']) ? 1 : 0;
    $size = $_POST['size'];
    // $color = $_POST['color'];
    $id_kategori = $_POST['id_kategori'];

    if (!empty($_FILES['image']['name'])) {
        $upload_dir = "../../image/";
        $file_name = basename($_FILES["image"]["name"]);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = "image/" . $file_name;
        } else {
            echo "Upload gambar gagal.";
            exit;
        }
    } else {
        $image = $produk['image']; 
    }

    $update = mysqli_query($koneksi, "UPDATE produk SET 
        name = '$name',
        stok = '$stok',
        deskripsi = '$deskripsi',
        harga = '$harga',
        image = '$image',
        best_seller = '$best_seller',
        new_arrival = '$new_arrival',
        size = '$size',
        -- color = '$color',
        id_kategori = '$id_kategori'
        WHERE produk_id = $id
    ");

    if ($update) {
        echo "<script>alert('Produk berhasil diupdate!'); window.location.href='index-produk.php';</script>";
    } else {
        echo "Gagal update: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="../../STYLESHEET/dashboard.css">
</head>
<body>

<h1>Edit Produk</h1>
<form action="" method="post" enctype="multipart/form-data">
    <input type="text" name="name" value="<?= htmlspecialchars($produk['name']) ?>" required>
    <input type="number" name="stok" value="<?= $produk['stok'] ?>" required>
    <textarea name="deskripsi" required><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
    <input type="number" name="harga" step="0.01" value="<?= $produk['harga'] ?>" required>
    <img src="../../<?= $produk['image'] ?>" alt="Gambar Produk" width="100"><br>
    <label>Ganti Gambar (jika perlu):</label>
    <input type="file" name="image" accept="image/*">
    <label><input type="checkbox" name="best_seller" <?= $produk['best_seller'] ? 'checked' : '' ?>> Best Seller</label>
    <label><input type="checkbox" name="new_arrival" <?= $produk['new_arrival'] ? 'checked' : '' ?>> New Arrival</label>
    <input type="text" name="size" value="<?= htmlspecialchars($produk['size']) ?>" placeholder="Ukuran">
    <!-- <input type="text" name="color" value="<?= htmlspecialchars($produk['color']) ?>" required> -->
    <select name="id_kategori" required>
        <option value="">Pilih Kategori</option>
        <?php while($kategori = mysqli_fetch_assoc($kategori_query)): ?>
            <option value="<?= $kategori['id'] ?>" <?= $produk['id_kategori'] == $kategori['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($kategori['jenis_produk']) ?>
            </option>
        <?php endwhile; ?>
    </select>
    <button type="submit">Simpan</button>
</form>

</body>
</html>
