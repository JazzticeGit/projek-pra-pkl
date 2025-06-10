<?php
session_start();
include '../koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
};


$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM toko_baju.users WHERE id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('User tidak ditemukan');
}

$user = $result->fetch_assoc();
$stmt->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing page</title>
    <link rel="stylesheet" href="../STYLESHEET/index_style.css">
    <link rel="stylesheet" href="../STYLESHEET/carousel.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Bebas Neue' rel='stylesheet'>
</head>
<body>
    <!-- NAVIGASI BAR -->
    <nav>
        
        <div class="navbg" id="nav">
            <!-- GAMBAR NAVIGASI -->
             <a href="index.php"><img src="../image/AGESA.png" alt="" srcset=""></a>


             <!-- LINK NAVIGASI -->
            <div class="navlink">
                <ul>
                    <li><a href="../FRONTEND/produk/all-produk.php">Shop</a></li>  
                    <li><a href="../FRONTEND/produk/colection.php">Collection</a></li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="#footer">Contact</a></li>
                    <li><a href="riwayat-transaksi.php">Transaksi</a></li>
                </ul>
            </div>


            <!-- SEARCH BAR -->
            <div class="searchBar">
                <form action="search.php" method="GET">
                <input type="text" name="query" placeholder="   Search  " required>
                <i class="fas fa-search"></i>
                <!-- <button type="submit">Cari</button> -->
                </form>
                
            </div>


            <!-- ICON LINK -->
             <div class="iconLink">
             <ul>
                <li><a href="keranjang.php" class="fa-solid fa-cart-shopping"></a></li> <!-- CART SHOPING LINK -->
                <li>
                <a href="#" class="fa-solid fa-user" id="profileTrigger"></a>
                </li>
                <script src="../javascript/profil.js"></script>
             </ul>
             </div>
        </div>
    </nav>


    <!-- FIRST MAIN LAYOUT -->

    <div class="grid-container">
  <!-- Grid 1: Diskon -->
  <div class="grid_1">
    <h2>#collection of <span>2024</span></h2>
    <h4>terbaik dan terlaris</h4>
    <p>segera dapatkan dan jangan sampai terlewat untuk koleksi kami, Siap tampil beda? Temukan koleksi terbaik kami yang siap mengubah gayamu!</p>
    <div class="container-tbl">
      <button class="tbl-harga">
      <a href="../FRONTEND/produk/colection.php">Preview</a>
      <div class="round-2"><i class="fa-solid fa-arrow-right"></i></div>
      </button>

        <!-- <button class="tbl-harga-2">
          <a href="http://">Hot Discount</a>
          <div class="round"><i class="fa-solid fa-arrow-right"></i></div>
        </button> -->

        
        </div>
  </div>

  <!-- Grid 2: Semua Orang Berhak Kece -->
  <div class="grid_2">
    <h1>SEMUA ORANG <br>BERHAK KECE!!</h1>
    <p>Dari outfit kasual hingga busana elegan, kami menyediakan beragam pilihan dengan <br> bahan berkualitas dan desain terkini. Belanja mudah, nyaman, dan pastinya bikin <br> penampilan makin kece.</p>
    <button>
      <a href="http://">View All</a>
      <div class="round"><i class="fa-solid fa-arrow-right"></i></div>
    </button>
  </div>

  <!-- Grid 3: New Arrival -->
  <div class="grid_3">
    <h3>#NEW ARRIVAL</h3>
    <button>
    <a href="../FRONTEND/produk/new_arrival.php">View All</a>
    <div class="round"><i class="fa-solid fa-arrow-right"></i></div>
  </button>
  </div>

  <!-- Grid 4: Best Seller -->
  <div class="grid_4">
    <h3>#BEST SELLER</h3>
    <button>
      <a href="../FRONTEND/produk/produk.php">View All</a>
      <div class="round"><i class="fa-solid fa-arrow-right"></i></div>
    </button>
  </div>
