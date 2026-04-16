<?php
include 'koneksi.php';
include 'midtrans_config.php';

$kode = $_GET['kode'] ?? '';

if (!$kode) {
    echo "error";
    exit;
}

// ambil status dari midtrans
$status = \Midtrans\Transaction::status($kode);

// 🔥 VALIDASI
if (!is_object($status) || !isset($status->transaction_status)) {
    echo "pending";
    exit;
}

$trxStatus = $status->transaction_status;

// ambil transaksi DB
$trans = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT * FROM transaksi WHERE kode_transaksi='$kode'"
));

if (!$trans) {
    echo "error";
    exit;
}

// ================= SUCCESS =================
if (
    ($trxStatus == 'settlement' || $trxStatus == 'capture') &&
    $trans['status'] != 'lunas'
) {

    mysqli_begin_transaction($koneksi);

    try {

        mysqli_query($koneksi, "
            UPDATE transaksi 
            SET status='lunas', bayar=total 
            WHERE kode_transaksi='$kode'
        ");

        $detail = mysqli_query($koneksi, "
            SELECT * FROM transaksi_detail 
            WHERE kode_transaksi='$kode'
        ");

        while ($d = mysqli_fetch_assoc($detail)) {
            mysqli_query($koneksi, "
                UPDATE list_barang 
                SET jumlah_barang = jumlah_barang - {$d['qty']}
                WHERE id = {$d['id_barang']}
            ");
        }

        mysqli_commit($koneksi);

        echo "lunas";
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "error";
        exit;
    }
}

// ================= EXPIRED =================
if ($trxStatus == 'expire' || $trxStatus == 'cancel') {

    mysqli_query($koneksi, "
        UPDATE transaksi 
        SET status='expired' 
        WHERE kode_transaksi='$kode'
    ");

    echo "expired";
    exit;
}

// ================= PENDING =================
echo "pending";