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

$rows = $result->fetch_all(MYSQLI_ASSOC);
$mapPoints = [];
$mapPointIds = [];
foreach ($rows as $row) {
    $latitude = (float) $row['latitude'];
    $longitude = (float) $row['longitude'];
    if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
        continue;
    }

    $mapPoints[] = [
        'id' => (int) $row['id'],
        'nama' => (string) $row['nama_fasilitas'],
        'kategori' => (string) $row['kategori'],
        'alamat' => (string) $row['alamat'],
        'latitude' => $latitude,
        'longitude' => $longitude,
    ];
    $mapPointIds[(int) $row['id']] = true;
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
    <style>
        body { margin: 0; color: #1f2937; background: #f3f6f8; font-family: Arial, sans-serif; }
        main { max-width: 1200px; margin: 0 auto; padding: 24px; }
        h1 { margin-top: 0; }
        .toolbar { margin-bottom: 20px; }
        .panel { margin: 18px 0; padding: 18px; background: #fff; border-radius: 10px; box-shadow: 0 2px 10px #10203012; }
        #map { width: 100%; height: 440px; border-radius: 8px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 9px 10px; border: 1px solid #d8e0e7; text-align: left; vertical-align: top; }
        th { background: #edf3f7; }
        td form { display: inline; }
        button, .button-link { display: inline-block; padding: 6px 9px; border: 0; border-radius: 5px; color: #fff; background: #176b87; font: inherit; text-decoration: none; cursor: pointer; }
        td form button { background: #a33333; }
        .map-popup h3 { margin: 0 0 6px; }
        @media (max-width: 600px) { main { padding: 14px; } #map { height: 360px; } }
    </style>
</head>
<body>
<main>
    <h1>Data Fasilitas Kesehatan Kota Malang</h1>
    <p class="toolbar"><a class="button-link" href="input/index.php">Tambah data</a></p>
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
    <?php if ($rows !== []): ?>
        <section class="panel" aria-labelledby="map-heading">
            <h2 id="map-heading">Peta Lokasi Fasilitas</h2>
            <?php if ($mapPoints !== []): ?>
                <div id="map" aria-label="Peta lokasi fasilitas kesehatan"></div>
            <?php else: ?>
                <p>Belum ada koordinat yang valid untuk ditampilkan pada peta.</p>
            <?php endif; ?>
        </section>
        <section class="panel" aria-labelledby="table-heading">
        <h2 id="table-heading">Daftar Fasilitas</h2>
        <div class="table-wrap"><table>
            <thead>
                <tr>
                    <th>Kecamatan</th>
                    <th>Kelurahan</th>
                    <th>Kategori</th>
                    <th>Nama Fasilitas</th>
                    <th>Alamat</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Peta</th>
                    <th colspan="2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['kecamatan']) ?></td>
                        <td><?= e($row['kelurahan']) ?></td>
                        <td><?= e($row['kategori']) ?></td>
                        <td><?= e($row['nama_fasilitas']) ?></td>
                        <td><?= e($row['alamat']) ?></td>
                        <td><?= e($row['latitude']) ?></td>
                        <td><?= e($row['longitude']) ?></td>
                        <td><?php if (isset($mapPointIds[(int) $row['id']])): ?><button type="button" class="show-on-map" data-map-id="<?= e($row['id']) ?>">Lihat di peta</button><?php else: ?>Koordinat tidak valid<?php endif; ?></td>
                        <td><a href="edit/index.php?id=<?= e($row['id']) ?>">edit</a></td>
                        <td>
                            <form action="delete.php" method="post" onsubmit="return confirm('Hapus data fasilitas kesehatan ini?');">
                                <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <button type="submit">hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table></div>
        </section>
    <?php else: ?>
        <p>0 results</p>
    <?php endif; ?>
    <?php if ($mapPoints !== []): ?>
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
    <script>
        const facilityPoints = <?= json_encode($mapPoints, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>;
        const map = L.map('map');
        const streetLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });
        const imageryLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: 'Tiles &copy; Esri'
        });
        streetLayer.addTo(map);

        const facilityLayer = L.featureGroup().addTo(map);
        const markersById = new Map();
        facilityPoints.forEach((facility) => {
            const marker = L.marker([facility.latitude, facility.longitude]).addTo(facilityLayer);
            const popup = document.createElement('div');
            popup.className = 'map-popup';
            const title = document.createElement('h3');
            title.textContent = facility.nama;
            const category = document.createElement('p');
            category.textContent = 'Kategori: ' + facility.kategori;
            const address = document.createElement('p');
            address.textContent = 'Alamat: ' + facility.alamat;
            const coordinates = document.createElement('small');
            coordinates.textContent = facility.latitude + ', ' + facility.longitude;
            popup.append(title, category, address, coordinates);
            marker.bindPopup(popup);
            markersById.set(String(facility.id), marker);
        });

        L.control.layers(
            { 'Peta jalan': streetLayer, 'Citra satelit': imageryLayer },
            { 'Fasilitas kesehatan': facilityLayer },
            { collapsed: false }
        ).addTo(map);
        L.control.scale({ maxWidth: 150, position: 'bottomright' }).addTo(map);

        const bounds = facilityLayer.getBounds();
        if (facilityPoints.length === 1) {
            map.setView(bounds.getCenter(), 15);
        } else {
            map.fitBounds(bounds, { padding: [24, 24], maxZoom: 15 });
        }

        document.querySelectorAll('.show-on-map').forEach((button) => {
            button.addEventListener('click', () => {
                const marker = markersById.get(button.dataset.mapId);
                if (!marker) return;
                map.setView(marker.getLatLng(), 16);
                marker.openPopup();
                document.getElementById('map').scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
    </script>
    <?php endif; ?>
</main>
</body>
</html>
<?php
$result->free();
$conn->close();
