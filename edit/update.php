<?php
declare(strict_types=1);

// Pembaruan data hanya boleh dilakukan melalui pengiriman formulir POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php', true, 303);
    exit;
}

// Memeriksa token keamanan dan ID data sebelum menerima perubahan.
require dirname(__DIR__) . '/csrf.php';
if (!csrf_token_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('ID data tidak valid.');
}

// Menentukan kolom teks dan batas karakter yang mengikuti struktur tabel.
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
// Membersihkan spasi tepi dan mengumpulkan kesalahan validasi kolom teks.
foreach ($fields as $field) {
    $value = $_POST[$field] ?? null;
    $values[$field] = is_string($value) ? trim($value) : '';
    if ($values[$field] === '' || mb_strlen($values[$field]) > $limits[$field]) {
        $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' wajib diisi dan tidak boleh melebihi ' . $limits[$field] . ' karakter.';
    }
}

// Memastikan latitude dan longitude dapat disimpan sebagai koordinat geografis.
$latitudeInput = $_POST['latitude'] ?? null;
$longitudeInput = $_POST['longitude'] ?? null;
if (!is_string($latitudeInput) || trim($latitudeInput) === '' || !is_numeric($latitudeInput) || (float) $latitudeInput < -90 || (float) $latitudeInput > 90) {
    $errors[] = 'Latitude harus berupa angka antara -90 dan 90.';
}
if (!is_string($longitudeInput) || trim($longitudeInput) === '' || !is_numeric($longitudeInput) || (float) $longitudeInput < -180 || (float) $longitudeInput > 180) {
    $errors[] = 'Longitude harus berupa angka antara -180 dan 180.';
}

// Mengembalikan pesan kesalahan tanpa menjalankan query pembaruan.
if ($errors !== []) {
    http_response_code(422);
    ?>
    <!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Periksa data</title></head>
    <body><h1>Data belum dapat diperbarui</h1><ul>
    <?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li><?php endforeach; ?>
    </ul><p><a href="index.php?id=<?= htmlspecialchars((string) $id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Kembali ke formulir edit</a></p></body></html>
    <?php
    exit;
}

// Memperbarui baris berdasarkan ID dengan prepared statement.
require dirname(__DIR__) . '/db.php';
$latitude = (float) $latitudeInput;
$longitude = (float) $longitudeInput;
$stmt = $conn->prepare(
    'UPDATE fasilitas_kesehatan
     SET kecamatan = ?, kelurahan = ?, kategori = ?, nama_fasilitas = ?, alamat = ?, latitude = ?, longitude = ?
     WHERE id = ?'
);

// Mengikat nilai teks, koordinat desimal, dan ID ke parameter query UPDATE.
if ($stmt === false) {
    http_response_code(500);
    exit('Pernyataan pembaruan tidak dapat disiapkan.');
}

$stmt->bind_param(
    'sssssddi',
    $values['kecamatan'],
    $values['kelurahan'],
    $values['kategori'],
    $values['nama_fasilitas'],
    $values['alamat'],
    $latitude,
    $longitude,
    $id
);
if (!$stmt->execute()) {
    http_response_code(500);
    exit('Data gagal diperbarui. Periksa struktur tabel fasilitas_kesehatan.');
}
// Nol baris berubah dapat berarti nilainya sama atau ID tidak ditemukan;
// query pemeriksaan di bawah membedakan kedua kondisi tersebut.
if ($stmt->affected_rows === 0) {
    $stmt->close();
    $check = $conn->prepare('SELECT id FROM fasilitas_kesehatan WHERE id = ?');
    if ($check === false) {
        http_response_code(500);
        exit('Pemeriksaan data tidak dapat disiapkan.');
    }
    $check->bind_param('i', $id);
    if (!$check->execute()) {
        http_response_code(500);
        exit('Data fasilitas kesehatan tidak dapat diperiksa.');
    }
    $check->store_result();
    if ($check->num_rows === 0) {
        http_response_code(404);
        exit('Data fasilitas kesehatan tidak ditemukan.');
    }
    $check->close();
} else {
    $stmt->close();
}

// Menutup koneksi dan kembali ke daftar dengan pesan pembaruan berhasil.
$conn->close();
header('Location: ../index.php?status=updated', true, 303);
exit;
