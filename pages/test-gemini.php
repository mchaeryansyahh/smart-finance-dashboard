<?php

require_once "../config/gemini.php";

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent";
$data = [

    "contents" => [

        [

            "parts" => [

                [
                    "text" => "Jelaskan apa itu pencatatan keuangan masjid dalam satu kalimat."
                ]

            ]

        ]

    ]

];


$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt(
    $ch,
    CURLOPT_IPRESOLVE,
    CURL_IPRESOLVE_V4
);

curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [

    "Content-Type: application/json",

    "x-goog-api-key: " . $GEMINI_API_KEY

]);

curl_setopt(
    $ch,
    CURLOPT_POSTFIELDS,
    json_encode($data)
);


$response = curl_exec($ch);

$http_code = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);


if ($response === false) {

    die(
        "Gagal menghubungi Gemini: "
        . curl_error($ch)
    );

}


curl_close($ch);


echo "<h2>Status: $http_code</h2>";

echo "<pre>";

echo htmlspecialchars($response);

echo "</pre>";

?>