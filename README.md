# Smart Financial Dashboard

Smart Financial Dashboard adalah sistem pencatatan dan pemantauan keuangan yang dirancang untuk membantu pengelolaan keuangan rumah ibadah, seperti masjid dan gereja.

Sistem ini memungkinkan pengguna mencatat pemasukan dan pengeluaran, melihat saldo serta riwayat transaksi, dan menggunakan bantuan AI untuk mengekstraksi informasi transaksi dari foto nota secara otomatis.

## Fitur Utama

- Dashboard ringkasan keuangan
- Pencatatan pemasukan
- Pencatatan pengeluaran
- Pencatatan transaksi
- Perhitungan saldo secara otomatis
- Riwayat transaksi
- Scan nota menggunakan AI
- Ekstraksi data transaksi dari foto nota
- Form konfirmasi hasil ekstraksi AI
- Penyimpanan hasil transaksi ke database
- Tampilan antarmuka dashboard yang sederhana

## Teknologi yang Digunakan

- PHP
- MySQL
- HTML
- CSS
- JavaScript
- Gemini API
- XAMPP
- phpMyAdmin

## Implementasi AI

AI digunakan untuk membantu proses pencatatan transaksi melalui fitur **Scan Nota**.

Pengguna dapat mengunggah foto nota atau bukti transaksi. Sistem kemudian mengirimkan gambar tersebut ke Gemini API untuk melakukan ekstraksi informasi transaksi.

Informasi yang dapat diekstraksi meliputi:

- Tanggal transaksi
- Jenis transaksi
- Kategori
- Deskripsi
- Nominal transaksi

### Alur Scan Nota

```text
Foto Nota
    ↓
Upload ke Sistem
    ↓
Gemini AI
    ↓
Ekstraksi Informasi Transaksi
    ↓
Tanggal
Jenis Transaksi
Kategori
Deskripsi
Nominal
    ↓
Form Konfirmasi
    ↓
User Memeriksa Data
    ↓
Simpan Transaksi
    ↓
MySQL Database
    ↓
Dashboard Keuangan
````

Dengan alur tersebut, pengguna tidak perlu memasukkan seluruh informasi transaksi secara manual dari nota.

## Arsitektur Sistem

```text
                    SMART FINANCIAL DASHBOARD
                              │
              ┌───────────────┴───────────────┐
              │                               │
        Dashboard Web                    Scan Nota
              │                               │
              │                          Gemini AI
              │                               │
              │                     Ekstraksi Data
              │                               │
              └───────────────┬───────────────┘
                              │
                         PHP Application
                              │
                              ▼
                         MySQL Database
                              │
                              ▼
                    Data Transaksi Keuangan
