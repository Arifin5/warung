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

    $data = mysqli_query($koneksi, "SELECT * FROM list_barang ORDER BY nama_barang ASC");
    ?>
    <!-- PHP END -->

    <!-- NAVBAR START -->
    <div class="container-navbar">
        <div class="strip-navbar" onclick="stripNavbar()">≡</div>
        <h1 class="h1-navbar">TRANSAKSI</h1>
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
    <div class="container-main">

        <div class="barang">
            <h3>Daftar Barang</h3>

            <?php while ($d = mysqli_fetch_array($data)) { ?>
                <div class="item"
                    onclick="tambahBarang(
                <?php echo $d['id']; ?>,
                '<?php echo $d['nama_barang']; ?>',
                <?php echo $d['harga']; ?>,
                '<?php echo $d['jenis_satuan']; ?>'
                )">

                    <strong><?php echo $d['nama_barang']; ?></strong><br>

                    <small>
                        Stok:
                        <?php
                        if ($d['jenis_satuan'] == 'kg') {
                            echo number_format($d['jumlah_barang'], 2) . " kg";
                        } else {
                            echo $d['jumlah_barang'] . " pcs";
                        }
                        ?>
                    </small><br>

                    <span class="harga">
                        Rp <?php echo number_format($d['harga']); ?>
                    </span>
                </div>
            <?php } ?>
        </div>

        <div class="keranjang">
            <h3>Keranjang</h3>

            <form action="prosesKasir.php" method="POST" id="formKasir">
                <table id="tableKeranjang"></table>

                <div class="total-box">
                    <p>Total: Rp <span id="total">0</span></p>
                    <p>Kembalian: Rp <span id="kembalian">0</span></p>
                </div>

                <div id="inputCash" style="display:none; margin-top:10px;">
                    <label>Uang Bayar</label>
                    <input type="number" id="bayar" placeholder="Masukkan uang" oninput="hitungKembalian()">
                </div>

                <input type="hidden" name="bayar" id="bayarInput">
                <input type="hidden" name="data" id="dataInput">
                <input type="hidden" name="metode" id="metode">

                <button type="button" onclick="pilihPembayaran()">Bayar</button>
            </form>
        </div>

    </div>

    <div id="popupBayar" class="popup-overlay">

        <div class="popup-box">
            <h3>Pilih Pembayaran</h3>

            <button onclick="submitBayar('Cash')" class="btn bayar cash">💵 Cash</button>
            <button onclick="submitBayar('QRIS')" class="btn bayar qris">📱 QRIS</button>
            <button onclick="submitBayar('DANA')" class="btn bayar dana">🟢 DANA</button>
            <button onclick="submitBayar('GOPAY')" class="btn bayar gopay">🟢 GoPay</button>

            <button onclick="tutupPopup()" class="btn batal">Batal</button>
        </div>

    </div>

    <div id="popupQR" class="popup-overlay">

        <div class="popup-box qr">
            <h3>Scan QRIS</h3>

            <img id="imgQR" src=""><br>

            <p id="statusQR">Menunggu pembayaran...</p>

            <button onclick="batalQR()" class="btn batal">Batal</button>
        </div>

    </div>

    <div id="popupCash" class="popup-overlay">
        <div class="popup-box cash">

            <h3>Pembayaran Cash</h3>

            <div class="cash-total">
                Total: <span id="totalCash">Rp 0</span>
            </div>

            <input type="number" id="inputCashBayar" placeholder="Masukkan uang">

            <div class="quick-money">
                <button onclick="setUang(5000)">5K</button>
                <button onclick="setUang(10000)">10K</button>
                <button onclick="setUang(20000)">20K</button>
                <button onclick="setUang(50000)">50K</button>
                <button onclick="setUang(100000)">100K</button>
            </div>

            <div class="cash-kembali">
                Kembalian: <span id="kembaliCash">Rp 0</span>
            </div>

            <button class="btn bayar cash" onclick="prosesCash()">Bayar</button>
            <button class="btn batal" onclick="tutupCash()">Batal</button>

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

        let keranjang = [];

        function tambahBarang(id, nama, harga, jenis) {

            let msg = (jenis === 'kg') ?
                "Masukkan berat (kg)" :
                "Masukkan jumlah (pcs)";

            let qty = prompt(msg, jenis === 'kg' ? "0.25" : "1");

            qty = parseFloat(qty);

            if (isNaN(qty) || qty <= 0) return;

            let item = keranjang.find(i => i.id == id);

            if (item) {
                item.qty += qty;
            } else {
                keranjang.push({
                    id,
                    nama,
                    harga,
                    qty
                });
            }

            render();
        }

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        function render() {
            let table = document.getElementById("tableKeranjang");
            table.innerHTML = "";

            let total = 0;

            keranjang.forEach((item, index) => {
                let subtotal = item.qty * item.harga;
                total += subtotal;

                table.innerHTML += `
            <tr>
                <td>${item.nama}</td>
                <td>
                    <input type="number" step="0.01" min="0.01" value="${item.qty}"
                        onchange="ubahQty(${index}, this.value)" 
                        style="width:60px;">
                </td>
                <td>Rp ${formatRupiah(item.harga)}</td>
                <td>Rp ${formatRupiah(subtotal)}</td>
                <td>
                    <button type="button" onclick="hapus(${index})">x</button>
                </td>
            </tr>
            `;
            });

            document.getElementById("total").innerText = formatRupiah(total);
            document.getElementById("dataInput").value = JSON.stringify(keranjang);
        }

        function ubahQty(index, value) {
            let qty = parseFloat(value);

            if (qty <= 0 || isNaN(qty)) {
                keranjang.splice(index, 1);
            } else {
                keranjang[index].qty = qty;
            }

            render();
        }

        function hapus(index) {
            keranjang.splice(index, 1);
            render();
        }

        function hitungKembalian() {
            let total = 0;

            keranjang.forEach(item => {
                total += item.qty * item.harga;
            });

            let bayar = parseFloat(document.getElementById("bayar").value) || 0;
            let kembalian = bayar - total;

            document.getElementById("kembalian").innerText =
                new Intl.NumberFormat('id-ID').format(kembalian > 0 ? kembalian : 0);

            document.getElementById("bayarInput").value = bayar;
        }

        document.getElementById("formKasir").addEventListener("submit", function(e) {

            document.getElementById("dataInput").value =
                JSON.stringify(keranjang);

            let metode = document.getElementById("metode").value;
            let total = keranjang.reduce((a, b) => a + (b.qty * b.harga), 0);
            let bayar = parseFloat(document.getElementById("bayar").value) || 0;

            if (metode === "Cash" && bayar < total) {
                alert("Uang tidak cukup!");
                e.preventDefault();
                return;
            }
        });

        function pilihPembayaran() {

            if (keranjang.length === 0) {
                alert("Keranjang kosong!");
                return;
            }

            document.getElementById("popupBayar").style.display = "flex";
        }

        function submitBayar(metode) {

            document.getElementById("metode").value = metode;

            let total = keranjang.reduce((a, b) => a + (b.qty * b.harga), 0);

            // ================= CASH =================
            if (metode === "Cash") {

                document.getElementById("popupBayar").style.display = "none";
                document.getElementById("popupCash").style.display = "flex";

                document.getElementById("totalCash").innerText =
                    "Rp " + formatRupiah(total);

                document.getElementById("inputCashBayar").value = "";
                document.getElementById("kembaliCash").innerText = "Rp 0";

                setTimeout(() => {
                    document.getElementById("inputCashBayar").focus();
                }, 100);

                return;
            }

            // ================= NON CASH =================
            document.getElementById("bayarInput").value = total;

            let formData = new FormData(document.getElementById("formKasir"));

            fetch("prosesKasir.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(res => {

                    if (res.error) {
                        alert(res.error);
                        return;
                    }

                    document.getElementById("popupQR").style.display = "flex";
                    document.getElementById("imgQR").src = res.qr;

                    cekStatusRealtime(res.kode);
                });
        }

        function tutupPopup() {
            document.getElementById("popupBayar").style.display = "none";
        }

        let intervalQR;

        function cekStatusRealtime(kode) {

            intervalQR = setInterval(() => {

                fetch("cek_status.php?kode=" + kode)
                    .then(res => res.text())
                    .then(res => {

                        if (res === "lunas") {

                            clearInterval(intervalQR);

                            document.getElementById("statusQR").innerText = "Pembayaran berhasil ✅";

                            setTimeout(() => {
                                window.location.href = "struk.php?kode=" + kode;
                            }, 1500);
                        }

                    });

            }, 3000);
        }

        function setUang(nominal) {
            document.getElementById("inputCashBayar").value = nominal;
            hitungKembaliCash();
        }

        function hitungKembaliCash() {

            let total = keranjang.reduce((a, b) => a + (b.qty * b.harga), 0);
            let bayar = parseFloat(document.getElementById("inputCashBayar").value) || 0;

            let kembali = bayar - total;

            document.getElementById("kembaliCash").innerText =
                "Rp " + formatRupiah(kembali > 0 ? kembali : 0);
        }

        function prosesCash() {

            let total = keranjang.reduce((a, b) => a + (b.qty * b.harga), 0);
            let bayar = parseFloat(document.getElementById("inputCashBayar").value) || 0;

            if (bayar < total) {
                alert("Uang tidak cukup!");
                return;
            }

            document.getElementById("bayarInput").value = bayar;

            document.getElementById("formKasir").submit();
        }

        function tutupCash() {
            document.getElementById("popupCash").style.display = "none";
        }

        document.getElementById("inputCashBayar").addEventListener("input", hitungKembaliCash);
    </script>
    <!-- JS END -->
</body>

</html>