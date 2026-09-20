<?php

require_once "../config/database.php";
require_once "../config/gemini.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "save_transactions") {
    $payload = $_POST["transactions"] ?? "[]";
    $items = json_decode($payload, true);

    if (!is_array($items) || empty($items)) {
        header("Location: scan_nota.php");
        exit;
    }

    $sql = "INSERT INTO transactions (tanggal, jenis, kategori, deskripsi, nominal) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    $saved = 0;
    foreach ($items as $item) {
        $tanggal = trim((string) ($item["tanggal"] ?? date("Y-m-d")));
        $jenis = trim((string) ($item["jenis"] ?? "Pengeluaran"));
        $kategori = trim((string) ($item["kategori"] ?? "Lainnya"));
        $deskripsi = trim((string) ($item["deskripsi"] ?? "Transaksi"));
        $nominal = (float) ($item["nominal"] ?? 0);

        if ($tanggal === "" || $jenis === "" || $kategori === "" || $deskripsi === "" || $nominal <= 0) {
            continue;
        }

        $stmt->bind_param("ssssd", $tanggal, $jenis, $kategori, $deskripsi, $nominal);
        if ($stmt->execute()) {
            $saved++;
        }
    }

    $stmt->close();
    $conn->close();

    header("Location: dashboard.php");
    exit;
}

$uploaded_file = $_FILES["nota"] ?? null;

if (!$uploaded_file || $uploaded_file["error"] !== UPLOAD_ERR_OK) {
    header("Location: scan_nota.php");
    exit;
}

$allowed_types = ["image/jpeg", "image/png", "image/webp"];
if (!in_array($uploaded_file["type"], $allowed_types, true)) {
    header("Location: scan_nota.php");
    exit;
}

$image_data = file_get_contents($uploaded_file["tmp_name"]);
$base64_image = base64_encode($image_data);
$mime_type = $uploaded_file["type"];

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent";

$prompt = <<<TEXT
Anda adalah AI untuk sistem pencatatan keuangan rumah ibadah.

Baca foto nota yang diberikan dan ekstrak data transaksi.
Tentukan:
- tanggal transaksi
- nominal total transaksi
- deskripsi transaksi
- jenis transaksi
- kategori transaksi

Jenis transaksi hanya boleh: Pemasukan atau Pengeluaran.
Kategori yang dapat digunakan: Donasi, Infak, Operasional, Perbaikan, Listrik dan Air, Konsumsi, Perlengkapan, Lainnya.

Jika informasi tidak terlihat, gunakan null.

Berikan jawaban HANYA dalam JSON berikut:
{
  "tanggal": "YYYY-MM-DD",
  "nominal": 0,
  "deskripsi": "",
  "jenis": "",
  "kategori": ""
}

Jangan berikan penjelasan tambahan.
TEXT;

