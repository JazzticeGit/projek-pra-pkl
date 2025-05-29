<?php
include '../../koneksi.php';
include 'end-date.php';

// Proses penambahan diskon
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $produk_id = $_POST['produk_id'];
    $persen_diskon = $_POST['persen_diskon'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $now = date('Y-m-d H:i:s');

    // Validasi status diskon
    if ($start_date > $now) {
        $status = 'pending';
    } elseif ($start_date <= $now && $end_date >= $now) {
        $status = 'active';
    } else {
        $status = 'expired';
    }

    // Simpan ke database
    $stmt = $koneksi->prepare("INSERT INTO diskon (produk_id, persen_diskon, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $produk_id, $persen_diskon, $start_date, $end_date, $status);
    $stmt->execute();

    // Pesan sukses untuk tampilkan di halaman yang sama
    $pesan_sukses = "Diskon berhasil ditambahkan!";
}

// Update status diskon otomatis
$now = date('Y-m-d H:i:s');
mysqli_query($koneksi, "UPDATE diskon SET status = 'expired' WHERE end_date < '$now' AND status != 'expired'");
mysqli_query($koneksi, "UPDATE diskon SET status = 'active' WHERE start_date <= '$now' AND end_date >= '$now'");
mysqli_query($koneksi, "UPDATE diskon SET status = 'pending' WHERE start_date > '$now'");

// Ambil data produk dan diskon (ambil ulang agar list terbaru)
$produk_query = mysqli_query($koneksi, "SELECT * FROM produk");
$diskon_query = mysqli_query($koneksi, "
    SELECT d.*, p.name as nama_produk, p.image, p.harga 
    FROM diskon d 
    JOIN produk p ON d.produk_id = p.produk_id 
    ORDER BY d.start_date DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Diskon Produk</title>
    <link rel="stylesheet" href="../../STYLESHEET/diskon-style.css">
    <script src="../../JS/diskon-script.js" defer></script>
</head>
<body>
    <div class="container">
        <h1>Tambah Diskon Produk</h1>

        <?php if (isset($pesan_sukses)): ?>
            <div class="alert-success" style="background-color:#d4edda; color:#155724; padding:10px; margin-bottom:15px; border-radius:5px;">
                <?= htmlspecialchars($pesan_sukses) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label for="produk_id">Pilih Produk:</label>
            <select name="produk_id" id="produkSelect" onchange="previewProduk()" required>
                <option value="" disabled selected>-- Pilih Produk --</option>
                <?php while($row = mysqli_fetch_assoc($produk_query)): ?>
                    <option value="<?= $row['produk_id'] ?>"
                        data-harga="<?= $row['harga'] ?>"
                        data-image="<?= $row['image'] ?>"
                        data-nama="<?= $row['name'] ?>">
                        <?= $row['name'] ?>
                    </option>
                <?php endwhile; ?>
            </select><br>

            <label for="persen_diskon">Persen Diskon (%):</label>
            <input type="number" name="persen_diskon" min="1" max="100" required oninput="updateHarga()"><br>

            <label for="start_date">Tanggal Mulai:</label>
            <input type="datetime-local" name="start_date" required><br>

            <label for="end_date">Tanggal Berakhir:</label>
            <input type="datetime-local" name="end_date" required><br>

            <button type="submit">Tambah Diskon</button>
        </form>

        <div class="preview-card">
            <img id="preview-gambar" src="" alt="Preview Gambar" style="max-width:150px;">
            <h3 id="preview-nama"></h3>
            <p id="preview-harga-asli"></p>
            <p id="preview-harga-diskon" style="color:red; font-weight:bold;"></p>
        </div>

        <h2 class="section-title">Daftar Produk Diskon</h2>

        <div class="filter-tabs">
            <button class="filter-tab active" onclick="filterCards('all')">Semua</button>
            <button class="filter-tab" onclick="filterCards('active')">Aktif</button>
            <button class="filter-tab" onclick="filterCards('pending')">Menunggu</button>
            <button class="filter-tab" onclick="filterCards('expired')">Berakhir</button>
        </div>

        <div class="container diskon-list">
            <?php while($row = mysqli_fetch_assoc($diskon_query)): 
                $status = $row['status'];
                $badgeClass = $status === 'active' ? 'badge-active' : ($status === 'pending' ? 'badge-waiting' : 'badge-expired');
                $hargaDiskon = $row['harga'] - ($row['harga'] * $row['persen_diskon'] / 100);
            ?>
            <div class="card" data-status="<?= $status ?>">
                <span class="discount-badge <?= $badgeClass ?>"><?= strtoupper($status) ?></span>
                <img src="../../<?= $row['image'] ?>" alt="<?= $row['nama_produk'] ?>" style="max-width:150px;">
                <h3><?= $row['nama_produk'] ?></h3>
                <p><del>Rp <?= number_format($row['harga'], 2, ',', '.') ?></del></p>
                <p><strong style="color:red;">Rp <?= number_format($hargaDiskon, 2, ',', '.') ?></strong> (-<?= $row['persen_diskon'] ?>%)</p>
                <div class="countdown-timer" 
                     data-start-time="<?= $row['start_date'] ?>" 
                     data-end-time="<?= $row['end_date'] ?>"></div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
    // Contoh fungsi previewProduk dan updateHarga minimal
    function previewProduk() {
        const select = document.getElementById('produkSelect');
        const selected = select.options[select.selectedIndex];
        const nama = selected.getAttribute('data-nama') || '';
        const harga = parseFloat(selected.getAttribute('data-harga')) || 0;
        const image = selected.getAttribute('data-image') || '';

        document.getElementById('preview-nama').textContent = nama;
        document.getElementById('preview-gambar').src = "../../" + image;
        document.getElementById('preview-harga-asli').textContent = `Harga Asli: Rp ${harga.toLocaleString('id-ID')}`;

        // Reset harga diskon saat produk berganti
        document.getElementById('preview-harga-diskon').textContent = '';
    }

    function updateHarga() {
        const persen = parseFloat(document.querySelector('input[name="persen_diskon"]').value) || 0;
        const hargaText = document.getElementById('preview-harga-asli').textContent;
        if (!hargaText) return;

        const harga = Number(hargaText.replace(/[^0-9]/g, ''));
        if (persen > 0 && harga > 0) {
            const diskon = harga * persen / 100;
            const hargaDiskon = harga - diskon;
            document.getElementById('preview-harga-diskon').textContent = `Harga Diskon: Rp ${hargaDiskon.toLocaleString('id-ID')}`;
        } else {
            document.getElementById('preview-harga-diskon').textContent = '';
        }
    }

    // Filter kartu diskon berdasarkan status
    function filterCards(status) {
        const tabs = document.querySelectorAll('.filter-tab');
        tabs.forEach(tab => tab.classList.remove('active'));

        event.target.classList.add('active');

        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            if (status === 'all' || card.dataset.status === status) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    </script>
</body>
</html>
