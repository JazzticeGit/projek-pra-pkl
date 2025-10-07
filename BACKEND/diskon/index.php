<?php
include '../FRONTEND/session_config.php';
include '../../koneksi.php';

// Validasi admin session
validateAdminSession($koneksi);
include 'end-date.php';

// Proses penambahan diskon
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $produk_id = $_POST['produk_id'];
    $persen_diskon = $_POST['persen_diskon'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $now = date('Y-m-d H:i:s');

    // Validasi status diskon berdasarkan waktu
    if ($start_date > $now) {
        $status = 'expired'; // Menggunakan 'expired' sebagai pending sesuai database enum
    } elseif ($start_date <= $now && $end_date >= $now) {
        $status = 'active';
    } else {
        $status = 'expired';
    }

    // Simpan ke database
    $stmt = $koneksi->prepare("INSERT INTO diskon (produk_id, persen_diskon, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $produk_id, $persen_diskon, $start_date, $end_date, $status);
    
    if ($stmt->execute()) {
        $diskon_id = $stmt->insert_id;
        
        // Log aktivitas penambahan diskon
        $produk_query = mysqli_query($koneksi, "SELECT name FROM produk WHERE produk_id = $produk_id");
        $produk_data = mysqli_fetch_assoc($produk_query);
        logDiscountActivity('created', $diskon_id, $produk_data['name']);
        
        $pesan_sukses = "Diskon berhasil ditambahkan dengan status: " . ($status == 'active' ? 'Aktif' : 'Menunggu');
    } else {
        $pesan_error = "Gagal menambahkan diskon: " . $stmt->error;
    }
    $stmt->close();
}

// Update status diskon otomatis menggunakan fungsi dari end-date.php
$now = date('Y-m-d H:i:s');

// Update status expired untuk diskon yang sudah berakhir
$expired_query = "UPDATE diskon SET status = 'expired' WHERE end_date < '$now' AND status = 'active'";
mysqli_query($koneksi, $expired_query);

// Update status active untuk diskon yang sudah dimulai dan belum berakhir
$active_query = "UPDATE diskon SET status = 'active' WHERE start_date <= '$now' AND end_date >= '$now' AND status = 'expired'";
mysqli_query($koneksi, $active_query);

// Ambil data produk dan diskon (ambil ulang agar list terbaru)
$produk_query = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY name ASC");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Diskon Produk</title>
    <link rel="stylesheet" href="../../STYLESHEET/diskon-style.css">
    <script src="../../JS/diskon-script.js" defer></script>
    <style>
        .status-indicator {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status-expired {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .realtime-status {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tambah Diskon Produk</h1>

        <?php if (isset($pesan_sukses)): ?>
            <div class="alert-success" style="background-color:#d4edda; color:#155724; padding:10px; margin-bottom:15px; border-radius:5px;">
                ✅ <?= htmlspecialchars($pesan_sukses) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($pesan_error)): ?>
            <div class="alert-error" style="background-color:#f8d7da; color:#721c24; padding:10px; margin-bottom:15px; border-radius:5px;">
                ❌ <?= htmlspecialchars($pesan_error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="produk_id">Pilih Produk:</label>
                <select name="produk_id" id="produkSelect" onchange="previewProduk()" required>
                    <option value="" disabled selected>-- Pilih Produk --</option>
                    <?php while($row = mysqli_fetch_assoc($produk_query)): ?>
                        <option value="<?= $row['produk_id'] ?>"
                            data-harga="<?= $row['harga'] ?>"
                            data-image="<?= $row['image'] ?>"
                            data-nama="<?= htmlspecialchars($row['name']) ?>">
                            <?= htmlspecialchars($row['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="persen_diskon">Persen Diskon (%):</label>
                <input type="number" name="persen_diskon" id="persenDiskon" min="1" max="100" required oninput="updateHarga()">
                <small>Masukkan nilai antara 1-100</small>
            </div>

            <div class="form-group">
                <label for="start_date">Tanggal Mulai:</label>
                <input type="datetime-local" name="start_date" id="startDate" required onchange="updateStatusPreview()">
            </div>

            <div class="form-group">
                <label for="end_date">Tanggal Berakhir:</label>
                <input type="datetime-local" name="end_date" id="endDate" required onchange="updateStatusPreview()">
            </div>

            <div class="realtime-status" id="statusPreview" style="display:none;">
                <strong>Status Diskon: </strong>
                <span id="statusIndicator" class="status-indicator"></span>
                <div id="statusMessage" style="margin-top:5px; font-size:14px;"></div>
            </div>

            <button type="submit">Tambah Diskon</button>
        </form>

        <div class="preview-card" id="previewCard" style="display:none;">
            <h3>Preview Produk</h3>
            <img id="preview-gambar" src="" alt="Preview Gambar" style="max-width:150px; height:auto;">
            <h4 id="preview-nama"></h4>
            <p id="preview-harga-asli"></p>
            <p id="preview-harga-diskon" style="color:red; font-weight:bold;"></p>
            <div id="preview-penghematan" style="color:green; font-weight:bold;"></div>
        </div>

        <h2 class="section-title">Daftar Produk Diskon</h2>

        <div class="filter-tabs">
            <button class="filter-tab active" onclick="filterCards('all')">Semua (<?= mysqli_num_rows($diskon_query) ?>)</button>
            <button class="filter-tab" onclick="filterCards('active')">Aktif</button>
            <button class="filter-tab" onclick="filterCards('expired')">Menunggu/Berakhir</button>
        </div>

        <div class="container diskon-list">
            <?php 
            mysqli_data_seek($diskon_query, 0); // Reset pointer
            while($row = mysqli_fetch_assoc($diskon_query)): 
                // Gunakan fungsi real-time status dari end-date.php
                $realtime_status = getDiscountStatus($row['start_date'], $row['end_date'], $row['status']);
                $countdown_data = getCountdownData($row['start_date'], $row['end_date']);
                
                $badgeClass = $realtime_status === 'active' ? 'badge-active' : 'badge-expired';
                $hargaDiskon = $row['harga'] - ($row['harga'] * $row['persen_diskon'] / 100);
                $penghematan = $row['harga'] * $row['persen_diskon'] / 100;
            ?>
            <div class="card" data-status="<?= $realtime_status ?>" data-discount-id="<?= $row['diskon_id'] ?>">
                <span class="discount-badge <?= $badgeClass ?>">
                    <?= $realtime_status === 'active' ? 'AKTIF' : 'MENUNGGU' ?>
                </span>
                
                <img src="../../<?= htmlspecialchars($row['image']) ?>" 
                     alt="<?= htmlspecialchars($row['nama_produk']) ?>" 
                     style="max-width:150px; height:auto;"
                     onerror="this.src='../../images/no-image.png'">
                
                <h3><?= htmlspecialchars($row['nama_produk']) ?></h3>
                
                <div class="price-info">
                    <p class="original-price">
                        <del>Rp <?= number_format($row['harga'], 0, ',', '.') ?></del>
                    </p>
                    <p class="discount-price">
                        <strong style="color:red;">Rp <?= number_format($hargaDiskon, 0, ',', '.') ?></strong> 
                        <span class="discount-percent">(-<?= $row['persen_diskon'] ?>%)</span>
                    </p>
                    <p class="savings" style="color:green; font-size:12px;">
                        Hemat: Rp <?= number_format($penghematan, 0, ',', '.') ?>
                    </p>
                </div>

                <div class="date-info" style="font-size:12px; color:#666; margin:10px 0;">
                    <div>Mulai: <?= date('d/m/Y H:i', strtotime($row['start_date'])) ?></div>
                    <div>Berakhir: <?= date('d/m/Y H:i', strtotime($row['end_date'])) ?></div>
                </div>

                <div class="countdown-timer" 
                     data-start-time="<?= $row['start_date'] ?>" 
                     data-end-time="<?= $row['end_date'] ?>"
                     data-discount-id="<?= $row['diskon_id'] ?>">
                    <div class="countdown-display">
                        <?php if ($countdown_data['type'] !== 'expired'): ?>
                            <strong><?= $countdown_data['message'] ?>:</strong><br>
                            <span class="time-remaining">
                                <?= formatCountdown($countdown_data['days'], $countdown_data['hours'], $countdown_data['minutes'], $countdown_data['seconds']) ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#999;">Sudah berakhir</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
    // Fungsi validasi form
    function validateForm() {
        const startDate = new Date(document.getElementById('startDate').value);
        const endDate = new Date(document.getElementById('endDate').value);
        
        if (endDate <= startDate) {
            alert('Tanggal berakhir harus setelah tanggal mulai!');
            return false;
        }
        
        const persen = parseInt(document.getElementById('persenDiskon').value);
        if (persen < 1 || persen > 100) {
            alert('Persen diskon harus antara 1-100!');
            return false;
        }
        
        return true;
    }

    // Preview produk yang dipilih
    function previewProduk() {
        const select = document.getElementById('produkSelect');
        const selected = select.options[select.selectedIndex];
        const previewCard = document.getElementById('previewCard');
        
        if (selected.value) {
            const nama = selected.getAttribute('data-nama') || '';
            const harga = parseFloat(selected.getAttribute('data-harga')) || 0;
            const image = selected.getAttribute('data-image') || '';

            document.getElementById('preview-nama').textContent = nama;
            document.getElementById('preview-gambar').src = "../../" + image;
            document.getElementById('preview-harga-asli').textContent = `Harga Asli: Rp ${harga.toLocaleString('id-ID')}`;
            
            previewCard.style.display = 'block';
            updateHarga();
        } else {
            previewCard.style.display = 'none';
        }
    }

    // Update harga dengan diskon
    function updateHarga() {
        const persen = parseFloat(document.querySelector('input[name="persen_diskon"]').value) || 0;
        const select = document.getElementById('produkSelect');
        const selected = select.options[select.selectedIndex];
        
        if (selected.value && persen > 0) {
            const harga = parseFloat(selected.getAttribute('data-harga')) || 0;
            const diskon = harga * persen / 100;
            const hargaDiskon = harga - diskon;
            
            document.getElementById('preview-harga-diskon').textContent = 
                `Harga Diskon: Rp ${hargaDiskon.toLocaleString('id-ID')}`;
            document.getElementById('preview-penghematan').textContent = 
                `Penghematan: Rp ${diskon.toLocaleString('id-ID')} (${persen}%)`;
        } else {
            document.getElementById('preview-harga-diskon').textContent = '';
            document.getElementById('preview-penghematan').textContent = '';
        }
    }

    // Update preview status berdasarkan tanggal
    function updateStatusPreview() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        const statusPreview = document.getElementById('statusPreview');
        const statusIndicator = document.getElementById('statusIndicator');
        const statusMessage = document.getElementById('statusMessage');
        
        if (startDate && endDate) {
            const now = new Date();
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            let status, statusClass, message;
            
            if (start > now) {
                status = 'MENUNGGU';
                statusClass = 'status-pending';
                const diffTime = start - now;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                message = `Diskon akan dimulai dalam ${diffDays} hari`;
            } else if (start <= now && end >= now) {
                status = 'AKTIF';
                statusClass = 'status-active';
                const diffTime = end - now;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                message = `Diskon aktif, berakhir dalam ${diffDays} hari`;
            } else {
                status = 'BERAKHIR';
                statusClass = 'status-expired';
                message = 'Diskon sudah berakhir';
            }
            
            statusIndicator.textContent = status;
            statusIndicator.className = 'status-indicator ' + statusClass;
            statusMessage.textContent = message;
            statusPreview.style.display = 'block';
        } else {
            statusPreview.style.display = 'none';
        }
    }

    // Filter kartu diskon berdasarkan status
    function filterCards(status) {
        const tabs = document.querySelectorAll('.filter-tab');
        tabs.forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');

        const cards = document.querySelectorAll('.card');
        let visibleCount = 0;
        
        cards.forEach(card => {
            const cardStatus = card.dataset.status;
            if (status === 'all' || cardStatus === status) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Update counter
        const activeTab = document.querySelector('.filter-tab.active');
        const currentText = activeTab.textContent.split('(')[0].trim();
        activeTab.textContent = `${currentText} (${visibleCount})`;
    }

    // Auto-refresh countdown setiap detik
    setInterval(function() {
        const countdownElements = document.querySelectorAll('.countdown-timer');
        countdownElements.forEach(element => {
            const startTime = element.getAttribute('data-start-time');
            const endTime = element.getAttribute('data-end-time');
            const discountId = element.getAttribute('data-discount-id');
            
            // Update countdown display
            updateCountdownDisplay(element, startTime, endTime);
            
            // Update status card jika diperlukan
            updateCardStatus(element.closest('.card'), startTime, endTime);
        });
    }, 1000);

    function updateCountdownDisplay(element, startTime, endTime) {
        const now = new Date();
        const start = new Date(startTime);
        const end = new Date(endTime);
        const display = element.querySelector('.time-remaining');
        
        if (!display) return;
        
        let targetDate, message;
        
        if (now < start) {
            targetDate = start;
            message = 'Dimulai dalam';
        } else if (now >= start && now <= end) {
            targetDate = end;
            message = 'Berakhir dalam';
        } else {
            display.innerHTML = '<span style="color:#999;">Sudah berakhir</span>';
            return;
        }
        
        const timeDiff = targetDate - now;
        if (timeDiff > 0) {
            const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
            
            let timeString = '';
            if (days > 0) timeString += `${days} hari `;
            if (hours > 0) timeString += `${hours} jam `;
            if (minutes > 0) timeString += `${minutes} menit `;
            if (days === 0) timeString += `${seconds} detik`;
            
            display.innerHTML = `<strong>${message}:</strong><br><span style="color: #007bff;">${timeString.trim()}</span>`;
        }
    }

    function updateCardStatus(card, startTime, endTime) {
        const now = new Date();
        const start = new Date(startTime);
        const end = new Date(endTime);
        const badge = card.querySelector('.discount-badge');
        
        let newStatus, newClass, newText;
        
        if (now < start) {
            newStatus = 'expired';
            newClass = 'badge-expired';
            newText = 'MENUNGGU';
        } else if (now >= start && now <= end) {
            newStatus = 'active';
            newClass = 'badge-active';
            newText = 'AKTIF';
        } else {
            newStatus = 'expired';
            newClass = 'badge-expired';
            newText = 'BERAKHIR';
        }
        
        // Update jika status berubah
        if (card.dataset.status !== newStatus) {
            card.dataset.status = newStatus;
            badge.className = 'discount-badge ' + newClass;
            badge.textContent = newText;
        }
    }

    // Set minimum date untuk input datetime-local (sekarang)
    document.addEventListener('DOMContentLoaded', function() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.getElementById('startDate').min = minDateTime;
        document.getElementById('endDate').min = minDateTime;
    });
    </script>
</body>
</html>