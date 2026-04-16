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

    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'semua';

    if ($filter == 'expired') {
        $query = "SELECT * FROM list_barang WHERE tgl_expired < CURDATE()";
    } elseif ($filter == 'aman') {
        $query = "SELECT * FROM list_barang WHERE tgl_expired > CURDATE()";
    } elseif ($filter == 'tidak_ada') {
        $query = "SELECT * FROM list_barang WHERE tgl_expired IS NULL OR tgl_expired = ''";
    } else {
        $query = "SELECT * FROM list_barang";
    }

    $data = mysqli_query($koneksi, $query . " ORDER BY tgl_expired ASC");
    ?>
    <!-- PHP END -->

    <!-- NAVBAR START -->
    <div class="container-navbar">
        <div class="strip-navbar" onclick="stripNavbar()">≡</div>
        <h1 class="h1-navbar">Tambah Barang</h1>
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
    <div class="filter-barang">
        <a href="?filter=semua" class="btn-filter <?php echo ($filter == 'semua') ? 'active' : ''; ?>">Semua</a>
        <a href="?filter=expired" class="btn-filter <?php echo ($filter == 'expired') ? 'active' : ''; ?>">Expired</a>
        <a href="?filter=aman" class="btn-filter <?php echo ($filter == 'aman') ? 'active' : ''; ?>">Aman</a>
        <a href="?filter=tidak_ada" class="btn-filter <?php echo ($filter == 'tidak_ada') ? 'active' : ''; ?>">Tanpa Expired</a>
    </div>

    <div class="list-barang">

        <?php if (mysqli_num_rows($data) == 0) { ?>
            <p>Belum ada data barang</p>
        <?php } ?>

        <?php while ($d = mysqli_fetch_array($data)) {

            if (empty($d['tgl_expired'])) {
                $status = "Tidak ada expired";
                $statusClass = "text-gray";
            } elseif ($d['tgl_expired'] < date('Y-m-d')) {
                $status = "Expired";
                $statusClass = "text-red";
            } else {
                $status = "Aman";
                $statusClass = "text-green";
            }
        ?>

            <div class="card-barang">

                <div class="card-kiri">
                    <h4><?php echo $d['nama_barang']; ?></h4>

                    <p>
                        Harga:
                        <?php
                        if ($d['jenis_satuan'] == 'kg') {
                            echo "Rp " . number_format($d['harga']) . " / kg";
                        } else {
                            echo "Rp " . number_format($d['harga']) . " / pcs";
                        }
                        ?>
                    </p>

                    <p>
                        Jumlah:
                        <?php
                        if ($d['jenis_satuan'] == 'kg') {
                            echo number_format($d['jumlah_barang']) . " kg";
                        } else {
                            if (!empty($d['isi_per_dus'])) {
                                $dus = floor($d['jumlah_barang'] / $d['isi_per_dus']);
                                echo $d['jumlah_barang'] . " pcs";
                                if ($dus > 0) {
                                    echo " (" . $dus . " dus)";
                                }
                            } else {
                                echo $d['jumlah_barang'] . " pcs";
                            }
                        }
                        ?>
                    </p>
                </div>

                <div class="card-kanan">
                    <p><?php echo empty($d['tgl_expired']) ? '-' : $d['tgl_expired']; ?></p>

                    <span class="<?php echo $statusClass; ?>">
                        <?php echo $status; ?>
                    </span>

                    <div class="aksi">
                        <a href="editBarang.php?id=<?php echo $d['id']; ?>" class="btn-edit">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <a href="hapusBarang.php?id=<?php echo $d['id']; ?>" class="btn-hapus" onclick="return confirm('Yakin hapus?')">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                </div>

            </div>

        <?php } ?>

    </div>

    <!-- Form Stok Barang -->
    <div id="form-barang" class="form-barang">
        <div class="form-barang-content">
            <span class="close" onclick="tutupForm()">&times;</span>
            <h2>Tambah Barang</h2>

            <form action="simpanBarang.php" method="POST">

                <label>Nama Barang</label>
                <input type="text" name="nama_barang" required>

                <label>Jumlah Stok</label>
                <input type="number" name="jumlah_barang" step="0.01" required>

                <label>Satuan</label>
                <select name="satuan" id="satuan" onchange="toggleDus()" required>
                    <option value="kg">Kg</option>
                    <option value="pcs">Pcs</option>
                    <option value="dus">Dus</option>
                </select>

                <div id="fieldDus" style="display:none;">
                    <label>Isi per dus</label>
                    <input type="number" name="isi_per_dus" id="isi_per_dus">
                </div>

                <label>Harga</label>
                <input type="number" id="harga" name="harga" required>

                <label>Tanggal Expired</label>
                <input type="date" name="tgl_expired">

                <button type="submit" class="btn-simpan">Simpan</button>

            </form>
        </div>
    </div>

    <button class="fab" onclick="bukaForm()">
        <i class="fa-solid fa-plus"></i>
    </button>
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

        function bukaForm() {
            document.getElementById("form-barang").style.display = "block";
        }

        function tutupForm() {
            document.getElementById("form-barang").style.display = "none";
        }

        window.onclick = function(event) {
            let formBarang = document.getElementById("form-barang");
            if (event.target == formBarang) {
                formBarang.style.display = "none";
            }
        }

        function toggleDus() {
            let satuan = document.getElementById("satuan").value;
            let fieldDus = document.getElementById("fieldDus");
            let isiDus = document.getElementById("isi_per_dus");

            if (satuan === "dus") {
                fieldDus.style.display = "block";
                isiDus.required = true;
            } else {
                fieldDus.style.display = "none";
                isiDus.required = false;
                isiDus.value = ""; // 🔥 RESET
            }
        }
    </script>
    <!-- JS END -->
</body>

</html>