</div>


    <!-- INFINITE CAROUSEL SECTION -->
    <section class="carousel-section">
        <div class="carousel-container">
            <div class="carousel-title">
                <h2>Koleksi Fashion Terbaru</h2>
                <p>Temukan style terbaik untuk tampilan yang memukau</p>
            </div>
            
            <div class="carousel-wrapper">
                <div class="carousel-track">
                    <div class="carousel-slide">
                        <img src="../image/kaos1.jpg" alt="Fashion Collection 1">
                        <div class="carousel-overlay">
                            <div class="slide-content">
                                <h3>Koleksi Casual</h3>
                                <p>Gaya santai yang tetap stylish untuk aktivitas sehari-hari</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-slide">
                        <img src="../image/kaos2.jpg" alt="Fashion Collection 2">
                        <div class="carousel-overlay">
                            <div class="slide-content">
                                <h3>Koleksi Formal</h3>
                                <p>Busana elegan untuk tampilan profesional dan acara khusus</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-slide">
                        <img src="../image/kaos3.jpg" alt="Fashion Collection 3">
                        <div class="carousel-overlay">
                            <div class="slide-content">
                                <h3>Koleksi Trendy</h3>
                                <p>Fashion terkini yang mengikuti perkembangan zaman</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-slide">
                        <img src="../image/download (16).jpg" alt="Fashion Collection 4">
                        <div class="carousel-overlay">
                            <div class="slide-content">
                                <h3>Koleksi Vintage</h3>
                                <p>Gaya klasik yang timeless dan selalu menarik</p>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-slide">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Fashion Collection 1">
                        <div class="carousel-overlay">
                            <div class="slide-content">
                                <h3>Koleksi Casual</h3>
                                <p>Gaya santai yang tetap stylish untuk aktivitas sehari-hari</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="carousel-indicators">
                <div class="indicator active"></div>
                <div class="indicator"></div>
                <div class="indicator"></div>
                <div class="indicator"></div>
            </div>
        </div>
    </section>



<!-- LANDING PAGE SECOND MAIN -->

<section class="promo-box">
    <div class="promo-content">
    <img src="../image/agesa putih.png" alt="Agesa Shop Logo" class="brand-logo" />
      <h1>Jika Anda Berjiwa Muda<br>Pakaian Agesa Brand Cocok Untukmu</h1>
      <p>
        pakaian kekinian cocok untuk semua usia asalkan berjiwa muda
        , jangan sampai ketinggalan produk terlaris dan terbaru kami lalu ada juga 
        diskonnya dan buruan ambil item favoritmu sekarang juga hanya di agesa shop
      </p>
      <a href="#" class="promo-button">
        Learn More <span class="arrow"><i class="fa-solid fa-arrow-right"></i></span>
      </a>
    </div>
    <div class="promo-image">
      <img src="../image/Untitled design (6).png" alt="Pakaian Kekinian" />
    </div>
  </section>





  

 <!-- KETIGA, 3 CARD BERSEBELAHAN -->

 <div class="collection-section">
  <p class="subheading">Agesa Shop Collection</p>
  <h2 class="main-heading">Collection 2025</h2>

  <div class="collection-grid">

    <!-- Card 1 -->
    <div class="collection-card" style="background-color: #260000;">
      <img src="../image/agesa putih.png" alt="Agesa Logo" class="logo-img">
      <div class="card-text">
        <h3>ITEM BEST SELLER TERBAIK</h3>
        <p>Klik menu best seller dan temukan item favoritmu sekarang juga hanya di agesa shop</p>
      </div>
      <div class="arrow-icon-1"><i class="fa-solid fa-arrow-right"></i></div>
    </div>

    <!-- Card 2 -->
    <div class="collection-card" style="background-color: #000000;">
      <img src="../image/agesa putih.png" alt="Agesa Logo" class="logo-img">
      <div class="card-text">
        <h3>ITEM NEW ARRIVAL SESUAI ZAMAN</h3>
        <p>Brand new arrival yang lebih keren dan trend sesuai perkembangan zaman yang tak kalah oke, hanya di agesa shop</p>
      </div>
      <div class="arrow-icon-1"><i class="fa-solid fa-arrow-right"></i></div>
        </div>

    <!-- Card 3 -->
    <div class="collection-card" style="background-color: #999999;">
      <img src="../image/agesa putih.png" alt="Agesa Logo" class="logo-img">
      <div class="card-text">
        <h3>ALL ITEM AGESA SHOP</h3>
        <p>Koleksi trend agesa shop dan pilihan sesuai selera dan best sellingnya juga hanya di agesa shop</p>
      </div>
      <div class="arrow-icon-1"><i class="fa-solid fa-arrow-right"></i></div>
    </div>

  </div>
</div>

<!-- MAIN KE EMPAT -->

<div class="about-container">



  <!-- GAMBAR DAN ABOUT -->

  <div class="image-bg">
    <h1>AGESA SHOP <br>OFFICIAL WEBSITE</h1>
    <h2>ABOUT DEVELOPER</h2>

    <!-- tombol -->
    <button>
      <a href="about.html">About Page</a>
      <div class="round-about"><i class="fa-solid fa-arrow-right"></i></div>
    </button>
  </div>
</div>

