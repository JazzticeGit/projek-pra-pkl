<?php
session_start();
include '../../koneksi.php';

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Handle add to cart
if (isset($_POST['action']) && $_POST['action'] == 'add_to_cart' && isset($_POST['produk_id'])) {
    $produk_id = (int)$_POST['produk_id'];
    $size = isset($_POST['size']) ? $_POST['size'] : '';
    $color = isset($_POST['color']) ? $_POST['color'] : '';
    
    // Get product details
    $query = "
        SELECT p.*, d.persen_diskon, d.start_date, d.end_date
        FROM produk p
        LEFT JOIN diskon d ON p.produk_id = d.produk_id 
            AND d.status = 'active' 
            AND NOW() BETWEEN d.start_date AND d.end_date
        WHERE p.produk_id = $produk_id
    ";
    $produk_query = mysqli_query($koneksi, $query);
    $produk = mysqli_fetch_assoc($produk_query);

    if ($produk) {
        // Calculate final price
        $harga_asli = $produk['harga'];
        $persen_diskon = $produk['persen_diskon'];
        $harga_final = ($persen_diskon && $persen_diskon > 0) ? $harga_asli * (1 - $persen_diskon / 100) : $harga_asli;

        // Create unique cart key with size and color
        $cart_key = $produk_id . '_' . $size . '_' . $color;

        if (isset($_SESSION['keranjang'][$cart_key])) {
            $_SESSION['keranjang'][$cart_key]['jumlah'] += 1;
        } else {
            $_SESSION['keranjang'][$cart_key] = [
                'id' => $produk['produk_id'],
                'nama' => $produk['name'],
                'harga' => $harga_final,
                'gambar' => $produk['image'],
                'jumlah' => 1,
                'size' => $size,
                'color' => $color
            ];
        }
        
        echo "<script>alert('Produk berhasil ditambahkan ke keranjang!');</script>";
    }
}

// Get product ID from URL
$produk_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($produk_id == 0) {
    header("Location: produk.php");
    exit;
}

// Query for product details with discount
$query = "
    SELECT p.*, k.nama_kategori,
           d.persen_diskon, d.start_date, d.end_date
    FROM produk p
    LEFT JOIN kategori k ON p.kategori_id = k.id
    LEFT JOIN diskon d ON p.produk_id = d.produk_id 
        AND d.status = 'active' 
        AND NOW() BETWEEN d.start_date AND d.end_date
    WHERE p.produk_id = $produk_id
";

$result = mysqli_query($koneksi, $query);
$produk = mysqli_fetch_assoc($result);

if (!$produk) {
    header("Location: produk.php");
    exit;
}

// Calculate prices
$harga_asli = $produk['harga'];
$persen_diskon = $produk['persen_diskon'];
$harga_diskon = ($persen_diskon && $persen_diskon > 0) ? $harga_asli * (1 - $persen_diskon / 100) : $harga_asli;

// Parse size options from enum
$size_options = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produk['name']) ?> - Detail Produk</title>
    <link rel="stylesheet" href="../../STYLESHEET/produk.css">
    <link rel="stylesheet" href="../../STYLESHEET/detail-produk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">