<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'koneksi.php';

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan!");
}

$id = (int) $_GET['id'];

$data = mysqli_query($koneksi, "SELECT * FROM list_barang WHERE id=$id");

if (!$data) {
    die("Query error: " . mysqli_error($koneksi));
}

$d = mysqli_fetch_array($data);

if (!$d) {
    die("Data tidak ditemukan!");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Stok Barang</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>

    <div id="form-barang" class="form-barang" style="display:block;">
        <div class="form-barang-content">
            <h2>Edit Stok Barang</h2>

            <form action="updateBarang.php?id=<?php echo $id; ?>" method="POST">

                <label>Nama Barang</label>
                <input type="text" name="nama_barang" value="<?php echo $d['nama_barang']; ?>" required>

                <label>Jumlah Stok</label>
                <input type="number" name="jumlah_barang" value="<?php echo $d['jumlah_barang']; ?>" step="0.01" required>

                <label>Satuan</label>
                <select name="satuan" id="satuan" onchange="toggleDus()" required>
                    <option value="kg" <?php if ($d['jenis_satuan'] == 'kg') echo 'selected'; ?>>Kg</option>
                    <option value="pcs" <?php if ($d['jenis_satuan'] == 'pcs' && empty($d['isi_per_dus'])) echo 'selected'; ?>>Pcs</option>
                    <option value="dus" <?php if (!empty($d['isi_per_dus'])) echo 'selected'; ?>>Dus</option>
                </select>

                <div id="fieldDus" style="display:none;">
                    <label>Isi per dus</label>
                    <input type="number" name="isi_per_dus" id="isi_per_dus"
                        value="<?php echo !empty($d['isi_per_dus']) ? $d['isi_per_dus'] : ''; ?>">
                </div>

                <label>Harga</label>
                <input type="number" name="harga"
                    value="<?php echo !empty($d['harga']) ? $d['harga'] : ''; ?>" required>

                <label>Tanggal Expired</label>
                <input type="date" name="tgl_expired"
                    value="<?php echo !empty($d['tgl_expired']) ? $d['tgl_expired'] : ''; ?>">

                <button type="submit" name="submit">Update</button>

            </form>
        </div>
    </div>
    <script>
        function toggleDus() {
            let satuan = document.getElementById("satuan").value;
            let field = document.getElementById("fieldDus");

            field.style.display = (satuan === "dus") ? "block" : "none";
        }

        // supaya langsung jalan saat halaman dibuka
        toggleDus();
    </script>
</body>

</html>