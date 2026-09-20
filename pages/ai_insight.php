<?php

$api_config = __DIR__ . "/../config/gemini.php";
if (is_file($api_config)) {
    require_once $api_config;
}

$bulan_ini = date("Y-m");
$bulan_lalu = date("Y-m", strtotime("-1 month"));

$sql_ai = "
    SELECT
        COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini' THEN nominal ELSE 0 END), 0) AS pemasukan_ini,
        COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini' THEN nominal ELSE 0 END), 0) AS pengeluaran_ini,
        COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_lalu' THEN nominal ELSE 0 END), 0) AS pemasukan_lalu,
        COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_lalu' THEN nominal ELSE 0 END), 0) AS pengeluaran_lalu
    FROM transactions
";

$ai_result = $conn->query($sql_ai);
$ai_data = $ai_result ? $ai_result->fetch_assoc() : [];

$pemasukan_ini = (float) ($ai_data["pemasukan_ini"] ?? 0);
$pengeluaran_ini = (float) ($ai_data["pengeluaran_ini"] ?? 0);
$pemasukan_lalu = (float) ($ai_data["pemasukan_lalu"] ?? 0);
$pengeluaran_lalu = (float) ($ai_data["pengeluaran_lalu"] ?? 0);
$saldo_ini = $pemasukan_ini - $pengeluaran_ini;

$ai_insight = "";
$ai_rekomendasi = "";

if (!empty($GEMINI_API_KEY) && function_exists("curl_init")) {
    $prompt = "Analisis keuangan masjid dalam bahasa Indonesia. "
        . "Berikan tepat dua baris tanpa markdown: baris pertama diawali 'Insight: ', "
        . "baris kedua diawali 'Rekomendasi: '. "
        . "Data bulan ini: pemasukan Rp " . number_format($pemasukan_ini, 0, ',', '.')
        . ", pengeluaran Rp " . number_format($pengeluaran_ini, 0, ',', '.')
        . ", saldo bulan ini Rp " . number_format($saldo_ini, 0, ',', '.')
        . ". Data bulan lalu: pemasukan Rp " . number_format($pemasukan_lalu, 0, ',', '.')
        . ", pengeluaran Rp " . number_format($pengeluaran_lalu, 0, ',', '.') . ".";

    $payload = json_encode([
        "contents" => [["parts" => [["text" => $prompt]]]],
        "generationConfig" => ["temperature" => 0.2, "maxOutputTokens" => 180]
    ]);

    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json", "x-goog-api-key: " . $GEMINI_API_KEY],
        CURLOPT_POSTFIELDS => $payload
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $decoded = $response ? json_decode($response, true) : null;
    $generated_text = trim($decoded["candidates"][0]["content"]["parts"][0]["text"] ?? "");

    if ($generated_text !== "") {
        $lines = preg_split('/\r\n|\r|\n/', $generated_text);
        foreach ($lines as $line) {
            $line = trim($line);
            if (stripos($line, "Insight:") === 0) {
                $ai_insight = trim(substr($line, 8));
            } elseif (stripos($line, "Rekomendasi:") === 0) {
                $ai_rekomendasi = trim(substr($line, 12));
            }
        }
    }
}

if ($ai_insight === "" && $pemasukan_ini > $pemasukan_lalu) {
    $insight_pemasukan = "Pemasukan bulan ini meningkat dibanding bulan lalu";
} elseif ($ai_insight === "" && $pemasukan_ini < $pemasukan_lalu) {
    $insight_pemasukan = "Pemasukan bulan ini menurun dibanding bulan lalu";
} elseif ($ai_insight === "") {
    $insight_pemasukan = "Pemasukan bulan ini berada di level yang sama dengan bulan lalu";
}

if ($ai_insight === "" && $pengeluaran_ini > $pemasukan_ini && $pengeluaran_ini > 0) {
    $ai_insight = "$insight_pemasukan, tetapi pengeluaran lebih besar daripada pemasukan sehingga arus kas perlu diperhatikan.";
    $ai_rekomendasi = "Tinjau kembali pengeluaran terbesar dan prioritaskan kebutuhan operasional agar saldo kas tetap aman.";
} elseif ($ai_insight === "" && $saldo_ini > 0) {
    $ai_insight = "$insight_pemasukan, sementara pengeluaran masih berada di bawah pemasukan. Arus kas bulan ini positif.";
    $ai_rekomendasi = "Pertahankan pola ini dan alokasikan sebagian saldo untuk dana darurat serta kegiatan sosial masjid.";
} elseif ($ai_insight === "") {
    $ai_insight = "Belum ada arus kas positif pada bulan ini. Tambahkan pemasukan atau kendalikan pengeluaran rutin agar saldo kembali sehat.";
    $ai_rekomendasi = "Periksa transaksi bulan berjalan dan dahulukan kebutuhan yang paling penting sebelum melakukan pengeluaran baru.";
}

if ($ai_rekomendasi === "") {
    $ai_rekomendasi = "Tinjau transaksi terbesar dan pertahankan dana cadangan untuk kebutuhan operasional masjid.";
}

?>

<div class="ai-title">
    <span class="ai-badge">✦</span>
    <span>AI Insight</span>
</div>

<div class="ai-content">
    <strong>Insight</strong>
    <p><?= htmlspecialchars($ai_insight) ?></p>

    <strong>Rekomendasi</strong>
    <p><?= htmlspecialchars($ai_rekomendasi) ?></p>
</div>