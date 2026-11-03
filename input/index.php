<?php
declare(strict_types=1);

require dirname(__DIR__) . '/csrf.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Fasilitas Kesehatan</title>
</head>
<body>
    <h2>Tambah Fasilitas Kesehatan</h2>
    <form action="save.php" method="post" id="form-input">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="kecamatan">Kecamatan:</label><br>
        <input type="text" id="kecamatan" name="kecamatan" maxlength="100" required><br>
        <label for="kelurahan">Kelurahan:</label><br>
        <input type="text" id="kelurahan" name="kelurahan" maxlength="100" required><br>
        <label for="kategori">Kategori:</label><br>
        <input type="text" id="kategori" name="kategori" maxlength="100" required><br>
        <label for="nama_fasilitas">Nama Fasilitas:</label><br>
        <input type="text" id="nama_fasilitas" name="nama_fasilitas" maxlength="150" required><br>
        <label for="alamat">Alamat:</label><br>
        <input type="text" id="alamat" name="alamat" maxlength="255" required><br>
        <label for="latitude">Latitude:</label><br>
        <input type="number" id="latitude" name="latitude" min="-90" max="90" step="any" required><br>
        <label for="longitude">Longitude:</label><br>
        <input type="number" id="longitude" name="longitude" min="-180" max="180" step="any" required><br><br>
        <input type="submit" value="Simpan">
    </form>
    <p id="informasi"></p>
    <p><a href="../index.php">Kembali</a></p>
    <script>
        document.getElementById('form-input').addEventListener('submit', function (event) {
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
