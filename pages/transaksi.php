<?php

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tanggal = $_POST["tanggal"] ?? date("Y-m-d");
    $jenis = $_POST["jenis"] ?? "Pemasukan";
    $kategori = $_POST["kategori"] ?? "Infaq";
    $deskripsi = trim($_POST["deskripsi"] ?? "");
    $nominal = (float)($_POST["nominal"] ?? 0);

    if ($deskripsi !== "" && $nominal > 0) {
        $sql = "INSERT INTO transactions (tanggal, jenis, kategori, deskripsi, nominal) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssd", $tanggal, $jenis, $kategori, $deskripsi, $nominal);

        if ($stmt->execute()) {
            $message = "Transaksi berhasil disimpan.";
        } else {
            $message = "Gagal menyimpan transaksi.";
        }
    } else {
        $message = "Lengkapi data transaksi terlebih dahulu.";
    }
}

$result = $conn->query("SELECT * FROM transactions ORDER BY tanggal DESC, id DESC LIMIT 18");

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Transaksi - Smart Finance</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="app-shell dashboard-layout">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">M</div>
            <div class="brand-info">
                <strong>Masjid Al-Ikhlas</strong>
                <span>Dashboard Keuangan</span>
            </div>
        </div>

        <nav class="nav">
            <a href="dashboard.php" class="nav-item active">
                <span class="nav-icon">◫</span>
                <span>Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">↗</span>
                <span>Pemasukan</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">↘</span>
                <span>Pengeluaran</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">◌</span>
                <span>Laporkan</span>
            </a>
            <a href="#" class="nav-item">
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
                    <span class="kpi-pill">↓</span>
                </div>
                <div class="kpi-value">Rp 1.000.000</div>
                <div class="kpi-trend positive">+12% dari bulan lalu</div>
            </article>

            <article class="kpi-card expense">
                <div class="kpi-top">
                    <span class="kpi-label">Pengeluaran Bulan Ini</span>
                    <span class="kpi-pill">↑</span>
                </div>
                <div class="kpi-value">Rp 9.786.000</div>
                <div class="kpi-trend negative">+156% dari bulan lalu</div>
            </article>

            <article class="kpi-card balance">
                <div class="kpi-top">
                    <span class="kpi-label">Saldo Kas</span>
                    <span class="kpi-pill">◌</span>
                </div>
                <div class="kpi-value">Rp 4.914.000</div>
                <div class="kpi-trend neutral">Saldo tersedia</div>
            </article>
        </section>

        <section class="ai-insight">
            <div class="ai-title">
                <span class="ai-badge">✦</span>
                <span>AI Insight</span>
            </div>
            <div class="ai-content">
                <strong>Insight</strong>
                <p>Pengeluaran bulan ini jauh lebih besar daripada pemasukan, sehingga masjid mengalami kekurangan dana (defisit) sebesar <strong class="danger-copy">Rp 8.786.000</strong>. Sebagian besar pengeluaran (sekitar 91%) digunakan hanya untuk biaya operasional harian atau bulanan.</p>
                <strong>Rekomendasi</strong>
                <p>Periksa kembali rincian biaya operasional untuk menghemat pengeluaran yang kurang penting, serta sampaikan kondisi keuangan ini secara transparan kepada jemaah agar dapat mendorong peningkatan donasi atau infaq.</p>
            </div>
        </section>

        <section class="chart-panel section">
            <div class="panel-header">
                <div>
                    <h2>Tren Keuangan 6 Bulan</h2>
                    <div class="panel-meta">April — September 2026</div>
                </div>
                <div class="chart-legend">
                    <span class="legend-income"><span class="legend-dot"></span>Pemasukan</span>
                    <span class="legend-expense"><span class="legend-dot"></span>Pengeluaran</span>
                </div>
            </div>

            <div class="chart-container">
                <div class="chart-item">
                    <div class="bar income-bar" style="height: 120px"></div>
                    <div class="bar expense-bar" style="height: 80px"></div>
                    <span class="month-label">Apr</span>
                </div>
                <div class="chart-item">
                    <div class="bar income-bar" style="height: 90px"></div>
                    <div class="bar expense-bar" style="height: 110px"></div>
                    <span class="month-label">Mei</span>
                </div>
                <div class="chart-item">
                    <div class="bar income-bar" style="height: 104px"></div>
                    <div class="bar expense-bar" style="height: 132px"></div>
                    <span class="month-label">Jun</span>
                </div>
                <div class="chart-item">
                    <div class="bar income-bar" style="height: 87px"></div>
                    <div class="bar expense-bar" style="height: 122px"></div>
                    <span class="month-label">Jul</span>
                </div>
                <div class="chart-item">
                    <div class="bar income-bar" style="height: 110px"></div>
                    <div class="bar expense-bar" style="height: 118px"></div>
                    <span class="month-label">Agu</span>
                </div>
                <div class="chart-item">
                    <div class="bar income-bar" style="height: 116px"></div>
                    <div class="bar expense-bar" style="height: 140px"></div>
                    <span class="month-label">Sep</span>
                </div>
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
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row["tanggal"]) ?></td>
                                <td><?= htmlspecialchars($row["deskripsi"]) ?></td>
                                <td><span class="kategori-badge"><?= htmlspecialchars($row["kategori"]) ?></span></td>
                                <td class="<?= $row["jenis"] === "Pemasukan" ? "income-text" : "expense-text" ?>">
                                    <?= $row["jenis"] === "Pemasukan" ? "+" : "-" ?>
                                    Rp <?= number_format($row["nominal"], 0, ",", ".") ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<div class="modal-backdrop">
    <div class="transaction-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="modal-header">
            <h2 id="modal-title">Tambah Transaksi</h2>
            <button type="button" class="modal-close" aria-label="Tutup">×</button>
        </div>

        <form method="POST" class="transaction-form">
            <div class="transaction-tabs" aria-label="Jenis transaksi">
                <button type="button" class="tab active" data-value="Pemasukan">Pemasukan</button>
                <button type="button" class="tab" data-value="Pengeluaran">Pengeluaran</button>
                <input type="hidden" name="jenis" value="Pemasukan">
            </div>

            <div class="field-group">
                <label for="tanggal">Tanggal</label>
                <input id="tanggal" type="date" name="tanggal" value="<?= date("Y-m-d") ?>" required>
            </div>

            <div class="field-group">
                <label for="deskripsi">Deskripsi</label>
                <input id="deskripsi" type="text" name="deskripsi" placeholder="Contoh: Infaq Jumat" required>
            </div>

            <div class="field-group">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori" required>
                    <option value="Infaq">Infaq</option>
                    <option value="Donasi">Donasi</option>
                    <option value="Operasional">Operasional</option>
                    <option value="Perbaikan">Perbaikan</option>
                    <option value="Listrik">Listrik & Air</option>
                    <option value="Kegiatan">Kegiatan</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>

            <div class="field-group">
                <label for="nominal">Nominal (Rp)</label>
                <input id="nominal" type="number" name="nominal" min="0" placeholder="0" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="secondary-btn">Batal</button>
                <button type="submit" class="primary-btn">Simpan Transaksi</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab').forEach((btn) => btn.classList.remove('active'));
            tab.classList.add('active');
            document.querySelector('input[name="jenis"]').value = tab.dataset.value;
        });
    });

    document.querySelector('.modal-close').addEventListener('click', () => {
        window.location.href = 'dashboard.php';
    });

    document.querySelector('.secondary-btn').addEventListener('click', () => {
        window.location.href = 'dashboard.php';
    });
</script>

</body>
</html>