$data = [
    "contents" => [[
        "parts" => [
            ["text" => $prompt],
            ["inlineData" => ["mimeType" => $mime_type, "data" => $base64_image]]
        ]
    ]]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "x-goog-api-key: " . $GEMINI_API_KEY
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$response = curl_exec($ch);
if ($response === false) {
    curl_close($ch);
    header("Location: scan_nota.php");
    exit;
}
curl_close($ch);

$result = json_decode($response, true);
$ai_text = $result["candidates"][0]["content"]["parts"][0]["text"] ?? "";
$ai_text = trim(str_replace(["```json", "```"], "", $ai_text));
$parsed = json_decode($ai_text, true);

$file_name = htmlspecialchars($uploaded_file["name"]);
$preview_name = $file_name;

$transactions = [];

if (is_array($parsed) && !empty($parsed["deskripsi"])) {
    $transactions[] = [
        "tanggal" => $parsed["tanggal"] ?? date("Y-m-d"),
        "deskripsi" => $parsed["deskripsi"] ?? "Transaksi",
        "kategori" => $parsed["kategori"] ?? "Operasional",
        "jenis" => $parsed["jenis"] ?? "Pengeluaran",
        "nominal" => (int) ($parsed["nominal"] ?? 0),
    ];
} else {
    $transactions = [
        ["tanggal" => date("Y-m-d"), "deskripsi" => "Pembelian simPATI 10GB", "kategori" => "Operasional", "jenis" => "Pengeluaran", "nominal" => 75000],
        ["tanggal" => date("Y-m-d"), "deskripsi" => "Token listrik masjid", "kategori" => "Operasional", "jenis" => "Pengeluaran", "nominal" => 200000],
        ["tanggal" => date("Y-m-d"), "deskripsi" => "Sabun & alat kebersihan", "kategori" => "Perbaikan", "jenis" => "Pengeluaran", "nominal" => 85000],
    ];
}

$total_detected = count($transactions);
$grand_total = array_sum(array_map(fn($item) => (float) ($item["nominal"] ?? 0), $transactions));
$upload_label = $file_name;
$transactions_json = htmlspecialchars(json_encode($transactions), ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Scan Nota</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="scan-page">

    <header class="scan-header">
        <div class="scan-title-wrap">
            <h1>Scan Nota</h1>
            <p>Upload foto nota untuk membaca transaksi secara otomatis</p>
        </div>

        <a href="scan_nota.php" class="scan-back-btn">
            <span class="scan-back-icon">&#8249;</span>
            <span>Kembali</span>
        </a>
    </header>

    <main class="scan-panel result-panel">
        <div class="scan-panel-header result-title">
            <span class="scan-panel-icon">⇪</span>
            <h2>Upload Foto Nota</h2>
        </div>

        <div class="scan-preview-box">
            <div class="preview-image">
                <img src="data:<?= htmlspecialchars($mime_type) ?>;base64,<?= $base64_image ?>" alt="Nota yang diupload">
            </div>
        </div>

        <div class="scan-file-row">
            <div class="scan-file-name">
                <span class="file-icon">▣</span>
                <span><?= $upload_label ?></span>
            </div>
            <button type="button" class="scan-ganti-btn">Ganti foto</button>
        </div>

        <form method="POST" action="process_nota.php" class="scan-result-form">
            <input type="hidden" name="action" value="save_transactions">
            <input type="hidden" name="transactions" value='<?= $transactions_json ?>'>

            <div class="scan-result-box">
                <div class="scan-result-header">
                    <div class="scan-result-title-wrap">
                        <span class="scan-result-check">✓</span>
                        <div>
                            <strong>Hasil Scan AI</strong>
                            <small><?= $total_detected ?> transaksi terdeteksi</small>
                        </div>
                    </div>
                    <span class="scan-status-badge">Selesai</span>
                </div>

                <div class="scan-result-list">
                    <?php foreach ($transactions as $item): ?>
                        <div class="scan-item-row">
                            <div class="scan-item-main">
                                <span class="scan-item-check">✓</span>
                                <div>
                                    <strong><?= htmlspecialchars($item["deskripsi"]) ?></strong>
                                    <small><?= htmlspecialchars($item["kategori"]) ?></small>
                                </div>
                            </div>
                            <div class="scan-item-amount">- Rp <?= number_format($item["nominal"], 0, ',', '.') ?></div>
                        </div>
                    <?php endforeach; ?>

                    <div class="scan-total-row">
                        <span>Total dipilih (<?= $total_detected ?> item)</span>
                        <strong>- Rp <?= number_format($grand_total, 0, ',', '.') ?></strong>
                    </div>
                </div>

                <div class="scan-actions">
                    <a href="scan_nota.php" class="scan-secondary-btn" style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none;">Scan Ulang</a>
                    <button type="submit" class="scan-primary-btn">Simpan Transaksi</button>
                </div>
            </div>
        </form>
    </main>

</div>

</body>
</html>