# Data Fasilitas Kesehatan

Aplikasi PHP dan MySQL untuk menampilkan, menambah, mengedit, dan menghapus data fasilitas kesehatan di Kota Malang.

## Kolom data

- Kecamatan (`WADMKC`)
- Kelurahan (`WADMKD`)
- Kategori (`NAMOBJ`)
- Nama fasilitas (`REMARK`)
- Alamat (`ALAMAT`)
- Latitude (`LAT_DCML`)
- Longitude (`LONG_DCML`)

Tabel juga memiliki kolom `id` sebagai kunci utama untuk aksi edit dan hapus.

## Menjalankan dengan XAMPP

1. Folder ini berada di `C:\xampp\htdocs\9\Script`.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Di phpMyAdmin, pilih **Import** lalu impor `database.sql`. File ini membuat database baru bernama `kesehatan`, tabel `fasilitas_kesehatan`, dan satu data contoh dari screenshot.
4. Pengaturan awal `db.php` memakai host `localhost`, user `root`, tanpa password, dan database `kesehatan`. Sesuaikan jika pengaturan MySQL berbeda.
5. Buka `http://localhost/9/Script/`.

Form memvalidasi teks wajib diisi dan koordinat latitude (-90 sampai 90) serta longitude (-180 sampai 180), di browser dan server. Aksi tambah, edit, dan hapus menggunakan token CSRF.
