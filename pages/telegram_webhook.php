<?php

require_once "../config/database.php";
require_once "../config/telegram.php";
require_once "../config/gemini.php";


// ==========================================
// 1. TERIMA UPDATE DARI TELEGRAM
// ==========================================

$update_raw = file_get_contents("php://input");
$update = json_decode($update_raw, true);

if (!isset($update["message"])) {
    http_response_code(200);
    exit;
}

$message = $update["message"];
$chat_id = $message["chat"]["id"];


// ==========================================
// 2. FUNGSI BANTU: KIRIM PESAN KE TELEGRAM
// ==========================================

function send_telegram_message($chat_id, $text) {

    global $TELEGRAM_BOT_TOKEN;

    $url = "https://api.telegram.org/bot{$TELEGRAM_BOT_TOKEN}/sendMessage";

    $data = [
        "chat_id" => $chat_id,
        "text" => $text,
        "parse_mode" => "HTML"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_exec($ch);
    curl_close($ch);

}


// ==========================================
// 3. FUNGSI BANTU: KIRIM TEKS/GAMBAR KE GEMINI
// ==========================================

function ask_gemini($prompt, $image_base64 = null, $mime_type = null) {

    global $GEMINI_API_KEY;

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent";

    $parts = [["text" => $prompt]];

    if ($image_base64) {
        $parts[] = [
            "inlineData" => [
                "mimeType" => $mime_type,
                "data" => $image_base64
            ]
        ];
    }

    $data = ["contents" => [["parts" => $parts]]];

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
    curl_close($ch);

    $result = json_decode($response, true);

    $ai_text = $result["candidates"][0]["content"]["parts"][0]["text"] ?? "";
    $ai_text = trim(str_replace(["```json", "```"], "", $ai_text));

    return json_decode($ai_text, true);

}


// ==========================================
// 4. FUNGSI BANTU: SIMPAN TRANSAKSI KE DATABASE
// ==========================================

function save_transaction_to_db($conn, $transaction) {

    $sql = "INSERT INTO transactions 
            (tanggal, jenis, kategori, deskripsi, nominal)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $tanggal = $transaction["tanggal"] ?? date("Y-m-d");
    $jenis = $transaction["jenis"] ?? "Pemasukan";
    $kategori = $transaction["kategori"] ?? "Lainnya";
    $deskripsi = $transaction["deskripsi"] ?? "-";
    $nominal = $transaction["nominal"] ?? 0;

    $stmt->bind_param(
        "ssssd",
        $tanggal,
        $jenis,
        $kategori,
        $deskripsi,
        $nominal
    );

    return $stmt->execute();

}


// ==========================================
// 5. PROMPT UNTUK EKSTRAKSI (dipakai foto & teks)
// ==========================================

$base_prompt = <<<TEXT

Anda adalah AI untuk sistem pencatatan keuangan rumah ibadah.

Ekstrak data transaksi dari input berikut.

Tentukan: tanggal (YYYY-MM-DD, gunakan hari ini jika tidak disebut),
nominal, deskripsi, jenis (Pemasukan/Pengeluaran),
kategori (Donasi/Infak/Operasional/Perbaikan/Listrik dan Air/Konsumsi/Perlengkapan/Lainnya).

Jika bukan transaksi keuangan sama sekali, balas: {"bukan_transaksi": true}

Berikan jawaban HANYA dalam JSON:
{"tanggal": "YYYY-MM-DD", "nominal": 0, "deskripsi": "", "jenis": "", "kategori": ""}

TEXT;


// ==========================================
// 6. HANDLE PESAN FOTO (kirim nota)
// ==========================================

if (isset($message["photo"])) {

    // Ambil foto resolusi terbesar (array photo urut dari kecil ke besar)
    $photos = $message["photo"];
    $file_id = end($photos)["file_id"];

    // Ambil path file dari Telegram
    $file_info_url = "https://api.telegram.org/bot{$TELEGRAM_BOT_TOKEN}/getFile?file_id={$file_id}";
    $file_info = json_decode(file_get_contents($file_info_url), true);
    $file_path = $file_info["result"]["file_path"];

    // Download file gambar
    $file_url = "https://api.telegram.org/file/bot{$TELEGRAM_BOT_TOKEN}/{$file_path}";
    $image_data = file_get_contents($file_url);
    $image_base64 = base64_encode($image_data);

    send_telegram_message($chat_id, "🔍 Sedang membaca nota...");

    $transaction = ask_gemini($base_prompt, $image_base64, "image/jpeg");

    if (isset($transaction["bukan_transaksi"]) || !is_array($transaction)) {
        send_telegram_message($chat_id, "❌ Maaf, saya tidak bisa membaca data transaksi dari foto ini.");
        exit;
    }

    $saved = save_transaction_to_db($conn, $transaction);

    if ($saved) {
        $reply = "✅ <b>Tercatat:</b>\n"
            . "📅 " . $transaction["tanggal"] . "\n"
            . "📝 " . $transaction["deskripsi"] . "\n"
            . "🏷 " . $transaction["kategori"] . " (" . $transaction["jenis"] . ")\n"
            . "💰 Rp " . number_format($transaction["nominal"], 0, ",", ".");
    } else {
        $reply = "❌ Gagal menyimpan ke database.";
    }

    send_telegram_message($chat_id, $reply);
    exit;

}


// ==========================================
// 7. HANDLE PESAN TEKS (input manual via chat)
// ==========================================

if (isset($message["text"])) {

    $text = $message["text"];

    // Perintah dasar
    if ($text === "/start") {
        send_telegram_message($chat_id, "👋 Halo! Kirim foto nota, atau ketik transaksi langsung, contoh:\n\"Infaq 500rb dari hamba Allah\"");
        exit;
    }

    send_telegram_message($chat_id, "🔍 Memproses...");

    $transaction = ask_gemini($base_prompt . "\n\nInput pengguna: " . $text);

    if (isset($transaction["bukan_transaksi"]) || !is_array($transaction)) {
        send_telegram_message($chat_id, "❌ Maaf, saya tidak mengenali ini sebagai transaksi keuangan.");
        exit;
    }

    $saved = save_transaction_to_db($conn, $transaction);

    if ($saved) {
        $reply = "✅ <b>Tercatat:</b>\n"
            . "📅 " . $transaction["tanggal"] . "\n"
            . "📝 " . $transaction["deskripsi"] . "\n"
            . "🏷 " . $transaction["kategori"] . " (" . $transaction["jenis"] . ")\n"
            . "💰 Rp " . number_format($transaction["nominal"], 0, ",", ".");
    } else {
        $reply = "❌ Gagal menyimpan ke database.";
    }

    send_telegram_message($chat_id, $reply);

}

?>