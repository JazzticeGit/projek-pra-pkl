<?php
$koneksi = mysqli_connect("localhost", "root", "", "toko_baju");

if($koneksi){
    echo "y";
} else {
    echo "n";
}
?>