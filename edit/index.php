<?php
declare(strict_types=1);

require dirname(__DIR__) . '/db.php';
require dirname(__DIR__) . '/csrf.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('ID data tidak valid.');
}

$stmt = $conn->prepare(
    'SELECT kecamatan, kelurahan, kategori, nama_fasilitas, alamat, latitude, longitude
     FROM fasilitas_kesehatan WHERE id = ?'
);
if ($stmt === false) {
    http_response_code(500);
    exit('Pernyataan pembacaan tidak dapat disiapkan.');
}

$stmt->bind_param('i', $id);
if (!$stmt->execute()) {
    http_response_code(500);
    exit('Data fasilitas kesehatan tidak dapat dibaca.');
}

$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
$conn->close();

if ($row === null) {
    http_response_code(404);
    exit('Data fasilitas kesehatan tidak ditemukan.');
}

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Fasilitas Kesehatan</title>
</head>
<body>
    <h2>Edit Fasilitas Kesehatan</h2>
    <form action="update.php" method="post" id="form-edit">
        <input type="hidden" name="id" value="<?= e($id) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="kecamatan">Kecamatan:</label><br>
        <input type="text" id="kecamatan" name="kecamatan" maxlength="100" required value="<?= e($row['kecamatan']) ?>"><br>
        <label for="kelurahan">Kelurahan:</label><br>
        <input type="text" id="kelurahan" name="kelurahan" maxlength="100" required value="<?= e($row['kelurahan']) ?>"><br>
        <label for="kategori">Kategori:</label><br>
        <input type="text" id="kategori" name="kategori" maxlength="100" required value="<?= e($row['kategori']) ?>"><br>
        <label for="nama_fasilitas">Nama Fasilitas:</label><br>
        <input type="text" id="nama_fasilitas" name="nama_fasilitas" maxlength="150" required value="<?= e($row['nama_fasilitas']) ?>"><br>
        <label for="alamat">Alamat:</label><br>
        <input type="text" id="alamat" name="alamat" maxlength="255" required value="<?= e($row['alamat']) ?>"><br>
        <label for="latitude">Latitude:</label><br>
        <input type="number" id="latitude" name="latitude" min="-90" max="90" step="any" required value="<?= e($row['latitude']) ?>"><br>
        <label for="longitude">Longitude:</label><br>
        <input type="number" id="longitude" name="longitude" min="-180" max="180" step="any" required value="<?= e($row['longitude']) ?>"><br><br>
        <input type="submit" value="Simpan">
    </form>
    <p id="informasi"></p>
    <p><a href="../index.php">Kembali</a></p>
    <script>
        document.getElementById('form-edit').addEventListener('submit', function (event) {
            const form = event.currentTarget;
            const latitude = Number(form.elements.latitude.value);
            const longitude = Number(form.elements.longitude.value);
            let pesan = '';

            if (!form.elements.kecamatan.value.trim()) pesan = 'Nama kecamatan wajib diisi.';
            else if (!form.elements.kelurahan.value.trim()) pesan = 'Nama kelurahan wajib diisi.';
            else if (!form.elements.kategori.value.trim()) pesan = 'Kategori wajib diisi.';
            else if (!form.elements.nama_fasilitas.value.trim()) pesan = 'Nama fasilitas wajib diisi.';
            else if (!form.elements.alamat.value.trim()) pesan = 'Alamat wajib diisi.';
            else if (!Number.isFinite(latitude) || latitude < -90 || latitude > 90) pesan = 'Latitude harus berupa angka antara -90 dan 90.';
            else if (!Number.isFinite(longitude) || longitude < -180 || longitude > 180) pesan = 'Longitude harus berupa angka antara -180 dan 180.';

            document.getElementById('informasi').textContent = pesan;
            if (pesan) event.preventDefault();
        });
    </script>
</body>
</html>