<div class="checkout-container">
  <!-- GAMBAR -->
   <img src="../image/cara checkout 1.png" alt="eror" srcset="">
</div>





<!-- Avatar / Profil Trigger -->
<li>
    <a href="#" id="profileTrigger">
        <img src="<?php echo !empty($user['icon']) ? htmlspecialchars($user['icon']) : 'default-avatar.jpg'; ?>" 
             alt="User Avatar" 
             style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
    </a>
</li>

<!-- Overlay Structure -->
<div id="profileOverlay" class="profile-overlay">
    <div class="profile-overlay-content">
        <span class="close-profile">&times;</span>
        <div class="profile-header">
            <div class="profile-avatar">
                <img src="../<?php echo !empty($user['icon']) ? htmlspecialchars($user['icon']) : 'default-avatar.jpg'; ?>" 
                     alt="User Avatar">
            </div>
            <h3><?php echo htmlspecialchars($user['username']); ?></h3>
        </div>
        <div class="profile-details">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Tanggal Lahir:</strong> <?php echo date('d F Y', strtotime($user['birth'])); ?></p>
            <p><strong>Telepon:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
            <p><strong>Bergabung:</strong> <?php echo date('d F Y', strtotime($user['tgl_daftar'])); ?></p>
        </div>
        <div class="profile-actions">
            <a href="keranjang.php" class="profile-btn"><i class="fas fa-shopping-cart"></i> Keranjang Saya</a>
            <a href="update-profil.php" class="profile-btn"><i class="fas fa-user-edit"></i> Edit Profil</a>
            <a href="logout.php" class="profile-btn logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</div>



<!-- FOOTER -->

<footer class="footer-container">
  <div class="footer-content">
    <!-- Kiri: Logo dan ikon sosial -->
    <div class="footer-left">
      <img src="../image/AGESA.png" alt="Agesa Shop Logo" class="footer-logo">
      <p><strong>clothing and accessories</strong></p>
      <div class="social-icons">
        <i class="fa-brands fa-instagram"></i>
        <i class="fa-brands fa-whatsapp"></i>
        <i class="fa-brands fa-tiktok"></i>
        <i class="fa-solid fa-bag-shopping"></i>
      </div>
    </div>

    <!-- Tengah: Navigasi dan teks -->
    <div class="footer-center" id="footer">
      <nav class="footer-nav">
        <a href="#nav">Home</a>
        <a href="#">Preview</a>
        <a href="about.html">About</a>
        <a href="#footer">Contact</a>
      </nav>
      <p class="footer-text">
        terimakasih sudah mengunjungi dan berbelanja pada toko kami, kenyamanan dan kepercayaan pembeli sangat berarti bagi kami
      </p>
    </div>

    <!-- Kanan: Maps -->
    <div class="footer-right">
      <h4>Maps Store</h4>
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.5542083109262!2d109.34410847400319!3d-7.4037412926063295!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6559b9ff8d3795%3A0xa58daaef273f4e44!2sSMKN%201%20Purbalingga!5e0!3m2!1sen!2sid!4v1744776124049!5m2!1sen!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>

  <!-- Bawah: Copyright -->
  <div class="footer-bottom">
    <p>&copy; hak cipta dilindungi undang undang</p>
  </div>
</footer>

<script>
// ===== INFINITE CAROUSEL JAVASCRIPT =====
document.addEventListener('DOMContentLoaded', function() {
    // Auto-update indicators untuk carousel
    const indicators = document.querySelectorAll('.carousel-indicators .indicator');
    let currentIndex = 0;
    
    function updateIndicators() {
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === currentIndex);
        });
        currentIndex = (currentIndex + 1) % 4;
    }
    
    // Update indicators every 3.75 seconds (15s animation / 4 slides)
    if (indicators.length > 0) {
        setInterval(updateIndicators, 3750);
    }
    
    // Pause animation on hover
    const carouselTrack = document.querySelector('.carousel-track');
    const container = document.querySelector('.carousel-container');
    
    if (container && carouselTrack) {
        container.addEventListener('mouseenter', () => {
            carouselTrack.style.animationPlayState = 'paused';
        });
        
        container.addEventListener('mouseleave', () => {
            carouselTrack.style.animationPlayState = 'running';
        });
    }
    
    // Optional: Click handler untuk indicators
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            // Reset all indicators
            indicators.forEach(ind => ind.classList.remove('active'));
            // Set clicked indicator as active
            indicator.classList.add('active');
            currentIndex = index;
        });
    });
});
</script>

</body>
<script src="https://kit.fontawesome.com/2de2a0ed8e.js" crossorigin="anonymous">
</script>



</html>