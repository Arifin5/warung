<?php
include 'koneksi.php';
include 'midtrans_config.php';

$data   = json_decode($_POST['data']);
$bayar  = $_POST['bayar'] ?? 0;
$metode = $_POST['metode'] ?? 'Cash';

if (!$data) die("Keranjang kosong");

$kode = "TRX" . time();
$total = 0;

// ================= HITUNG TOTAL =================
foreach ($data as $item) {
    $total += $item->qty * $item->harga;
}
if ($metode != 'Cash') {
    $bayar = $total;
}

// ================= JIKA CASH =================
if ($metode == 'Cash') {

    if ($bayar < $total) {
        die("Uang tidak cukup");
    }

    mysqli_begin_transaction($koneksi);

    try {

        foreach ($data as $item) {

            $id  = (int)$item->id;
            $qty = (float)$item->qty;

            $barang = mysqli_fetch_assoc(mysqli_query(
                $koneksi,
                "SELECT * FROM list_barang WHERE id='$id' FOR UPDATE"
            ));

            if (!$barang) throw new Exception("Barang tidak ditemukan");

            if ($barang['jumlah_barang'] < $qty)
                throw new Exception("Stok tidak cukup");

            $subtotal = $qty * $barang['harga'];

            // kurangi stok
            $stok_baru = $barang['jumlah_barang'] - $qty;

            mysqli_query($koneksi, "
                UPDATE list_barang 
                SET jumlah_barang='$stok_baru'
                WHERE id='$id'
            ");

            // simpan detail
            mysqli_query($koneksi, "
                INSERT INTO transaksi_detail
                (kode_transaksi, id_barang, qty, harga, subtotal)
                VALUES
                ('$kode', '$id', '$qty', '{$barang['harga']}', '$subtotal')
            ");
        }

        // simpan transaksi
        mysqli_query($koneksi, "
            INSERT INTO transaksi
            (kode_transaksi, total, bayar, metode, status, tanggal)
            VALUES
            ('$kode', '$total', '$bayar', 'Cash', 'lunas', NOW())
        ");

        mysqli_commit($koneksi);

        header("Location: struk.php?kode=$kode");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        die("ERROR: " . $e->getMessage());
    }
}

// ================= JIKA QRIS =================
if ($metode == 'QRIS' || $metode == 'DANA' || $metode == 'GOPAY') {

    mysqli_begin_transaction($koneksi);

    try {

        foreach ($data as $item) {

            $id  = (int)$item->id;
            $qty = (float)$item->qty;

            $barang = mysqli_fetch_assoc(mysqli_query(
                $koneksi,
                "SELECT * FROM list_barang WHERE id='$id'"
            ));

            if (!$barang) throw new Exception("Barang tidak ditemukan");

            $subtotal = $qty * $barang['harga'];

            mysqli_query($koneksi, "
                INSERT INTO transaksi_detail
                (kode_transaksi, id_barang, qty, harga, subtotal)
                VALUES
                ('$kode', '$id', '$qty', '{$barang['harga']}', '$subtotal')
            ");
        }

        mysqli_query($koneksi, "
            INSERT INTO transaksi
            (kode_transaksi, total, bayar, metode, status, tanggal)
            VALUES
            ('$kode', '$total', '$total', '$metode', 'pending', NOW())
        ");

        mysqli_commit($koneksi);
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }

    // MIDTRANS
    $params = [
        'transaction_details' => [
            'order_id' => $kode,
            'gross_amount' => (int)$total,
        ],
        'payment_type' => 'qris'
    ];

    try {
        $response = \Midtrans\CoreApi::charge($params);

        $qr_url = '';

        foreach ($response->actions as $a) {
            if ($a->name == 'generate-qr-code') {
                $qr_url = $a->url;
            }
        }

        if (!$qr_url) {
            echo json_encode(['error' => 'QR tidak tersedia']);
            exit;
        }

        echo json_encode([
            'status' => 'success',
            'qr' => $qr_url,
            'kode' => $kode
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}
