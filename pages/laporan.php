<?php

require_once "../config/database.php";

$months = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date("Y-m", strtotime("-$i months"));
    $months[] = $month;
}

$summary = [
    "pemasukan" => 0,
    "pengeluaran" => 0,
    "total_transaksi" => 0,
    "surplus" => 0,
];

$sql_total = "
    SELECT
        COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' THEN nominal ELSE 0 END), 0) AS pemasukan,
        COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' THEN nominal ELSE 0 END), 0) AS pengeluaran,
        COUNT(*) AS total_transaksi
    FROM transactions
";

$result_total = $conn->query($sql_total);
if ($result_total && $row = $result_total->fetch_assoc()) {
    $summary["pemasukan"] = (float) $row["pemasukan"];
    $summary["pengeluaran"] = (float) $row["pengeluaran"];
    $summary["total_transaksi"] = (int) $row["total_transaksi"];
    $summary["surplus"] = $summary["pemasukan"] - $summary["pengeluaran"];
}

$chart_data = [];
foreach ($months as $month) {
    $chart_data[$month] = [
        "pemasukan" => 0,
        "pengeluaran" => 0,
        "surplus" => 0,
    ];
}

$sql_chart = "
    SELECT
        DATE_FORMAT(tanggal, '%Y-%m') AS bulan,
        SUM(CASE WHEN jenis = 'Pemasukan' THEN nominal ELSE 0 END) AS pemasukan,
        SUM(CASE WHEN jenis = 'Pengeluaran' THEN nominal ELSE 0 END) AS pengeluaran
    FROM transactions
    WHERE tanggal >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
    GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
    ORDER BY bulan ASC
";

$result_chart = $conn->query($sql_chart);
while ($row = $result_chart->fetch_assoc()) {
    if (isset($chart_data[$row["bulan"]])) {
        $chart_data[$row["bulan"]]["pemasukan"] = (float) $row["pemasukan"];
        $chart_data[$row["bulan"]]["pengeluaran"] = (float) $row["pengeluaran"];
        $chart_data[$row["bulan"]]["surplus"] = $row["pemasukan"] - $row["pengeluaran"];
    }
}

$category_pemasukan = [];
$sql_income_category = "
    SELECT kategori, COALESCE(SUM(nominal), 0) AS total
    FROM transactions
    WHERE jenis = 'Pemasukan'
    GROUP BY kategori
    ORDER BY total DESC
";
$result_income_cat = $conn->query($sql_income_category);
while ($row = $result_income_cat->fetch_assoc()) {
    $category_pemasukan[] = $row;
}

$category_pengeluaran = [];
$sql_expense_category = "
    SELECT kategori, COALESCE(SUM(nominal), 0) AS total
    FROM transactions
    WHERE jenis = 'Pengeluaran'
    GROUP BY kategori
    ORDER BY total DESC
";
$result_expense_cat = $conn->query($sql_expense_category);
while ($row = $result_expense_cat->fetch_assoc()) {
    $category_pengeluaran[] = $row;
}

$income_total_category = array_sum(array_column($category_pemasukan, 'total')) ?: 1;
$expense_total_category = array_sum(array_column($category_pengeluaran, 'total')) ?: 1;

$monthly_rows = [];
foreach ($months as $month) {
    $month_start = date('Y-m-01', strtotime($month . '-01'));
    $month_end = date('Y-m-t', strtotime($month . '-01'));

    $sql_month = "
        SELECT
            COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' THEN nominal ELSE 0 END), 0) AS pemasukan,
            COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' THEN nominal ELSE 0 END), 0) AS pengeluaran
        FROM transactions
        WHERE tanggal BETWEEN ? AND ?
    ";

    $stmt = $conn->prepare($sql_month);
    $stmt->bind_param('ss', $month_start, $month_end);
    $stmt->execute();
    $month_row = $stmt->get_result()->fetch_assoc();

    $monthly_rows[] = [
        'bulan' => date('M Y', strtotime($month . '-01')),
        'pemasukan' => (float) $month_row['pemasukan'],
        'pengeluaran' => (float) $month_row['pengeluaran'],
        'surplus' => (float) $month_row['pemasukan'] - (float) $month_row['pengeluaran'],
    ];
}

