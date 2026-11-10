<?php
declare(strict_types=1);

// Pengaturan koneksi MySQL; sesuaikan nilainya jika konfigurasi XAMPP berbeda.
$dbHost = 'localhost';
$dbUser = 'root';
$dbPassword = '';
$dbName = 'kesehatan';

mysqli_report(MYSQLI_REPORT_OFF);
// Membuka satu koneksi yang dipakai oleh halaman yang memanggil berkas ini.
$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

// Hentikan proses dengan status server 500 jika database tidak bisa dihubungi.
if ($conn->connect_error) {
    http_response_code(500);
    exit('Koneksi basis data gagal. Periksa pengaturan MySQL di db.php.');
}

// Menyamakan encoding koneksi dengan database agar teks UTF-8 tersimpan benar.
$conn->set_charset('utf8mb4');
