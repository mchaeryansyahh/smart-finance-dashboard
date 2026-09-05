<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Total pemasukan bulan ini
|--------------------------------------------------------------------------
*/

$sql_pemasukan = "
    SELECT COALESCE(SUM(nominal), 0) AS total
    FROM transactions
    WHERE jenis = 'Pemasukan'
    AND MONTH(tanggal) = MONTH(CURDATE())
    AND YEAR(tanggal) = YEAR(CURDATE())
";

$result_pemasukan = $conn->query($sql_pemasukan);
$pemasukan = $result_pemasukan->fetch_assoc()["total"];


/*
|--------------------------------------------------------------------------
| Total pengeluaran bulan ini
|--------------------------------------------------------------------------
*/

$sql_pengeluaran = "
    SELECT COALESCE(SUM(nominal), 0) AS total
    FROM transactions
    WHERE jenis = 'Pengeluaran'
    AND MONTH(tanggal) = MONTH(CURDATE())
    AND YEAR(tanggal) = YEAR(CURDATE())
";

$result_pengeluaran = $conn->query($sql_pengeluaran);
$pengeluaran = $result_pengeluaran->fetch_assoc()["total"];


/*
|--------------------------------------------------------------------------
| Saldo keseluruhan
|--------------------------------------------------------------------------
*/

$sql_saldo = "
    SELECT
        COALESCE(
            SUM(
                CASE
                    WHEN jenis = 'Pemasukan' THEN nominal
                    WHEN jenis = 'Pengeluaran' THEN -nominal
                END
            ),
            0
        ) AS saldo
    FROM transactions
";

$result_saldo = $conn->query($sql_saldo);
$saldo = $result_saldo->fetch_assoc()["saldo"];

/*
|--------------------------------------------------------------------------
| Transaksi terbaru
|--------------------------------------------------------------------------
*/

$sql_transaksi = "
    SELECT *
    FROM transactions
    ORDER BY tanggal DESC, id DESC
    LIMIT 10
";

$result_transaksi = $conn->query($sql_transaksi);

/*
|--------------------------------------------------------------------------
| Data grafik 6 bulan terakhir
|--------------------------------------------------------------------------
*/

$chart_data = [];

for ($i = 5; $i >= 0; $i--) {

    $bulan = date("Y-m", strtotime("-$i months"));

    $chart_data[$bulan] = [
        "pemasukan" => 0,
        "pengeluaran" => 0
    ];
}


$sql_chart = "
    SELECT
        DATE_FORMAT(tanggal, '%Y-%m') AS bulan,
        jenis,
        SUM(nominal) AS total
    FROM transactions
    WHERE tanggal >= DATE_FORMAT(
        DATE_SUB(CURDATE(), INTERVAL 5 MONTH),
        '%Y-%m-01'
    )
    GROUP BY bulan, jenis
    ORDER BY bulan ASC
";

$result_chart = $conn->query($sql_chart);


while ($row = $result_chart->fetch_assoc()) {

    if (isset($chart_data[$row["bulan"]])) {

        if ($row["jenis"] == "Pemasukan") {

            $chart_data[$row["bulan"]]["pemasukan"]
                = $row["total"];

        } else {

            $chart_data[$row["bulan"]]["pengeluaran"]
                = $row["total"];

        }
    }
}
?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard Keuangan</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>

<div class="container">


    <!-- HEADER -->

    <div class="header">

        <div>

            <h1>Dashboard Keuangan</h1>

            <p>
                Masjid Al-Ikhlas — ringkasan
                <?= date("F Y") ?>
            </p>

        </div>

        <a
            href="transaksi.php"
            class="btn"
        >
            + Tambah Transaksi
        </a>

    </div>


    <!-- CARDS -->

    <div class="cards">


        <div class="card income">

            <div class="card-title">
                Pemasukan bulan ini
            </div>

            <div class="card-value">
                Rp <?= number_format(
                    $pemasukan,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

        </div>


        <div class="card expense">

            <div class="card-title">
                Pengeluaran bulan ini
            </div>

            <div class="card-value">
                Rp <?= number_format(
                    $pengeluaran,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

        </div>


        <div class="card balance">

            <div class="card-title">
                Saldo kas
            </div>

            <div class="card-value">
                Rp <?= number_format(
                    $saldo,
                    0,
                    ',',
                    '.'
                ) ?>
            </div>

        </div>

    </div>

    <!-- GRAFIK 6 BULAN -->

<div class="section">

    <h2>Tren Keuangan 6 Bulan</h2>

    <div class="chart-legend">

        <span class="legend-income">
            ● Pemasukan
        </span>

        <span class="legend-expense">
            ● Pengeluaran
        </span>

    </div>


    <div class="chart-container">

        <?php foreach ($chart_data as $bulan => $data): ?>

            <?php

            $max_value = max(
                $data["pemasukan"],
                $data["pengeluaran"],
                1
            );

            $income_height =
                ($data["pemasukan"] / $max_value) * 200;

            $expense_height =
                ($data["pengeluaran"] / $max_value) * 200;

            $timestamp = strtotime($bulan . "-01");

            $nama_bulan = date("M", $timestamp);

            ?>

            <div class="chart-item">

                <div
                    class="bar income-bar"
                    style="height: <?= $income_height ?>px;"
                    title="Pemasukan: Rp <?= number_format(
                        $data["pemasukan"],
                        0,
                        ",",
                        "."
                    ) ?>"
                ></div>


                <div
                    class="bar expense-bar"
                    style="height: <?= $expense_height ?>px;"
                    title="Pengeluaran: Rp <?= number_format(
                        $data["pengeluaran"],
                        0,
                        ",",
                        "."
                    ) ?>"
                ></div>


                <span class="month-label">

                    <?= $nama_bulan ?>

                </span>

            </div>

        <?php endforeach; ?>

    </div>

</div>


    <!-- TRANSAKSI TERBARU -->

    <div class="section">

        <h2>Transaksi Terbaru</h2>

        <table>

            <thead>

                <tr>

                    <th>Tanggal</th>

                    <th>Deskripsi</th>

                    <th>Kategori</th>

                    <th>Nominal</th>

                </tr>

            </thead>


            <tbody>

            <?php while (
                $row = $result_transaksi->fetch_assoc()
            ): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars(
                            $row["tanggal"]
                        ) ?>
                    </td>


                    <td>
                        <?= htmlspecialchars(
                            $row["deskripsi"]
                        ) ?>
                    </td>


                    <td>
                        <?= htmlspecialchars(
                            $row["kategori"]
                        ) ?>
                    </td>


                    <td class="<?=
                        $row["jenis"] === "Pemasukan"
                        ? "income-text"
                        : "expense-text"
                    ?>">

                        <?= $row["jenis"] === "Pemasukan"
                            ? "+"
                            : "-"
                        ?>

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

    </div>


</div>

</body>

</html>