<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Nota</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="scan-page">

    <header class="scan-header">
        <div class="scan-title-wrap">
            <h1>Scan Nota</h1>
            <p>Upload foto nota untuk membaca transaksi secara otomatis</p>
        </div>

        <a href="dashboard.php" class="scan-back-btn">
            <span class="scan-back-icon">&#8249;</span>
            <span>Kembali</span>
        </a>
    </header>

    <main class="scan-panel">
        <div class="scan-panel-header">
            <span class="scan-panel-icon">⇪</span>
            <h2>Upload Foto Nota</h2>
        </div>

        <form id="scanForm" action="process_nota.php" method="POST" enctype="multipart/form-data" class="scan-upload-form">
            <label class="upload-dropzone" for="nota-file">
                <input id="nota-file" type="file" name="nota" accept="image/*" required>
                <div class="upload-placeholder">
                    <div class="upload-icon">◌</div>
                    <div class="upload-text">
                        <strong>Drag &amp; drop foto nota</strong>
                        <span>atau klik untuk memilih file</span>
                    </div>
                    <small>JPG, PNG, WEBP hingga 10MB</small>
                </div>
            </label>

            <button type="submit" class="upload-submit">Scan Nota</button>
        </form>

        <script>
            const scanFileInput = document.getElementById('nota-file');
            const scanForm = document.getElementById('scanForm');

            if (scanFileInput && scanForm) {
                scanFileInput.addEventListener('change', function () {
                    if (this.files && this.files.length > 0) {
                        scanForm.submit();
                    }
                });
            }
        </script>
    </main>

    <div class="tips-box">
        <h3>TIPS UNTUK HASIL TERBAIK</h3>
        <ol>
            <li>Pastikan foto nota tidak buram atau terpotong</li>
            <li>Ambil foto dengan pencahayaan yang cukup</li>
            <li>Posisikan kamera lurus menghadap nota</li>
            <li>Gunakan resolusi kamera yang cukup tinggi</li>
        </ol>
    </div>

</div>

</body>
</html>