<?php
declare(strict_types=1);

$dbHost = 'localhost';
$dbUser = 'root';
$dbPassword = '';
$dbName = 'kesehatan';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

if ($conn->connect_error) {
    http_response_code(500);
    exit('Koneksi basis data gagal. Periksa pengaturan MySQL di db.php.');
}

$conn->set_charset('utf8mb4');