```

## Database

Sistem menggunakan MySQL sebagai database.

Nama database:

```text
smart_finance
```

Tabel utama:

```text
transactions
```

Tabel `transactions` digunakan untuk menyimpan data transaksi keuangan yang terdiri dari pemasukan dan pengeluaran.

Struktur database dapat ditemukan pada file:

```text
database/smart_finance.sql
```

## Struktur Project

```text
smart-finance/
│
├── assets/
│   └── css/
│
├── config/
│   ├── database.php
│   └── gemini.php
│
├── pages/
│   ├── dashboard.php
│   ├── pemasukan.php
│   ├── pengeluaran.php
│   ├── transaksi.php
│   ├── scan_nota.php
│   ├── process_nota.php
│   └── save_transaction.php
│
├── database/
│   └── smart_finance.sql
│
├── index.php
│
└── README.md
```

## Persyaratan Sistem

Sebelum menjalankan project, pastikan perangkat telah memiliki:

* XAMPP
* PHP
* MySQL
* Web Browser
* Gemini API Key

## Instalasi

### 1. Clone Repository

Clone repository menggunakan Git:

```bash
git clone https://github.com/mchaeryansyahh/smart-finance-dashboard.git
```

Kemudian masuk ke folder project:

```bash
cd smart-finance-dashboard
```

### 2. Letakkan Project di XAMPP

Pindahkan folder project ke:

```text
C:\xampp\htdocs\smart-finance
```

### 3. Jalankan XAMPP

Buka XAMPP Control Panel.

Jalankan:

```text
Apache
MySQL
```

Pastikan keduanya dalam kondisi running.

### 4. Membuat Database

Buka phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Buat database dengan nama:

```text
smart_finance
```

Kemudian import file:

```text
database/smart_finance.sql
```

### 5. Konfigurasi Database

Sesuaikan konfigurasi database pada file:

```text
config/database.php
```

Contoh konfigurasi lokal:

```php
$host = "localhost";
$dbname = "smart_finance";
$username = "root";
$password = "";
```

Sesuaikan konfigurasi tersebut dengan pengaturan MySQL pada perangkat.

### 6. Konfigurasi Gemini API

Sistem menggunakan Gemini API untuk fitur Scan Nota.

API Key harus disimpan pada konfigurasi aplikasi dan **tidak boleh dimasukkan ke repository publik**.

Contoh:

```text
config/gemini.php
```

API Key:

```text
GEMINI_API_KEY
```

Jangan membagikan API Key melalui GitHub atau media publik lainnya.

## Menjalankan Sistem

Setelah Apache dan MySQL aktif, buka browser dan akses:

```text
http://localhost/smart-finance/
```

Jika menggunakan konfigurasi port Apache tertentu, sesuaikan URL dengan port yang digunakan.

Contoh:

```text
http://localhost:8082/smart-finance/
```

## Penggunaan Sistem

### Pencatatan Transaksi

Pengguna dapat menambahkan transaksi melalui halaman transaksi.

Data yang dimasukkan meliputi:

* Tanggal
* Jenis transaksi
* Kategori
* Deskripsi
* Nominal

Setelah transaksi disimpan, data akan masuk ke database dan ditampilkan pada dashboard.

### Scan Nota

Pengguna dapat memilih menu **Scan Nota**, kemudian mengunggah foto nota.

Sistem akan:

1. Menerima foto nota.
2. Mengirim gambar ke Gemini AI.
3. Mengekstraksi informasi transaksi.
4. Menampilkan hasil ekstraksi.
5. Memungkinkan pengguna memeriksa dan mengoreksi data.
6. Menyimpan transaksi setelah dikonfirmasi.

## Contoh Hasil Scan

```text
Tanggal       : 05/09/2026
Jenis         : Pengeluaran
Kategori      : Operasional
Deskripsi     : Pembelian pulsa
Nominal       : Rp89.360
```

Data tersebut kemudian dapat dikonfirmasi oleh pengguna sebelum disimpan ke database.

## Peran AI dalam Sistem

AI pada sistem digunakan sebagai komponen untuk membantu proses ekstraksi dan pengolahan informasi transaksi.

Gemini AI berperan dalam:

* Membaca informasi dari foto nota.
* Mengekstraksi data transaksi.
* Mengubah informasi dari gambar menjadi data terstruktur.
* Mengurangi kebutuhan input transaksi secara manual.

AI tidak menggantikan pengguna dalam melakukan verifikasi. Hasil ekstraksi tetap ditampilkan kepada pengguna untuk diperiksa sebelum disimpan.

## Keamanan

Beberapa informasi sensitif tidak boleh dimasukkan ke repository publik, terutama:

```text
Gemini API Key
Password Database
Credential
File konfigurasi yang berisi secret
```

Gunakan `.gitignore` untuk mencegah file sensitif ikut ter-upload ke GitHub.

Contoh:

```text
config/gemini.php
.env
```

## Status Project

Project ini dikembangkan sebagai project Praktik Industri/Magang.

Status pengembangan:

* [x] Dashboard keuangan
* [x] Pencatatan pemasukan
* [x] Pencatatan pengeluaran
* [x] Riwayat transaksi
* [x] Perhitungan saldo
* [x] Database MySQL
* [x] Integrasi Gemini AI
* [x] Scan nota
* [x] Ekstraksi data transaksi menggunakan AI
* [x] Konfirmasi hasil scan
* [x] Penyimpanan hasil scan ke database
* [ ] Deployment online
* [ ] Pengembangan fitur lanjutan

## Tujuan Project

Project ini bertujuan untuk menyediakan sistem pencatatan keuangan yang sederhana dan mudah digunakan, sekaligus memanfaatkan teknologi AI untuk mengurangi proses input transaksi secara manual.

## Pengembang

**M.Chaeryansyah**

Project Magang.

```

Setelah kamu paste ke GitHub, **jangan dulu masukkan bagian n8n, Telegram, atau fitur yang belum benar-benar ada**. README sebaiknya mencerminkan kondisi project yang benar-benar sudah bisa dijalankan.

Satu hal yang juga perlu kamu lakukan sebelum `git push`: **pastikan API key Gemini tidak pernah ikut ter-upload ke repository publik**. Ini lebih penting daripada menambahkan banyak dokumentasi.
```
