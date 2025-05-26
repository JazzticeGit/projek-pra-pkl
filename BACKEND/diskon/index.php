<?php
include '../../koneksi.php';
include 'end-date.php';

// Proses Tambah 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produk_id = $_POST['produk_id'];
    $persen_diskon = $_POST['persen_diskon'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    // Tentukan status berdasarkan waktu mulai
    $now = date('Y-m-d H:i:s');
    $status = ($start_date <= $now) ? 'active' : 'expired'; // Menggunakan 'expired' untuk pending status

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

// Ambil produk dengan diskon (semua status)
$diskon_query = mysqli_query($koneksi, "
    SELECT p.*, d.diskon_id, d.persen_diskon, d.start_date, d.end_date, d.status
    FROM produk p 
    INNER JOIN diskon d ON p.produk_id = d.produk_id 
    ORDER BY d.start_date DESC
");
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
        
        .container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .card img {
            width: 100%;
            height: 160px;
            object-fit: contain;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .card h3 {
            margin: 10px 0;
            font-size: 18px;
            color: #333;
            font-weight: 600;
        }
        
        .card p {
            margin: 8px 0;
            font-size: 14px;
        }
        
        .discount-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            z-index: 10;
        }
        
        .badge-active { background: #2ed573; }
        .badge-expired { background: #ffa502; }
        .badge-waiting { background: #ff4757; }
        
        .countdown-timer {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px;
            border-radius: 8px;
            margin: 10px 0;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
        }
        
        .countdown-timer.ending-soon {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .status-active { background: #2ed573; }
        .status-expired { background: #ffa502; }
        .status-waiting { background: #ff4757; }
        
        .section-divider {
            margin: 40px 0;
            border-bottom: 2px solid #eee;
        }
        
        .section-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #007bff;
            display: inline-block;
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            padding: 10px 20px;
            background: #f8f9fa;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .filter-tab.active {
            background: #007bff;
            color: white;
        }
        
        .filter-tab:hover {
            background: #e9ecef;
        }
        
        .filter-tab.active:hover {
            background: #0056b3;
        }
        
        .auto-refresh-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 1000;
            display: none;
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
    
    // Auto refresh untuk update status real-time
    function startAutoRefresh() {
        setInterval(function() {
            updateCountdowns();
            
            // Refresh halaman setiap 5 menit untuk sinkronisasi dengan database
            setTimeout(function() {
                document.querySelector('.auto-refresh-indicator').style.display = 'block';
                location.reload();
            }, 300000); // 5 menit
        }, 1000); // Update setiap detik
    }
    
    function updateCountdowns() {
        const countdownElements = document.querySelectorAll('.countdown-timer');
        countdownElements.forEach(function(element) {
            const endTime = new Date(element.getAttribute('data-end-time')).getTime();
            const startTime = new Date(element.getAttribute('data-start-time')).getTime();
            const now = new Date().getTime();
            
            let targetTime, message, type;
            
            if (now < startTime) {
                targetTime = startTime;
                message = 'Dimulai dalam: ';
                type = 'pending';
            } else if (now >= startTime && now <= endTime) {
                targetTime = endTime;
                message = 'Berakhir dalam: ';
                type = 'active';
            } else {
                element.innerHTML = '<span style="color: #ff4757;">Sudah berakhir</span>';
                element.className = 'countdown-timer';
                return;
            }
            
            const distance = targetTime - now;
            
            if (distance > 0) {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                let timeString = '';
                if (days > 0) timeString += days + 'h ';
                if (hours > 0) timeString += hours + 'j ';
                if (minutes > 0) timeString += minutes + 'm ';
                if (days === 0) timeString += seconds + 'd';
                
                element.innerHTML = message + timeString;
                
                // Tambahkan efek jika mendekati berakhir (kurang dari 1 jam)
                if (type === 'active' && distance < 3600000) {
                    element.className = 'countdown-timer ending-soon';
                } else {
                    element.className = 'countdown-timer';
                }
            } else {
                element.innerHTML = '<span style="color: #ff4757;">Waktu habis</span>';
                // Auto refresh halaman jika ada perubahan status
                setTimeout(() => location.reload(), 2000);
            }
        });
    }
    
    function filterCards(status) {
        const cards = document.querySelectorAll('.card');
        const tabs = document.querySelectorAll('.filter-tab');
        
        // Update active tab
        tabs.forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');
        
        // Filter cards
        cards.forEach(card => {
            const cardStatus = card.getAttribute('data-status');
            if (status === 'all' || cardStatus === status) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Mulai auto refresh saat halaman dimuat
    window.onload = function() {
        startAutoRefresh();
        updateCountdowns();
    };
    </script>
</head>
<body>

<div class="auto-refresh-indicator">
    Auto-refreshing...
</div>

<h1>Tambah Diskon Produk</h1>

<form method="POST" action="">
    <label for="produk_id">Pilih Produk:</label>
    <select name="produk_id" onchange="updateHarga()" required>
        <option value="">-- Pilih Produk --</option>
        <?php 
        // Reset pointer untuk query produk
        $produk_query = mysqli_query($koneksi, "SELECT * FROM produk");
        while($row = mysqli_fetch_assoc($produk_query)): ?>
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

<div class="preview-card">
    <img id="preview-gambar" src="" alt="Gambar Produk">
    <h3 id="preview-nama">-</h3>
    <p id="preview-harga-asli">Harga: -</p>
    <p id="preview-harga-diskon">Diskon: -</p>
</div>

<div class="section-divider"></div>

<h2 class="section-title">Manajemen Diskon Produk</h2>

<div class="filter-tabs">
    <button class="filter-tab active" onclick="filterCards('all')">Semua</button>
    <button class="filter-tab" onclick="filterCards('active')">Aktif</button>
    <button class="filter-tab" onclick="filterCards('expired')">Menunggu/Berakhir</button>
</div>

<?php if (mysqli_num_rows($diskon_query) > 0): ?>
<div class="container">
    <?php while($row = mysqli_fetch_assoc($diskon_query)): 
        $harga_asli = $row['harga'];
        $persen = $row['persen_diskon'];
        $harga_diskon = $harga_asli - ($harga_asli * $persen / 100);
        
        // Dapatkan status real-time berdasarkan database enum
        $now = date('Y-m-d H:i:s');
        if ($now < $row['start_date']) {
            $real_status = 'waiting'; // Belum dimulai
            $display_status = 'MENUNGGU';
            $status_color = '#ffa502';
        } elseif ($now >= $row['start_date'] && $now <= $row['end_date']) {
            $real_status = 'active'; // Sedang berlangsung
            $display_status = 'AKTIF';
            $status_color = '#2ed573';
        } else {
            $real_status = 'expired'; // Sudah berakhir
            $display_status = 'BERAKHIR';
            $status_color = '#ff4757';
        }
        
        $countdown_data = getCountdownData($row['start_date'], $row['end_date']);
        
        // Tentukan class untuk styling
        $badge_class = 'badge-' . $real_status;
        $status_class = 'status-' . $real_status;
        
        $opacity_style = ($real_status === 'expired') ? 'style="opacity: 0.7;"' : '';
    ?>
        <div class="card" data-status="<?= $real_status ?>" <?= $opacity_style ?>>
            <div class="discount-badge <?= $badge_class ?>">
                <?php 
                if ($real_status === 'active') {
                    echo 'DISKON ' . $persen . '%';
                } else {
                    echo $display_status;
                }
                ?>
            </div>
            
            <img src="../../<?= $row['image'] ?>" alt="<?= htmlspecialchars($row['name']) ?>">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            
            <?php if ($real_status === 'active'): ?>
                <p><del>Rp <?= number_format($harga_asli, 2, ',', '.') ?></del></p>
                <p style="color:red;"><strong>Rp <?= number_format($harga_diskon, 2, ',', '.') ?></strong> (-<?= $persen ?>%)</p>
            <?php else: ?>
                <p><strong>Rp <?= number_format($harga_asli, 2, ',', '.') ?></strong></p>
                <p style="color:#666;">Diskon: <?= $persen ?>%</p>
            <?php endif; ?>
            
            <p><strong>Mulai:</strong> <?= date("d-m-Y H:i", strtotime($row['start_date'])) ?></p>
            <p><strong>Berakhir:</strong> <?= date("d-m-Y H:i", strtotime($row['end_date'])) ?></p>
            
            <?php if ($countdown_data['type'] !== 'expired'): ?>
                <div class="countdown-timer" 
                     data-start-time="<?= $row['start_date'] ?>" 
                     data-end-time="<?= $row['end_date'] ?>">
                    <?= $countdown_data['message'] ?>: 
                    <?= formatCountdown($countdown_data['days'], $countdown_data['hours'], $countdown_data['minutes'], $countdown_data['seconds']) ?>
                </div>
            <?php endif; ?>
            
            <p style="font-weight: bold; font-size: 13px;">
                <span class="status-indicator <?= $status_class ?>"></span>
                <span style="color: <?= $status_color ?>;"><?= $display_status ?></span>
            </p>
        </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
<div style="text-align: center; padding: 40px; color: #666;">
    <h3>Belum ada produk dengan diskon</h3>
    <p>Tambahkan diskon untuk produk menggunakan form di atas.</p>
</div>
<?php endif; ?>

</body>
</html>