$income_colors = ['#8b5cf6', '#10b981', '#2dd4bf'];
$expense_colors = ['#f97316', '#f59e0b', '#38bdf8'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="app-shell dashboard-layout">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">M</div>
            <div class="brand-info">
                <strong>Masjid Al-Ikhlas</strong>
                <span>Manajemen Keuangan</span>
            </div>
        </div>

        <nav class="nav">
            <a href="dashboard.php" class="nav-item">
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
            <a href="laporan.php" class="nav-item active">
                <span class="nav-icon">◌</span>
                <span>Laporan</span>
            </a>
            <a href="pengaturan.php" class="nav-item">
                <span class="nav-icon">⚑</span>
                <span>Pengaturan</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="avatar">AD</div>
            <div>
                <strong>Admin</strong>
                <span>Bandarh</span>
            </div>
        </div>
    </aside>

    <main class="content-panel report-content">
        <header class="report-header">
            <div>
                <h1>Laporan Keuangan</h1>
                <p>Masjid Al-Ikhlas — September 2026</p>
            </div>
            <button class="report-export-btn" type="button">Export PDF</button>
        </header>

        <section class="report-summary-grid">
            <article class="report-card report-card-income">
                <div class="report-card-label">Total Pemasukan</div>
                <div class="report-card-value">Rp <?= number_format($summary['pemasukan'], 0, ',', '.') ?></div>
            </article>

            <article class="report-card report-card-expense">
                <div class="report-card-label">Total Pengeluaran</div>
                <div class="report-card-value">Rp <?= number_format($summary['pengeluaran'], 0, ',', '.') ?></div>
            </article>

            <article class="report-card report-card-balance">
                <div class="report-card-label">Surplus Bersih</div>
                <div class="report-card-value">Rp <?= number_format($summary['surplus'], 0, ',', '.') ?></div>
            </article>

            <article class="report-card report-card-transactions">
                <div class="report-card-label">Total Transaksi</div>
                <div class="report-card-value transactions-count"><?= $summary['total_transaksi'] ?> <span>transaksi</span></div>
            </article>
        </section>

        <section class="report-chart-panel section">
            <div class="report-chart-head">
                <h2>Tren Keuangan 6 Bulan</h2>
                <div class="chart-toggle">
                    <button class="toggle-btn active" type="button">Bar</button>
                    <button class="toggle-btn" type="button">Line</button>
                </div>
            </div>

            <div class="bar-chart">
                <?php foreach ($chart_data as $month => $values): ?>
                    <?php
                        $maxValue = max(array_column($chart_data, 'pemasukan')) ?: 1;
                        $maxValue = max($maxValue, max(array_column($chart_data, 'pengeluaran')) ?: 1);
                        $incomeHeight = $maxValue > 0 ? ($values['pemasukan'] / $maxValue) * 100 : 0;
                        $expenseHeight = $maxValue > 0 ? ($values['pengeluaran'] / $maxValue) * 100 : 0;
                        $label = date('M', strtotime($month . '-01'));
                    ?>
                    <div class="bar-chart-item">
                        <div class="bar-stack">
                            <span class="bar bar-income" style="height: <?= $incomeHeight ?>%"></span>
                            <span class="bar bar-expense" style="height: <?= $expenseHeight ?>%"></span>
                        </div>
                        <span class="bar-label"><?= $label ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="report-donut-grid">
            <div class="donut-panel section">
                <h3>Komposisi Pemasukan</h3>
                <div class="donut-wrap">
                    <div class="donut donut-income" style="background: conic-gradient(#8b5cf6 0 74%, #10b981 74% 98%, #2dd4bf 98% 100%);">
                        <span class="donut-center"></span>
                    </div>
                    <ul class="donut-legend">
                        <?php foreach ($category_pemasukan as $index => $item): ?>
                            <?php $percent = $income_total_category > 0 ? ($item['total'] / $income_total_category) * 100 : 0; ?>
                            <li>
                                <span class="legend-dot" style="background: <?= $income_colors[$index % count($income_colors)] ?>;"></span>
                                <span class="legend-name"><?= htmlspecialchars($item['kategori']) ?></span>
                                <span class="legend-value"><?= number_format($percent, 0) ?>%</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="donut-panel section">
                <h3>Komposisi Pengeluaran</h3>
                <div class="donut-wrap">
                    <div class="donut donut-expense" style="background: conic-gradient(#f97316 0 32%, #f59e0b 32% 48%, #38bdf8 48% 100%);">
                        <span class="donut-center"></span>
                    </div>
                    <ul class="donut-legend">
                        <?php foreach ($category_pengeluaran as $index => $item): ?>
                            <?php $percent = $expense_total_category > 0 ? ($item['total'] / $expense_total_category) * 100 : 0; ?>
                            <li>
                                <span class="legend-dot" style="background: <?= $expense_colors[$index % count($expense_colors)] ?>;"></span>
                                <span class="legend-name"><?= htmlspecialchars($item['kategori']) ?></span>
                                <span class="legend-value"><?= number_format($percent, 0) ?>%</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </section>

        <section class="report-table-panel section">
            <h3>Rekap Bulanan</h3>
            <table>
                <thead>
                    <tr>
                        <th>BULAN</th>
                        <th>PEMASUKAN</th>
                        <th>PENGELUARAN</th>
                        <th>SURPLUS / DEFISIT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthly_rows as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['bulan']) ?></td>
                            <td class="income-text">+ Rp <?= number_format($item['pemasukan'], 0, ',', '.') ?></td>
                            <td class="expense-text">- Rp <?= number_format($item['pengeluaran'], 0, ',', '.') ?></td>
                            <td class="<?= $item['surplus'] >= 0 ? 'income-text' : 'expense-text' ?>">
                                <?= $item['surplus'] >= 0 ? '+' : '-' ?> Rp <?= number_format(abs($item['surplus']), 0, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

</body>
</html>
