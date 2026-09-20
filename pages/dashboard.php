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

$sql_bulan_lalu = "
    SELECT
        COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' THEN nominal ELSE 0 END), 0) AS pemasukan,
        COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' THEN nominal ELSE 0 END), 0) AS pengeluaran
    FROM transactions
    WHERE MONTH(tanggal) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
    AND YEAR(tanggal) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
";

$result_bulan_lalu = $conn->query($sql_bulan_lalu);
$bulan_lalu = $result_bulan_lalu ? $result_bulan_lalu->fetch_assoc() : [];
$pemasukan_bulan_lalu = (float) ($bulan_lalu["pemasukan"] ?? 0);
$pengeluaran_bulan_lalu = (float) ($bulan_lalu["pengeluaran"] ?? 0);

$persen_pemasukan = $pemasukan_bulan_lalu > 0
    ? (($pemasukan - $pemasukan_bulan_lalu) / $pemasukan_bulan_lalu) * 100
    : ($pemasukan > 0 ? 100 : 0);

$persen_pengeluaran = $pengeluaran_bulan_lalu > 0
    ? (($pengeluaran - $pengeluaran_bulan_lalu) / $pengeluaran_bulan_lalu) * 100
    : ($pengeluaran > 0 ? 100 : 0);

$format_persen = static function (float $persen): string {
    $nilai = number_format(abs($persen), 1, ',', '.');
    return ($persen > 0 ? '+' : ($persen < 0 ? '-' : '')) . $nilai . '%';
};


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

<div class="app-shell">

    <aside class="sidebar">

        <div class="brand">
            <div class="brand-mark">M</div>
            <div class="brand-info">
                <strong>Masjid Al-Ikhlas</strong>
                <span>Dashboard Keuangan</span>
            </div>
        </div>

        <nav class="nav">
            <a href="#" class="nav-item active">
                <span class="nav-icon">◫</span>
                <span>Dashboard</span>
            </a>
            <a href="pemasukan.php" class="nav-item">
                <span class="nav-icon">↗</span>
                <span>Pemasukan</span>
            </a>
            <a href="pengeluaran.php" class="nav-item">
                <span class="nav-icon">↘</span>
                <span>Pengeluaran</span>
            </a>
            <a href="laporan.php" class="nav-item">
                <span class="nav-icon">◌</span>
                <span>Laporan</span>
            </a>
            <a href="pengaturan.php" class="nav-item">
                <span class="nav-icon">⚑</span>
                <span>Pengaturan</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="avatar">A</div>
            <div>
                <strong>Admin</strong>
                <span>Masjid Admin</span>
            </div>
        </div>

    </aside>

    <main class="content-panel">

        <header class="topbar">

            <div>
                <p class="eyebrow">Dashboard Keuangan</p>
                <h1>Dashboard</h1>
            </div>

            <div class="header-buttons">
                <a href="scan_nota.php" class="btn btn-ghost">Scan Nota</a>
                <a href="transaksi.php" class="btn btn-primary">+ Tambah Transaksi</a>
            </div>

        </header>

        <section class="stats-grid">

            <article class="kpi-card income">
                <div class="kpi-top">
                    <span class="kpi-label">Pemasukan Bulan Ini</span>
                    <span class="kpi-pill">↑</span>
                </div>
                <div class="kpi-value">Rp <?= number_format($pemasukan, 0, ',', '.') ?></div>
                <div class="kpi-trend <?= $persen_pemasukan >= 0 ? "positive" : "negative" ?>">
                    <?= $format_persen((float) $persen_pemasukan) ?> dari bulan lalu
                </div>
            </article>

            <article class="kpi-card expense">
                <div class="kpi-top">
                    <span class="kpi-label">Pengeluaran Bulan Ini</span>
                    <span class="kpi-pill">↓</span>
                </div>
                <div class="kpi-value">Rp <?= number_format($pengeluaran, 0, ',', '.') ?></div>
                <div class="kpi-trend <?= $persen_pengeluaran <= 0 ? "positive" : "negative" ?>">
                    <?= $format_persen((float) $persen_pengeluaran) ?> dari bulan lalu
                </div>
            </article>

            <article class="kpi-card balance">
                <div class="kpi-top">
                    <span class="kpi-label">Saldo Kas</span>
                    <span class="kpi-pill">◌</span>
                </div>
                <div class="kpi-value">Rp <?= number_format($saldo, 0, ',', '.') ?></div>
                <div class="kpi-trend neutral">Tersedia saat ini</div>
            </article>

        </section>

        <section class="ai-insight">
            <?php include "ai_insight.php"; ?>
        </section>

        <section class="chart-panel section">
            <div class="panel-header">
                <h2>Tren Keuangan 6 Bulan</h2>
                <span class="panel-meta">Apr - Sep 2025</span>
            </div>

            <div class="chart-legend">
                <span class="legend-income"><span class="legend-dot"></span>Pemasukan</span>
                <span class="legend-expense"><span class="legend-dot"></span>Pengeluaran</span>
            </div>

            <div class="chart-container">
                <?php foreach ($chart_data as $bulan => $data): ?>
                    <?php
                    $max_value = max($data["pemasukan"], $data["pengeluaran"], 1);
                    $income_height = ($data["pemasukan"] / $max_value) * 170;
                    $expense_height = ($data["pengeluaran"] / $max_value) * 170;
                    $timestamp = strtotime($bulan . "-01");
                    $nama_bulan = date("M", $timestamp);
                    ?>
                    <div class="chart-item">
                        <div class="bar income-bar" style="height: <?= $income_height ?>px;" title="Pemasukan: Rp <?= number_format($data["pemasukan"], 0, ',', '.') ?>"></div>
                        <div class="bar expense-bar" style="height: <?= $expense_height ?>px;" title="Pengeluaran: Rp <?= number_format($data["pengeluaran"], 0, ',', '.') ?>"></div>
                        <span class="month-label"><?= $nama_bulan ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="table-panel section">
            <div class="panel-header">
                <h2>Transaksi Terbaru</h2>
                <span class="panel-meta">23 transaksi</span>
            </div>

            <div class="table-wrap">
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
                    <?php while ($row = $result_transaksi->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row["tanggal"]) ?></td>
                            <td><?= htmlspecialchars($row["deskripsi"]) ?></td>
                            <td><span class="kategori-badge"><?= htmlspecialchars($row["kategori"]) ?></span></td>
                            <td class="<?= $row["jenis"] === "Pemasukan" ? "income-text" : "expense-text" ?>">
                                <?= $row["jenis"] === "Pemasukan" ? "+" : "-" ?>
                                Rp <?= number_format($row["nominal"], 0, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

</div>

</body>

</html>