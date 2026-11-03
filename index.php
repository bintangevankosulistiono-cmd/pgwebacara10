<?php
declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/csrf.php';

$result = $conn->query(
    'SELECT id, kecamatan, kelurahan, kategori, nama_fasilitas, alamat, latitude, longitude
     FROM fasilitas_kesehatan ORDER BY id'
);

if ($result === false) {
    http_response_code(500);
    exit('Data tidak dapat dibaca. Pastikan tabel fasilitas_kesehatan sudah dibuat.');
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
    <title>Data Fasilitas Kesehatan</title>
</head>
<body>
    <h1>Data Fasilitas Kesehatan Kota Malang</h1>
    <p><a href="input/index.php">Tambah data</a></p>
    <?php
    $messages = [
        'saved' => 'Data fasilitas kesehatan berhasil disimpan.',
        'updated' => 'Data fasilitas kesehatan berhasil diperbarui.',
        'deleted' => 'Data fasilitas kesehatan berhasil dihapus.',
    ];
    $status = $_GET['status'] ?? '';
    if (is_string($status) && isset($messages[$status])):
    ?>
        <p role="status"><?= e($messages[$status]) ?></p>
    <?php endif; ?>
    <?php if ($result->num_rows > 0): ?>
        <table border="1">
            <thead>
                <tr>
                    <th>Kecamatan</th>
                    <th>Kelurahan</th>
                    <th>Kategori</th>
                    <th>Nama Fasilitas</th>
                    <th>Alamat</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th colspan="2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= e($row['kecamatan']) ?></td>
                        <td><?= e($row['kelurahan']) ?></td>
                        <td><?= e($row['kategori']) ?></td>
                        <td><?= e($row['nama_fasilitas']) ?></td>
                        <td><?= e($row['alamat']) ?></td>
                        <td><?= e($row['latitude']) ?></td>
                        <td><?= e($row['longitude']) ?></td>
                        <td><a href="edit/index.php?id=<?= e($row['id']) ?>">edit</a></td>
                        <td>
                            <form action="delete.php" method="post" onsubmit="return confirm('Hapus data fasilitas kesehatan ini?');">
                                <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <button type="submit">hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>0 results</p>
    <?php endif; ?>
</body>
</html>
<?php
$result->free();
$conn->close();
