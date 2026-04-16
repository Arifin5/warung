<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <!-- PHP START -->
    <?php
    include 'koneksi.php';

    $data = mysqli_query($koneksi, "
    SELECT * FROM transaksi 
    ORDER BY id DESC
");
    ?>
    <!-- PHP END -->

    <!-- NAVBAR START -->
    <div class="container-navbar">
        <div class="strip-navbar" onclick="stripNavbar()">≡</div>
        <h1 class="h1-navbar">Riwayat Pembelian</h1>
        <ul class="ul-navbar">
            <li class="li-navbar"><a href="#">Logout</a></li>
        </ul>
    </div>
    <!-- NAVBAR END -->

    <!-- SIDEBAR START -->
    <div class="sideBar" id="sideBar">
        <ul class="ul-sideBar">
            <li class="li-sideBar"><a href="index.php"><i class="fa-solid fa-house"></i>Beranda</a></li>
            <li class="li-sideBar"><a href="transaksi.php"><i class="fa-solid fa-money-bill"></i>Transaksi</a></li>
            <li class="li-sideBar"><a href="riwayat.php"><i class="fa-solid fa-receipt"></i>Riwayat Pembelian</a></li>

            <li class="li-sideBar dropdown">
                <div class="dropdown-btn" onclick="toggleDropdown()">
                    <i class="fa-solid fa-gear"></i> Pengaturan
                    <span class="arrow">▼</span>
                </div>

                <ul class="submenu" id="submenu">
                    <li><a href="listBarang.php">List Stok Barang</a></li>
                    <li><a href="#">Profil</a></li>
                    <li><a href="#">Ubah Password</a></li>
                </ul>
            </li>
        </ul>
    </div>

    <div id="overlay" class="overlay" onclick="stripNavbar()"></div>
    <!-- SIDEBAR END -->

    <!-- CONTENT START -->
    <div class="container">

        <?php while ($t = mysqli_fetch_assoc($data)) { ?>

            <?php
            // hitung total dari detail
            $kode = $t['kode_transaksi'];

            $detail = mysqli_query($koneksi, "
            SELECT SUM(subtotal) as total_item
            FROM transaksi_detail
            WHERE kode_transaksi='$kode'
        ");

            $d = mysqli_fetch_assoc($detail);
            ?>

            <div class="card">

                <div class="kode">#<?= $kode ?></div>
                <div class="tanggal"><?= $t['tanggal'] ?></div>

                <div class="info">Total: Rp <?= number_format($t['total']) ?></div>
                <div class="info">Bayar: Rp <?= number_format($t['bayar']) ?></div>

                <div class="total">
                    Kembalian: Rp <?= number_format($t['bayar'] - $t['total']) ?>
                </div>

                <a class="btn" href="struk.php?kode=<?= $kode ?>">
                    Lihat Struk
                </a>

            </div>

        <?php } ?>

    </div>
    <!-- CONTENT END -->

    <!-- JS START -->
    <script>
        function stripNavbar() {
            document.getElementById("sideBar").classList.toggle("active");
            document.getElementById("overlay").classList.toggle("active");
        }

        function toggleDropdown() {
            const menu = document.getElementById("submenu");
            const parent = document.querySelector(".dropdown");

            menu.classList.toggle("active");
            parent.classList.toggle("active");
        }
    </script>
    <!-- JS END -->
</body>

</html>