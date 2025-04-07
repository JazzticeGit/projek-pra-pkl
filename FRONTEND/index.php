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

    <main class="grid-container">
        
    <!-- ========== GRID SATU KIRI ATAS  =========== -->
    <div class="grid_1">
    <h1>SEMUA ORANG BERHAK KECE!!</h1>
    <p>Dari outfit kasual hingga busana elegan...</p>
    <button>View All</button>
    </div>

    <!-- ==========  GRID DUA BEST SELLER  ========== -->
    <div class="grid_2">
    <h3>#BEST SELLER</h3>
    <img src="baju1.jpg" alt="Best Seller">
    <button>Learn More</button>
    </div>

    <!-- =========  GRID EMPAT NEW ARRIVAL  ========== -->
    <div class="grid_3">
    <h3>#NEW ARRIVAL</h3>
    <img src="topi1.jpg" alt="New Arrival">
    <button>Learn More</button>
    </div>

    <!-- ===========  GRID EMPAT KANAN PALING BESAR  ========== -->
    <div class="grid_4">
    <h2>#Discount Up To <span>50%</span></h2>
    <img src="jaket1.jpg" alt="Discount Item">
    <h4>Oversized Boxy Work Jacket</h4>
    <p>Tetap hangat dan stylish...</p>
    <p class="price">Rp. 800.000,00</p>
    <button>Hot Discount</button>
    </div>

    </main>

</body>
<script src="https://kit.fontawesome.com/2de2a0ed8e.js" crossorigin="anonymous"></script>
</html>