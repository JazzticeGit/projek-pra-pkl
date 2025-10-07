<?php
include '../FRONTEND/session_config.php';
include '../../koneksi.php';

// Validasi admin session
validateAdminSession($koneksi);
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $query_select = mysqli_query($koneksi, "SELECT image FROM produk WHERE produk_id = $id");

    if (mysqli_num_rows($query_select) > 0) {
        $data = mysqli_fetch_assoc($query_select);
        $image_path = "../../" . $data['image']; 

        $query_delete = mysqli_query($koneksi, "DELETE FROM produk WHERE produk_id = $id");

        if ($query_delete) {
            if (file_exists($image_path)) {
                unlink($image_path);
            }

            echo "<script>alert('Produk berhasil dihapus!'); window.location.href='index-produk.php';</script>";
        } else {
            echo "Gagal menghapus produk: " . mysqli_error($koneksi);
        }
    } else {
        echo "Produk tidak ditemukan.";
    }
} else {
    echo "ID produk tidak valid.";
}
?>
