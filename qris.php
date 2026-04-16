<?php
$qr = $_GET['qr'] ?? '';
$kode = $_GET['kode'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>QRIS Payment</title>
</head>
<body style="text-align:center; font-family:sans-serif;">

<h2>Scan QR untuk bayar</h2>

<?php if ($qr) { ?>
    <img src="<?= $qr ?>" width="300"><br><br>
<?php } else { ?>
    <p>QR tidak ditemukan</p>
<?php } ?>

<p>Kode Transaksi: <?= $kode ?></p>

<a href="cek_status.php?kode=<?= $kode ?>">Cek Status</a>

</body>
</html>