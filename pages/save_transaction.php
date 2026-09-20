<?php

// ==========================================
// KONEKSI DATABASE
// ==========================================

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "smart_finance"
);


// Cek koneksi

if ($conn->connect_error) {

    die(
        "Koneksi database gagal: "
        . $conn->connect_error
    );

}


// ==========================================
// AMBIL DATA DARI FORM
// ==========================================

$tanggal = $_POST["tanggal"] ?? "";

$jenis = $_POST["jenis"] ?? "";

$kategori = $_POST["kategori"] ?? "";

$deskripsi = $_POST["deskripsi"] ?? "";

$nominal = $_POST["nominal"] ?? 0;


// ==========================================
// VALIDASI
// ==========================================

if (
    empty($tanggal) ||
    empty($jenis) ||
    empty($kategori) ||
    empty($deskripsi) ||
    empty($nominal)
) {

    die("Data transaksi belum lengkap.");

}


// ==========================================
// SIMPAN KE DATABASE
// ==========================================

$sql = "
    INSERT INTO transactions
    (
        tanggal,
        jenis,
        kategori,
        deskripsi,
        nominal
    )
    VALUES
    (?, ?, ?, ?, ?)
";


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

    // Berhasil → kembali ke dashboard

    header(
        "Location: dashboard.php"
    );

    exit;

} else {

    echo "Gagal menyimpan transaksi: ";

    echo $stmt->error;

}


$stmt->close();

$conn->close();

?>