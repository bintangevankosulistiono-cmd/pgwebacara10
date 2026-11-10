<?php
declare(strict_types=1);

// Endpoint ini hanya memproses pengiriman formulir dengan metode POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php', true, 303);
    exit;
}

// Menolak pengiriman jika token formulir tidak cocok dengan token sesi.
require dirname(__DIR__) . '/csrf.php';
if (!csrf_token_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
}

// Daftar kolom teks dan batas karakter yang harus sesuai dengan tabel SQL.
$fields = ['kecamatan', 'kelurahan', 'kategori', 'nama_fasilitas', 'alamat'];
$values = [];
$limits = [
    'kecamatan' => 100,
    'kelurahan' => 100,
    'kategori' => 100,
    'nama_fasilitas' => 150,
    'alamat' => 255,
];
$errors = [];
// Membersihkan spasi dan memeriksa semua kolom teks yang dikirim pengguna.
foreach ($fields as $field) {
    $value = $_POST[$field] ?? null;
    $values[$field] = is_string($value) ? trim($value) : '';
    if ($values[$field] === '' || mb_strlen($values[$field]) > $limits[$field]) {
        $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' wajib diisi dan tidak boleh melebihi ' . $limits[$field] . ' karakter.';
    }
}

// Memastikan koordinat berupa angka dan berada dalam rentang yang sah.
$latitudeInput = $_POST['latitude'] ?? null;
$longitudeInput = $_POST['longitude'] ?? null;
if (!is_string($latitudeInput) || trim($latitudeInput) === '' || !is_numeric($latitudeInput) || (float) $latitudeInput < -90 || (float) $latitudeInput > 90) {
    $errors[] = 'Latitude harus berupa angka antara -90 dan 90.';
}
if (!is_string($longitudeInput) || trim($longitudeInput) === '' || !is_numeric($longitudeInput) || (float) $longitudeInput < -180 || (float) $longitudeInput > 180) {
    $errors[] = 'Longitude harus berupa angka antara -180 dan 180.';
}

// Jika input tidak valid, tampilkan semua pesan kesalahan tanpa menyimpan data.
if ($errors !== []) {
    http_response_code(422);
    ?>
    <!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Periksa input</title></head>
    <body><h1>Data belum dapat disimpan</h1><ul>
    <?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li><?php endforeach; ?>
    </ul><p><a href="index.php">Kembali ke formulir</a></p></body></html>
    <?php
    exit;
}

// Menyimpan fasilitas baru dengan prepared statement agar input tidak menjadi SQL.
require dirname(__DIR__) . '/db.php';
$latitude = (float) $latitudeInput;
$longitude = (float) $longitudeInput;
$stmt = $conn->prepare(
    'INSERT INTO fasilitas_kesehatan (kecamatan, kelurahan, kategori, nama_fasilitas, alamat, latitude, longitude)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);

// Mengikat nilai ke parameter query dengan tipe teks dan angka yang sesuai.
if ($stmt === false) {
    http_response_code(500);
    exit('Pernyataan penyimpanan tidak dapat disiapkan.');
}

$stmt->bind_param(
    'sssssdd',
    $values['kecamatan'],
    $values['kelurahan'],
    $values['kategori'],
    $values['nama_fasilitas'],
    $values['alamat'],
    $latitude,
    $longitude
);
if (!$stmt->execute()) {
    http_response_code(500);
    exit('Data gagal disimpan. Periksa struktur tabel fasilitas_kesehatan.');
}

// Setelah berhasil, tutup koneksi dan kembali ke halaman daftar.
$stmt->close();
$conn->close();
header('Location: ../index.php?status=saved', true, 303);
exit;
