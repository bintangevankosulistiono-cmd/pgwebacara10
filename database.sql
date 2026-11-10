-- Membuat database aplikasi dengan encoding yang mendukung berbagai karakter.
CREATE DATABASE IF NOT EXISTS kesehatan
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE kesehatan;

-- Menyimpan data fasilitas, termasuk koordinat untuk penanda pada peta.
CREATE TABLE IF NOT EXISTS fasilitas_kesehatan (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kecamatan VARCHAR(100) NOT NULL,
  kelurahan VARCHAR(100) NOT NULL,
  kategori VARCHAR(100) NOT NULL,
  nama_fasilitas VARCHAR(150) NOT NULL,
  alamat VARCHAR(255) NOT NULL,
  latitude DECIMAL(13, 10) NOT NULL,
  longitude DECIMAL(13, 10) NOT NULL,
  PRIMARY KEY (id),
  CHECK (latitude BETWEEN -90 AND 90),
  CHECK (longitude BETWEEN -180 AND 180)
);

-- Memasukkan satu data contoh hanya jika data yang sama belum tersedia.
INSERT INTO fasilitas_kesehatan
  (kecamatan, kelurahan, kategori, nama_fasilitas, alamat, latitude, longitude)
SELECT
  'Blimbing',
  'Purwantoro',
  'Kesehatan',
  'Puskesmas Cisadea',
  'Jl. Cisadea No. 19',
  -7.9554028156,
  112.6436047965
WHERE NOT EXISTS (
  SELECT 1
  FROM fasilitas_kesehatan
  WHERE nama_fasilitas = 'Puskesmas Cisadea'
    AND alamat = 'Jl. Cisadea No. 19'
);
