<?php

require_once "../config/database.php";

$sql_summary = "
    SELECT
        COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' THEN nominal ELSE 0 END), 0) AS total_pengeluaran,
        COALESCE(COUNT(CASE WHEN jenis = 'Pengeluaran' THEN 1 END), 0) AS total_transaksi,
        COALESCE(MAX(CASE WHEN jenis = 'Pengeluaran' THEN nominal END), 0) AS terbesar,
        COALESCE(AVG(CASE WHEN jenis = 'Pengeluaran' THEN nominal END), 0) AS rata_rata
    FROM transactions
";

$result_summary = $conn->query($sql_summary);
$summary = $result_summary->fetch_assoc();

$sql_categories = "
    SELECT
        kategori,
        COALESCE(SUM(nominal), 0) AS total
    FROM transactions
    WHERE jenis = 'Pengeluaran'
    GROUP BY kategori
    ORDER BY total DESC
";

$result_categories = $conn->query($sql_categories);
$categories = [];
$total_kategori = 0;

while ($row = $result_categories->fetch_assoc()) {
    $total_kategori += (float) $row["total"];
    $categories[] = $row;
}

$sql_transactions = "
    SELECT *
    FROM transactions
    WHERE jenis = 'Pengeluaran'
    ORDER BY tanggal DESC, id DESC
    LIMIT 12
";

$result_transactions = $conn->query($sql_transactions);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengeluaran - Smart Finance</title>
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
            <a href="pengeluaran.php" class="nav-item active">
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
            <div class="avatar">AD</div>
            <div>
                <strong>Admin</strong>
                <span>Bandarh</span>
            </div>
        </div>
    </aside>

    <main class="content-panel expense-page">
        <header class="income-header expense-header">
            <div>
                <h1>Pengeluaran</h1>
                <p>Masjid Al-Ikhlas — September 2026</p>
            </div>
        </header>

        <section class="stats-grid income-stats-grid">
            <article class="kpi-card expense">
                <div class="kpi-top">
                    <span class="kpi-label">Total Pengeluaran</span>
                    <span class="kpi-pill">↑</span>
                </div>
                <div class="kpi-value">Rp <?= number_format((float) $summary["total_pengeluaran"], 0, ',', '.') ?></div>
                <div class="kpi-trend negative"><span><?= $summary["total_transaksi"] ?></span> transaksi</div>
            </article>

            <article class="kpi-card expense">
                <div class="kpi-top">
                    <span class="kpi-label">Terbesar</span>
                    <span class="kpi-pill">↑</span>
                </div>
                <div class="kpi-value">Rp <?= number_format((float) $summary["terbesar"], 0, ',', '.') ?></div>
                <div class="kpi-trend negative">nominal tertinggi</div>
            </article>

            <article class="kpi-card balance">
                <div class="kpi-top">
                    <span class="kpi-label">Rata-rata</span>
                    <span class="kpi-pill">◌</span>
                </div>
                <div class="kpi-value">Rp <?= number_format((float) $summary["rata_rata"], 0, ',', '.') ?></div>
                <div class="kpi-trend neutral">per transaksi</div>
            </article>
        </section>

        <section class="income-breakdown section expense-breakdown">
            <h3>KOMPOSISI PER KATEGORI</h3>

            <?php foreach ($categories as $category): ?>
                <?php $width = $total_kategori > 0 ? (($category["total"] / $total_kategori) * 100) : 0; ?>
                <?php
                    $barColor = "#ff637e";
                    if ($category["kategori"] === "Operasional") $barColor = "#2b7fff";
                    if ($category["kategori"] === "Perbaikan") $barColor = "#f59e0b";
                    if ($category["kategori"] === "Donasi") $barColor = "#22c55e";
                    if ($category["kategori"] === "Lainnya") $barColor = "#f97316";
                    if ($category["kategori"] === "Listrik & Air") $barColor = "#38bdf8";
                    $categoryLabel = htmlspecialchars($category["kategori"]);
                    $categoryClass = strtolower(str_replace([' ', '&'], ['-', '-'], $category["kategori"]));
                ?>
                <div class="breakdown-row">
                    <div class="breakdown-label">
                        <span><?= $categoryLabel ?></span>
                    </div>
                    <div class="breakdown-track">
                        <div class="breakdown-bar" style="width: <?= $width ?>%; background: <?= $barColor ?>;"></div>
                    </div>
                    <div class="breakdown-amount">Rp <?= number_format((float) $category["total"], 0, ',', '.') ?> (<?= number_format($width, 0) ?>%)</div>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="income-table section expense-table">
            <div class="income-table-toolbar">
                <input type="text" value="Cari deskripsi..." aria-label="Cari deskripsi" readonly>
                <div class="income-table-actions">
                    <button type="button">Semua</button>
                    <button type="button">Semua Bulan</button>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>TANGGAL</th>
                        <th>DESKRIPSI</th>
                        <th>KATEGORI</th>
                        <th>NOMINAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_transactions->fetch_assoc()): ?>
                        <?php $rowCategoryClass = strtolower(str_replace([' ', '&'], ['-', '-'], $row["kategori"])); ?>
                        <tr>
                            <td><?= htmlspecialchars($row["tanggal"]) ?></td>
                            <td><?= htmlspecialchars($row["deskripsi"]) ?></td>
                            <td>
                                <span class="kategori-badge category-<?= $rowCategoryClass ?>">
                                    <?= htmlspecialchars($row["kategori"]) ?>
                                </span>
                            </td>
                            <td class="expense-text">- Rp <?= number_format((float) $row["nominal"], 0, ',', '.') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

</body>
</html>
