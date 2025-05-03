<?php
include '../../koneksi.php'; // Pastikan koneksi.php menginisialisasi $koneksi

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($koneksi, $_POST['name']);
    $stok = $_POST['stok'];
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $harga = $_POST['harga'];
    $best_seller = isset($_POST['best_seller']) ? 1 : 0;
    $new_arrival = isset($_POST['new_arrival']) ? 1 : 0;
    $color = mysqli_real_escape_string($koneksi, $_POST['color']);
    $id_kategori = $_POST['id_kategori'];

    // Upload dan bersihkan nama file gambar
    $upload_dir = "../../image/";
    $original_file_name = $_FILES["image"]["name"];
    $clean_file_name = preg_replace("/[^a-zA-Z0-9\.\-_]/", "_", $original_file_name);
    $target_file = $upload_dir . $clean_file_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image = "image/" . $clean_file_name;

        $query = "INSERT INTO produk (name, stok, deskripsi, harga, image, best_seller, new_arrival, color, id_kategori)
                  VALUES ('$name', '$stok', '$deskripsi', '$harga', '$image', '$best_seller', '$new_arrival', '$color', '$id_kategori')";

        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Produk berhasil ditambahkan!'); window.location.href='index-produk.php';</script>";
        } else {
            echo "Error saat insert: " . mysqli_error($koneksi);
        }
    } else {
        echo "Upload gambar gagal!";
    }
}

// Query tampilkan produk
$query = "SELECT produk.*, kategori.jenis_produk 
          FROM produk 
          JOIN kategori ON produk.id_kategori = kategori.id";
$result = mysqli_query($koneksi, $query);

// Ambil daftar kategori
$kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori");
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Produk</title>
    <link rel="stylesheet" href="../../STYLESHEET/dashboard.css">
</head>
<body>


<h1>Produk</h1> <br><br>
<h2>Tambah Produk Baru</h2>
<form action="" method="post" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Nama Produk" required>
    <input type="number" name="stok" placeholder="Stok" required>
    <textarea name="deskripsi" placeholder="Deskripsi Produk" required></textarea>
    <input type="number" step="0.01" name="harga" placeholder="Harga" required>
    <input type="file" name="image" accept="image/*" required>
    <label><input type="checkbox" name="best_seller"> Best Seller</label>
    <label><input type="checkbox" name="new_arrival"> New Arrival</label>
    <!-- <select name="size" required>
        <option value="">Pilih Ukuran</option>
        <option value="XS">XS</option>
        <option value="S">S</option>
        <option value="M">M</option>
        <option value="L">L</option>
        <option value="XL">XL</option>
        <option value="XXL">XXL</option>
        <option value="3XL">3XL</option>
    </select> -->
    <input type="text" name="color" placeholder="Warna" required>
    <select name="id_kategori" required>
        <option value="">Pilih Kategori</option>
        <?php while($kategori = mysqli_fetch_assoc($kategori_query)): ?>
            <option value="<?= $kategori['id'] ?>"><?= htmlspecialchars($kategori['jenis_produk']) ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit">Tambah Produk</button>

    
</form>

<h2>Daftar Produk</h2>
<div class="container">
    
        <?php while($produk = mysqli_fetch_assoc($result)): ?>
            <div class="card">
            <img src="../../<?= htmlspecialchars($produk['image']) ?>" alt="<?= htmlspecialchars($produk['name']) ?>">
            <h3><?= htmlspecialchars($produk['name']) ?></h3>
            <p>Stok: <?= $produk['stok'] ?></p>
            <p> Rp<?= number_format($produk['harga'], 2, ',', '.') ?></p>
            <p> <?= htmlspecialchars($produk['jenis_produk']) ?></p>
            <p> <?= $produk['size'] ?> 
             <?= htmlspecialchars($produk['color']) ?></p>
            <?php if($produk['best_seller']): ?><p><strong>🔥 Best Seller</strong></p><?php endif; ?>
            <?php if($produk['new_arrival']): ?><p><strong>🆕 New Arrival</strong></p><?php endif; ?>
            <div class="actions">
                <a href="edit.php?id=<?= $produk['produk_id'] ?>" class="btn edit">Edit</a>
                <a href="hapus.php?id=<?= $produk['produk_id'] ?>" class="btn delete" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
            </div>
    </div>
<?php endwhile; ?>

</div>

</body>
</html>