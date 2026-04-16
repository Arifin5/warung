<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'koneksi.php';

// AMBIL DATA
$nama_barang   = $_POST['nama_barang'];
$jumlah_barang = $_POST['jumlah_barang'];
$satuan        = $_POST['satuan'];
$isi_per_dus   = !empty($_POST['isi_per_dus']) ? intval($_POST['isi_per_dus']) : NULL;
$harga         = $_POST['harga'];
$tgl_expired   = !empty($_POST['tgl_expired']) ? $_POST['tgl_expired'] : NULL;

if ($satuan == 'dus' && empty($isi_per_dus)) {
    echo "Isi per dus wajib diisi!";
    exit;
}

if ($satuan == 'dus') {
    $jumlah_barang = $jumlah_barang * $isi_per_dus; // jadi pcs
    $jenis_satuan = 'pcs';
    $satuan = 'pcs';
} elseif ($satuan == 'kg') {
    $jenis_satuan = 'kg';
} else {
    $jenis_satuan = 'pcs';
}

// QUERY
$query = "INSERT INTO list_barang 
(nama_barang, jumlah_barang, satuan, jenis_satuan, isi_per_dus, harga, tgl_expired)
VALUES 
('$nama_barang', '$jumlah_barang', '$satuan', '$jenis_satuan', 
" . ($isi_per_dus ? "'$isi_per_dus'" : "NULL") . ",
'$harga',
" . ($tgl_expired ? "'$tgl_expired'" : "NULL") . ")";

if (mysqli_query($koneksi, $query)) {
    header("Location: listBarang.php");
    exit;
} else {
    echo "Error: " . mysqli_error($koneksi);
}
