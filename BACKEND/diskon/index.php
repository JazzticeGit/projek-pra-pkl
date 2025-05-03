<?php
include '../../koneksi.php';

// Proses Tambah Diskon
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produk_id = $_POST['produk_id'];
    $persen_diskon = $_POST['persen_diskon'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = 'active';

    $query = "INSERT INTO diskon (produk_id, persen_diskon, start_date, end_date, status)
              VALUES ('$produk_id', '$persen_diskon', '$start_date', '$end_date', '$status')";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Diskon berhasil ditambahkan!'); window.location.href='diskon.php';</script>";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}

// Ambil semua produk
$produk_query = mysqli_query($koneksi, "SELECT * FROM produk");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Diskon Produk</title>
    <link rel="stylesheet" href="../../STYLESHEET/dashboard.css">
    <script>
    function updateHarga() {
        const select = document.querySelector('select[name="produk_id"]');
        const selected = select.options[select.selectedIndex];
        const harga = parseFloat(selected.getAttribute('data-harga'));
        const gambar = selected.getAttribute('data-image');
        const diskon = parseFloat(document.querySelector('input[name="persen_diskon"]').value || 0);

        const hargaDiskon = harga - (harga * diskon / 100);

        document.getElementById('preview-harga-asli').innerText = 'Rp ' + harga.toLocaleString('id-ID', {minimumFractionDigits:2});
        document.getElementById('preview-gambar').src = '../../' + gambar;
        document.getElementById('preview-harga-diskon').innerText = 'Rp ' + hargaDiskon.toLocaleString('id-ID', {minimumFractionDigits:2});
    }
    </script>
</head>
<body>

<h1>Tambah Diskon Produk</h1>

<form method="POST" action="">
    <label for="produk_id">Pilih Produk:</label>
    <select name="produk_id" onchange="updateHarga()" required>
        <option value="">-- Pilih Produk --</option>
        <?php while($row = mysqli_fetch_assoc($produk_query)): ?>
            <option value="<?= $row['produk_id'] ?>" 
                data-harga="<?= $row['harga'] ?>" 
                data-image="<?= $row['image'] ?>">
                <?= htmlspecialchars($row['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <div class="preview-box">
        <img id="preview-gambar" src="" alt="Gambar Produk" style="max-width:150px; margin-top:10px;">
        <p id="preview-harga-asli">Harga: -</p>
    </div>

    <input type="number" name="persen_diskon" placeholder="Persentase Diskon (%)" min="1" max="100" required oninput="updateHarga()">
    <input type="datetime-local" name="start_date" required>
    <input type="datetime-local" name="end_date" required>

    <p id="preview-harga-diskon">Harga Setelah Diskon: -</p>

    <button type="submit">Tambahkan Diskon</button>
</form>

</body>
</html>
 