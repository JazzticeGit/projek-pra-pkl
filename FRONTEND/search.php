<?php
include "../koneksi.php";
$query = $_GET['query'];
// kemananan sql injeksyen
$query = $koneksi->real_escape_string($query);

$sql = "SELECT * FROM produk WHERE name LIKE '%$query%'";
$result = $koneksi->query($sql);

// hasil
if ($result->num_rows > 0) {
    echo "<h3>Hasil pencarian:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo "<p>" . $row['nama_barang'] . " - " . $row['harga'] . "</p>";
    }
} else {
    echo "Barang tidak ditemukan.";
}

$koneksi->close();
?>

<!-- FITUR SEARCH BELUM DALAM TAHAP PERCOBAAN -->