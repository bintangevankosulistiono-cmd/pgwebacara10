<?php
declare(strict_types=1);

// Penghapusan hanya menerima permintaan POST agar tidak terpicu oleh tautan biasa.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php', true, 303);
    exit;
}

// Memastikan formulir penghapusan berasal dari sesi aplikasi yang sah.
require __DIR__ . '/csrf.php';
if (!csrf_token_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
}

// Memeriksa ID sebelum digunakan pada query penghapusan.
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('ID data tidak valid.');
}

// Menghapus satu baris berdasarkan ID menggunakan prepared statement.
require __DIR__ . '/db.php';
$stmt = $conn->prepare('DELETE FROM fasilitas_kesehatan WHERE id = ?');
if ($stmt === false) {
    http_response_code(500);
    exit('Pernyataan penghapusan tidak dapat disiapkan.');
}

$stmt->bind_param('i', $id);
if (!$stmt->execute()) {
    http_response_code(500);
    exit('Data gagal dihapus.');
}
if ($stmt->affected_rows !== 1) {
    http_response_code(404);
    exit('Data fasilitas kesehatan tidak ditemukan.');
}

// Menutup resource lalu kembali ke daftar dengan pesan status berhasil.
$stmt->close();
$conn->close();
header('Location: index.php?status=deleted', true, 303);
exit;
