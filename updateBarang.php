<?php
include 'koneksi.php';

$id = $_GET['id'];

$nama_barang   = $_POST['nama_barang'];
$jumlah_barang = $_POST['jumlah_barang'];
$satuan        = $_POST['satuan'];
$isi_per_dus   = !empty($_POST['isi_per_dus']) ? $_POST['isi_per_dus'] : NULL;
$tgl_expired   = !empty($_POST['tgl_expired']) ? $_POST['tgl_expired'] : NULL;
$harga         = $_POST['harga'];

// VALIDASI
if ($satuan == 'dus') {
    if (empty($isi_per_dus)) {
        echo "Isi per dus wajib diisi!";
        exit;
    }

    $jumlah_barang = $jumlah_barang * $isi_per_dus;
    $jenis_satuan = 'pcs';
} elseif ($satuan == 'kg') {
    $jenis_satuan = 'kg';
    $isi_per_dus = NULL;
} else {
    $jenis_satuan = 'pcs';
    $isi_per_dus = NULL;
}

// QUERY UPDATE
$query = "UPDATE list_barang SET
    nama_barang='$nama_barang',
    jumlah_barang='$jumlah_barang',
    satuan='$satuan',
    jenis_satuan='$jenis_satuan',
    isi_per_dus=" . ($isi_per_dus ? "'$isi_per_dus'" : "NULL") . ",
    tgl_expired=" . ($tgl_expired ? "'$tgl_expired'" : "NULL") . ",
    harga='$harga'
    WHERE id='$id'
";

if (mysqli_query($koneksi, $query)) {
    header("Location: listBarang.php");
} else {
    echo mysqli_error($koneksi);
}