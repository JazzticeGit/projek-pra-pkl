<?php
include '../../koneksi.php';
include 'end-date.php'; // perbaiki komentar

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
        echo "<script>alert('Diskon berhasil ditambahkan!'); window.location.href='index.php';</script>";
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
    <style>
        .preview-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: none;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .preview-card.show {
            display: flex;
        }
        .preview-card img {
            max-width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }
        .preview-card h3, .preview-card p {
            margin: 8px 0;
            text-align: center;
        }
        del {
            color: #888;
        }
    </style>
    <script>
    function updateHarga() {
        const select = document.querySelector('select[name="produk_id"]');
        const selected = select.options[select.selectedIndex];
        const harga = parseFloat(selected.getAttribute('data-harga')) || 0;
        const gambar = selected.getAttribute('data-image');
        const namaProduk = selected.textContent;
        const diskon = parseFloat(document.querySelector('input[name="persen_diskon"]').value || 0);

        const hargaDiskon = harga - (harga * diskon / 100);

        if (harga > 0) {
            document.querySelector('.preview-card').classList.add('show');
            document.getElementById('preview-nama').innerText = namaProduk;
            document.getElementById('preview-gambar').src = '../../' + gambar;
            document.getElementById('preview-harga-asli').innerHTML = '<del>Rp ' + harga.toLocaleString('id-ID', {minimumFractionDigits:2}) + '</del>';
            document.getElementById('preview-harga-diskon').innerHTML = '<strong style="color:red;">Rp ' + hargaDiskon.toLocaleString('id-ID', {minimumFractionDigits:2}) + '</strong> (-' + diskon + '%)';
        } else {
            document.querySelector('.preview-card').classList.remove('show');
        }
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

    <input type="number" name="persen_diskon" placeholder="Persentase Diskon (%)" min="1" max="100" required oninput="updateHarga()">
    
    <label for="start_date">Start Date</label>
    <input type="datetime-local" name="start_date" required>
    
    <label for="end_date">End Date</label>
    <input type="datetime-local" name="end_date" required>

    <button type="submit">Tambahkan Diskon</button>
</form>

<!-- Preview Produk -->
<div class="preview-card">
    <img id="preview-gambar" src="" alt="Gambar Produk">
    <h3 id="preview-nama">-</h3>
    <p id="preview-harga-asli">Harga: -</p>
    <p id="preview-harga-diskon">Diskon: -</p>
</div>

<h2>Produk dengan Diskon Aktif</h2>
<div class="container">
    <?php while($row = mysqli_fetch_assoc($diskon_query)): 
        $harga_asli = $row['harga'];
        $persen = $row['persen_diskon'];
        $harga_diskon = $harga_asli - ($harga_asli * $persen / 100);
    ?>
        <div class="card">
            <img src="../../<?= $row['image'] ?>" alt="<?= htmlspecialchars($row['name']) ?>">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <p><del>Rp <?= number_format($harga_asli, 2, ',', '.') ?></del></p>
            <p style="color:red;"><strong>Rp <?= number_format($harga_diskon, 2, ',', '.') ?></strong> (-<?= $persen ?>%)</p>
            <p>Mulai: <?= date("d-m-Y H:i", strtotime($row['start_date'])) ?></p>
            <p>Berakhir: <?= date("d-m-Y H:i", strtotime($row['end_date'])) ?></p>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
