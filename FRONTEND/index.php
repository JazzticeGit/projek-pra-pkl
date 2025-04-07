<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing page</title>
    <link rel="stylesheet" href="../STYLESHEET/index_style.css">
</head>
<body>
    <!-- NAVIGASI BAR -->
    <nav>
        
        <div class="navbg">
            <!-- GAMBAR NAVIGASI -->
             <a href="index.php"><img src="" alt="" srcset=""></a>


             <!-- LINK NAVIGASI -->
            <div class="navlink">
                <ul>
                    <li><a href="http://">Shop</a></li>  <!-- SEMENTARA SEBELUM DROPDoWN LINK -->
                    <li><a href="http://">Collection</a></li>
                    <li><a href="http://">About</a></li>
                    <li><a href="http://">Comtack</a></li>
                </ul>
            </div>


            <!-- SEARCH BAR -->
            <div class="searchBar">
                <form action="search.php" method="GET">
                <input type="text" name="query" placeholder="   Search  " required>
                <button type="submit">Cari</button>
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
</body>
<script src="https://kit.fontawesome.com/2de2a0ed8e.js" crossorigin="anonymous"></script>
</html>