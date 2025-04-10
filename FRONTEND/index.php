<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing page</title>
    <link rel="stylesheet" href="../STYLESHEET/index_style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Bebas Neue' rel='stylesheet'>
</head>
<body>
    <!-- NAVIGASI BAR -->
    <nav>
        
        <div class="navbg">
            <!-- GAMBAR NAVIGASI -->
             <a href="index.php"><img src="../image/AGESA.png" alt="" srcset=""></a>


             <!-- LINK NAVIGASI -->
            <div class="navlink">
                <ul>
                    <li><a href="http://">Shop</a></li>  <!-- SEMENTARA SEBELUM DROPDoWN LINK -->
                    <li><a href="http://">Collection</a></li>
                    <li><a href="http://">About</a></li>
                    <li><a href="http://">Contack</a></li>
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
                <li><a href="http://" class="fa-solid fa-cart-shopping"></a></li> <!-- CART SHOPING LINK -->
                <li><a href="http://" class="fa-solid fa-user"></a></li> <!-- ACCOUNT LINK -->
             </ul>
             </div>
        </div>
    </nav>

    <!-- FIRST MAIN LAYOUT -->

    <div class="grid-container">
  <!-- Grid 1: Diskon -->
  <div class="grid_1">
    <h2>#Discount Up To <span>50%</span></h2>
    <h4>Oversized Boxy Work Jacket</h4>
    <p>Tetap hangat dan stylish dengan jaket berkualitas tinggi! <br>Dibuat dari bahan premium yang nyaman dipakai, desain modern<br> yang cocok  untuk segala suasana</p>
    <div class="container-tbl">
      <button class="tbl-harga">
      <a href="http://">Rp.800.000,00</a>
      <div class="round-2"><i class="fa-solid fa-arrow-right"></i></div>
      </button>

        <button class="tbl-harga-2">
          <a href="http://">Hot Discount</a>
          <div class="round"><i class="fa-solid fa-arrow-right"></i></div>
        </button>

        
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
    <a href="http://">View All</a>
    <div class="round"><i class="fa-solid fa-arrow-right"></i></div>
  </button>
  </div>

  <!-- Grid 4: Best Seller -->
  <div class="grid_4">
    <h3>#BEST SELLER</h3>
    <button>
      <a href="http://">View All</a>
      <div class="round"><i class="fa-solid fa-arrow-right"></i></div>
    </button>
  </div>
</div>

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
        Learn More <span class="arrow">↗</span>
      </a>
    </div>
    <div class="promo-image">
      <img src="../image/gambar.png" alt="Pakaian Kekinian" />
    </div>
  </section>

 <!-- KETIGA, 3 CARD BERSEBELAHAN -->

 <div class="collection-section">
  <p>Agesa Shop Collection</p>
  <h2>Collection 2025</h2>
  <div class="collection-grid">
    
    <!-- CARD 1 -->
    <div class="collection-card" style="background-color: #260000">
      <img src="../image/agesa putih.png" class="logo-img" alt="Agesa Logo" />
      <div class="card-content">
        <h3>ITEM BEST SELLER TERBAIK</h3>
        <p>Klik menu best seller dan temukan item favoritmu sekarang juga hanya di agesa shop</p>
      </div>
      <div class="arrow-icon"><i class="fa-solid fa-arrow-right"></i></div>
    </div>

    <!-- CARD 2 -->
    <div class="collection-card" style="background-color: #000">
      <img src="../image/agesa putih.png" class="logo-img" alt="Agesa Logo" />
      <div class="card-content">
        <h3>ITEM NEW ARRIVAL SESUAI ZAMAN</h3>
        <p>Brand new arrival yang lebih keren dan trend sesuai perkembangan zaman yang tak kalah oke, hanya di agesa shop</p>
      </div>
      <div class="arrow-icon"><i class="fa-solid fa-arrow-right"></i></div>
    </div>

    <!-- CARD 3 -->
    <div class="collection-card" style="background-color: #999">
      <img src="../image/agesa putih.png" class="logo-img" alt="Agesa Logo" />
      <div class="card-content">
        <h3>ALL ITEM AGESA SHOP</h3>
        <p>Koleksi trend agesa shop dan pilihan sesuai selera dan best sellingnya juga hanya di agesa shop</p>
      </div>
      <div class="arrow-icon"><i class="fa-solid fa-arrow-right"></i></div>
    </div>

  </div>
</div>


</body>
<script src="https://kit.fontawesome.com/2de2a0ed8e.js" crossorigin="anonymous"></script>
</html>