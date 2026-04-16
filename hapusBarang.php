<?php
include 'koneksi.php';

$id = $_GET['id'];

$query = mysqli_query($koneksi, "DELETE FROM list_barang WHERE id='$id'");

if ($query) {
    header("Location: listBarang.php");
    exit;
} else {
    echo "Gagal hapus: " . mysqli_error($koneksi);
}
?>