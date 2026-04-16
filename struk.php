<?php
include 'koneksi.php';

$kode = $_GET['kode'];

$trans = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT * FROM transaksi WHERE kode_transaksi='$kode'"
));

$detail = mysqli_query(
    $koneksi,
    "SELECT td.*, b.nama_barang 
     FROM transaksi_detail td
     JOIN list_barang b ON td.id_barang = b.id
     WHERE kode_transaksi='$kode'"
);

$kembalian = ($trans['metode'] == 'Cash')
    ? ($trans['bayar'] - $trans['total'])
    : 0;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Struk</title>

    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #f4f6f9;
        }

        .receipt {
            width: 320px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .center {
            text-align: center;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .sub {
            font-size: 12px;
            color: #666;
        }

        .line {
            border-top: 1px dashed #ccc;
            margin: 12px 0;
        }

        table {
            width: 100%;
            font-size: 12px;
            border-collapse: collapse;
        }

        td {
            padding: 4px 0;
        }

        .right {
            text-align: right;
        }

        .total-box {
            font-weight: bold;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
        }

        .btn {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .print {
            background: #4CAF50;
            color: white;
        }

        .back {
            background: #2196F3;
            color: white;
            text-decoration: none;
            display: block;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            margin-top: 8px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            margin-top: 10px;
            color: #777;
        }

        @media print {

            .btn,
            .back {
                display: none;
            }

            body {
                background: white;
            }

            .receipt {
                box-shadow: none;
                margin: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="receipt">

        <div class="center">
            <div class="title">TOKO SRI</div>
            <div class="sub">Kode: <?= $kode ?></div>
            <div class="sub">
                <?= date('d-m-Y H:i', strtotime($trans['tanggal'])) ?>
            </div>
        </div>

        <div class="line"></div>

        <table>
            <?php while ($d = mysqli_fetch_assoc($detail)) { ?>
                <tr>
                    <td><?= $d['nama_barang'] ?></td>
                    <td class="right">x<?= $d['qty'] ?></td>
                    <td class="right"><?= number_format($d['subtotal']) ?></td>
                </tr>
            <?php } ?>
        </table>

        <div class="line"></div>

        <div class="total-box">
            <span>Total</span>
            <span>Rp <?= number_format($trans['total']) ?></span>
        </div>

        <div class="total-box">
            <span>Metode</span>
            <span><?= $trans['metode'] ?></span>
        </div>

        <div class="total-box">
            <span>Bayar</span>
            <span>Rp <?= number_format($trans['bayar']) ?></span>
        </div>

        <div class="total-box">
            <span>Kembalian</span>
            <span>Rp <?= number_format($kembalian) ?></span>
        </div>

        <div class="line"></div>

        <div class="footer">
            Terima Kasih 🙏<br>
            Barang yang sudah dibeli tidak dapat dikembalikan
        </div>

        <button class="btn print" onclick="window.print()">PRINT STRUK</button>
        <a href="riwayat.php" class="back">Kembali</a>

    </div>

</body>

</html>