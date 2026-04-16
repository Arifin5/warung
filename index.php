<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <!-- PHP START -->
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    include 'koneksi.php';

    // total penjualan hari ini
    $today = date('Y-m-d');

    $penjualan = mysqli_fetch_assoc(mysqli_query($koneksi, "
SELECT COALESCE(SUM(total),0) as total_harian
FROM transaksi
WHERE tanggal >= CURDATE()
AND tanggal < (CURDATE() + INTERVAL 1 DAY)
")) ?? ['total_harian' => 0];

    $transaksi = mysqli_fetch_assoc(mysqli_query($koneksi, "
SELECT COUNT(*) as total_transaksi
FROM transaksi
WHERE tanggal >= CURDATE()
AND tanggal < (CURDATE() + INTERVAL 1 DAY)
")) ?? ['total_transaksi' => 0];

    $barang = mysqli_fetch_assoc(mysqli_query($koneksi, "
SELECT COALESCE(SUM(td.qty),0) as total_barang
FROM transaksi_detail td
JOIN transaksi t ON td.kode_transaksi = t.kode_transaksi
WHERE t.tanggal >= CURDATE()
AND t.tanggal < (CURDATE() + INTERVAL 1 DAY)
")) ?? ['total_barang' => 0];

    $stok = mysqli_query($koneksi, "
SELECT * FROM list_barang
WHERE jumlah_barang < 5
") or die(mysqli_error($koneksi));

    $grafik = mysqli_query($koneksi, "
    SELECT DATE(tanggal) as tgl, SUM(total) as total
    FROM transaksi
    WHERE tanggal >= CURDATE() - INTERVAL 6 DAY
    GROUP BY DATE(tanggal)
    ORDER BY tgl ASC
");

    $tanggal = [];
    $total = [];

    while ($g = mysqli_fetch_assoc($grafik)) {
        $tanggal[] = date('d/m', strtotime($g['tgl']));
        $total[] = $g['total'];
    }
    ?>
    <!-- PHP END -->

    <!-- NAVBAR START -->
    <div class="container-navbar">
        <div class="strip-navbar" onclick="stripNavbar()">≡</div>
        <h1 class="h1-navbar">DASHBOARD</h1>
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
    <div class="container-dashboard">

        <!-- CARD -->
        <div class="grid">

            <div class="card green">
                <i class="fa-solid fa-coins"></i>
                <h3>Penjualan Hari Ini</h3>
                <h1>Rp <?= number_format($penjualan['total_harian']) ?></h1>
            </div>

            <div class="card blue">
                <i class="fa-solid fa-receipt"></i>
                <h3>Total Transaksi</h3>
                <h1><?= $transaksi['total_transaksi'] ?></h1>
            </div>

            <div class="card orange">
                <i class="fa-solid fa-box"></i>
                <h3>Barang Terjual</h3>
                <h1><?= $barang['total_barang'] ?></h1>
            </div>

            <div class="card red">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <h3>Stok Menipis</h3>
                <h1><?= mysqli_num_rows($stok) ?></h1>
            </div>

        </div>

        <!-- STOK LIST -->
        <div class="stok">
            <h3>⚠️ Stok Menipis</h3>

            <?php if ($stok && mysqli_num_rows($stok) > 0) { ?>
                <?php while ($s = mysqli_fetch_assoc($stok)) { ?>
                    <div class="stok-item">
                        <?= $s['nama_barang'] ?> - <?= $s['jumlah_barang'] ?> tersisa
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="stok-item">Tidak ada stok menipis 🎉</div>
            <?php } ?>
        </div>

        <!-- MENU -->
        <div class="menu">
            <a href="transaksi.php">💳 Transaksi</a>
            <a href="riwayat.php">📜 Riwayat</a>
            <a href="listBarang.php">📦 Barang</a>
        </div>

        <div class="chart-box">
            <h3>📊 Grafik Penjualan</h3>
            <canvas id="chartPenjualan"></canvas>
        </div>

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

        const ctx = document.getElementById('chartPenjualan').getContext('2d');

        // 🔥 gradient biar smooth
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(33, 150, 243, 0.4)');
        gradient.addColorStop(1, 'rgba(33, 150, 243, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($tanggal) ?>,
                datasets: [{
                    label: 'Penjualan',
                    data: <?= json_encode($total) ?>,
                    borderColor: '#2196f3',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#2196f3',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#eee'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>
    <!-- JS END -->
</body>

</html>