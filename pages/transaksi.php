<?php

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tanggal = $_POST["tanggal"];
    $jenis = $_POST["jenis"];
    $kategori = $_POST["kategori"];
    $deskripsi = $_POST["deskripsi"];
    $nominal = $_POST["nominal"];

    $sql = "INSERT INTO transactions 
            (tanggal, jenis, kategori, deskripsi, nominal)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssssd",
        $tanggal,
        $jenis,
        $kategori,
        $deskripsi,
        $nominal
    );

    if ($stmt->execute()) {
        $message = "Transaksi berhasil disimpan.";
    } else {
        $message = "Gagal menyimpan transaksi.";
    }
}

$result = $conn->query(
    "SELECT * FROM transactions ORDER BY tanggal DESC, id DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Transaksi - Smart Finance</title>
</head>

<body>

    <h1>Transaksi Keuangan</h1>

    <?php if (isset($message)): ?>
        <p>
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>


    <h2>Tambah Transaksi</h2>

    <form method="POST">

        <div>
            <label>Tanggal</label>
            <br>

            <input
                type="date"
                name="tanggal"
                required
            >
        </div>

        <br>

        <div>
            <label>Jenis Transaksi</label>
            <br>

            <select name="jenis" required>

                <option value="">
                    -- Pilih Jenis --
                </option>

                <option value="Pemasukan">
                    Pemasukan
                </option>

                <option value="Pengeluaran">
                    Pengeluaran
                </option>

            </select>
        </div>

        <br>

        <div>
            <label>Kategori</label>
            <br>

            <select name="kategori" required>

                <option value="">
                    -- Pilih Kategori --
                </option>

                <option value="Infaq">
                    Infaq
                </option>

                <option value="Donasi">
                    Donasi
                </option>

                <option value="Operasional">
                    Operasional
                </option>

                <option value="Perbaikan">
                    Perbaikan
                </option>

                <option value="Listrik">
                    Listrik & Air
                </option>

                <option value="Kegiatan">
                    Kegiatan
                </option>

                <option value="Lainnya">
                    Lainnya
                </option>

            </select>

        </div>

        <br>

        <div>

            <label>Deskripsi</label>
            <br>

            <textarea
                name="deskripsi"
                rows="3"
                placeholder="Contoh: Pembelian semen dan cat"
            ></textarea>

        </div>

        <br>

        <div>

            <label>Nominal</label>
            <br>

            <input
                type="number"
                name="nominal"
                min="0"
                required
                placeholder="850000"
            >

        </div>

        <br>

        <button type="submit">
            Simpan Transaksi
        </button>

    </form>


    <hr>


    <h2>Daftar Transaksi</h2>

    <table border="1" cellpadding="10">

        <thead>

            <tr>

                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th>Nominal</th>

            </tr>

        </thead>

        <tbody>

            <?php while ($row = $result->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($row["tanggal"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row["jenis"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row["kategori"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row["deskripsi"]) ?>
                    </td>

                    <td>
                        Rp <?= number_format(
                            $row["nominal"],
                            0,
                            ",",
                            "."
                        ) ?>
                    </td>

                </tr>

            <?php endwhile; ?>

        </tbody>

    </table>

</body>

</html>