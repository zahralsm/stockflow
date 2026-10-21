# StockFlow 🌿

### Stok rapi, transaksi tercatat, kerja jadi lebih ringan.

**Inventory & Sales Management System** · Laravel 12 · Bootstrap 5 · MySQL / MariaDB

StockFlow adalah aplikasi web untuk mengelola persediaan dan transaksi dalam satu tempat. Mulai dari menambahkan produk, mencatat barang masuk, sampai melihat penjualan—semuanya bisa dilakukan lewat dashboard yang simpel dan nyaman dipakai.

Project ini dibuat untuk **UTS Web Programming 2** dengan tema **Integrated Laravel Project: Inventory & Sales Management System**.

> Fokusnya bukan cuma tampilan. Data tersimpan di database, akses dibatasi sesuai role, dan perubahan stok mengikuti transaksi yang berhasil diproses.

---

## 🧭 Mau lihat bagian mana?

- [Fitur utama](#-fitur-utama)
- [Hak akses](#-siapa-bisa-melakukan-apa)
- [Teknologi](#-teknologi-yang-dipakai)
- [Instalasi](#-cara-menjalankan-di-laptop)
- [Akun demo](#-akun-demo-lokal)
- [Alur penggunaan](#-coba-alur-ini-dulu)
- [Aturan stok](#-bagaimana-stok-dijaga)
- [Struktur project](#-peta-project)
- [Pengujian](#-pengujian-manual)
- [Troubleshooting](#-kalau-ada-kendala)

## ✨ Fitur utama

| Fitur                  | Yang bisa dilakukan                                                                        |
| ---------------------- | ------------------------------------------------------------------------------------------ |
| Login & logout         | Masuk dengan username dan password, dengan role Admin atau Staff.                          |
| Dashboard              | Lihat total produk, penjualan hari ini dan bulan ini, jumlah transaksi, serta stok rendah. |
| Kategori               | Tambah, lihat, ubah, dan hapus kategori dengan nama unik.                                  |
| Produk                 | Kelola SKU, kategori, nama, harga beli, harga jual, dan batas stok minimum.                |
| Pemasok                | Simpan nama, telepon, dan email pemasok.                                                   |
| Pembelian              | Catat barang masuk dari pemasok dengan beberapa produk dalam satu transaksi.               |
| Penjualan              | Catat beberapa produk sekaligus dengan pengecekan stok di server.                          |
| Pembatalan             | Batalkan transaksi dengan alasan dan penyesuaian stok.                                     |
| Kelola user            | Admin dapat membuat dan mengelola akun pengguna.                                           |
| Laporan                | Lihat pembelian atau penjualan berdasarkan tanggal, status, dan nomor transaksi.           |
| Pencarian & pagination | Temukan data lebih cepat tanpa menampilkan seluruh baris sekaligus.                        |

Tampilannya memakai tema **Minimalist Corporate**: warna putih, mint, dan emerald, dengan sidebar, kartu ringkasan, badge status, serta tabel yang bisa digeser di layar kecil.

## 🔐 Siapa bisa melakukan apa?

| Aktivitas                                     | Admin / Owner | Staff |
| --------------------------------------------- | :-----------: | :---: |
| Membuka dashboard dan melihat produk          |      ✅       |  ✅   |
| Membuat serta melihat pembelian dan penjualan |      ✅       |  ✅   |
| Mengelola kategori, produk, dan pemasok       |      ✅       |   —   |
| Mengelola akun pengguna                       |      ✅       |   —   |
| Membuka laporan                               |      ✅       |   —   |
| Membatalkan transaksi                         |      ✅       |   —   |

Pembatasan akses dilakukan melalui middleware di server. Jadi, mengetik URL halaman Admin secara langsung tetap tidak memberikan akses kepada Staff.

## 🛠 Teknologi yang dipakai

| Bagian                  | Teknologi                                     |
| ----------------------- | --------------------------------------------- |
| Backend                 | PHP, Laravel 12                               |
| Tampilan                | Blade, Bootstrap 5 melalui CDN, FontAwesome   |
| Interaksi form          | JavaScript                                    |
| Database                | MySQL / MariaDB dengan tabel transaksi InnoDB |
| Pengelolaan dependensi  | Composer                                      |
| Lingkungan pengembangan | XAMPP dan Laravel development server          |

Internet diperlukan untuk mengunduh dependensi serta memuat Bootstrap dan FontAwesome dari CDN. Tampilan yang memakai CDN ini tidak membutuhkan proses build npm.

## 🚀 Cara menjalankan di laptop

Panduan ini menggunakan **Windows + XAMPP**. Semua perintah di bawah dijalankan melalui tombol **Shell** pada XAMPP Control Panel. Kalau project dan database sudah siap, langsung ke langkah 5.

### 1. Siapkan alatnya

Pastikan XAMPP, Composer, dan PHP yang sesuai dengan dependensi project sudah tersedia. Buka **XAMPP Control Panel → Shell**, lalu cek:

```bash
php -v
composer --version
```

### 2. Letakkan project

Ekstrak atau salin project ke:

```text
C:\xampp\htdocs\stockflow
```

Di **Shell XAMPP**, masuk ke folder project:

```bat
cd /d C:\xampp\htdocs\stockflow
```

Pasang dependensi sesuai versi pada `composer.lock`:

```bash
composer install
composer check-platform-reqs
```

### 3. Siapkan konfigurasi lokal

Untuk instalasi baru yang belum mempunyai `.env`, salin `.env.example` melalui **Shell XAMPP**:

```bat
copy .env.example .env
```

Jangan menimpa `.env` jika project sudah dikonfigurasi dan berjalan. Sesuaikan bagian berikut pada `.env`:

```dotenv
APP_NAME=StockFlow
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stockflow
DB_USERNAME=root
DB_PASSWORD=
```

Konfigurasi database di atas memakai pengaturan lokal XAMPP tanpa password untuk `root`. Kalau pengaturan MySQL kamu berbeda, sesuaikan username, password, dan port-nya.

Untuk instalasi baru dengan `APP_KEY` yang masih kosong, jalankan:

```bash
php artisan key:generate
```

### 4. Siapkan database

Nyalakan **Apache dan MySQL** di XAMPP, lalu buka:

```text
http://localhost/phpmyadmin
```

Buat database bernama **stockflow**, lalu pilih salah satu cara berikut.

**Pilihan A — Instalasi baru dengan data demo**

Gunakan database kosong, kemudian jalankan:

```bash
php artisan migrate --seed
```

Seeder menyiapkan akun demo dan data contoh yang disediakan project. Data ini membantu saat mencoba aplikasi; aktivitas berikutnya tetap disimpan ke database.

**Pilihan B — Menggunakan backup `stockflow.sql`**

Pilih database kosong di phpMyAdmin, buka tab **Import**, lalu unggah `stockflow.sql`. Gunakan akun yang tersimpan dalam backup tersebut. Tidak perlu menjalankan seeder lagi untuk memulihkan data backup.

> Pilih salah satu cara. Jangan menjalankan `migrate:fresh` pada database yang masih berisi data penting karena perintah itu menghapus tabel yang ada.

### 5. Jalankan lewat Shell XAMPP

Kalau project dan database sudah siap, setiap mau membuka web cukup ikuti langkah ini:

1. Buka **XAMPP Control Panel**.
2. Klik **Start** pada **MySQL**.
3. Klik tombol **Shell** di sebelah kanan XAMPP.
4. Masuk ke folder project dengan perintah berikut, lalu tekan Enter:

```bat
cd /d C:\xampp\htdocs\stockflow
```

5. Jalankan server Laravel:

```bash
php artisan serve
```

6. Buka **http://127.0.0.1:8000** di browser, lalu login.

Biarkan **Shell XAMPP tetap terbuka** selama web digunakan. Untuk menghentikan server, klik jendela Shell lalu tekan **Ctrl+C**.

Tidak perlu menjalankan migrasi atau seeder lagi setiap membuka web. Kalau baru mengubah konfigurasi `.env`, jalankan `php artisan config:clear` sebelum menyalakan server.

**Catatan:** Apache hanya perlu dinyalakan jika ingin membuka phpMyAdmin melalui `http://localhost/phpmyadmin`. Web StockFlow dijalankan oleh `php artisan serve`.

## 👤 Akun demo lokal

Akun berikut tersedia apabila seeder akun demo sudah dijalankan pada lingkungan lokal:

| Role          | Username | Password   |
| ------------- | -------- | ---------- |
| Admin / Owner | `admin`  | `admin123` |
| Staff         | `staff`  | `staff123` |

Ini kredensial contoh untuk pengujian lokal, bukan akun untuk deployment publik. Jika password akun sudah diubah, gunakan password terbaru.

## 🛒 Coba alur ini dulu

1. Login sebagai **Admin**.
2. Tambahkan **kategori**, **pemasok**, lalu **produk**.
3. Buat **pembelian** untuk memasukkan stok. Produk baru mulai dari stok nol.
4. Buat **penjualan** menggunakan produk yang stoknya tersedia.
5. Lihat perubahan stok di halaman Produk serta ringkasannya di Dashboard.
6. Buka Laporan untuk memeriksa transaksi berdasarkan tanggal dan status.
7. Jika transaksi perlu dibatalkan, gunakan menu Pembatalan dan isi alasannya.

Staff dapat melanjutkan kegiatan pembelian dan penjualan menggunakan data master yang disiapkan Admin.

## 📦 Bagaimana stok dijaga?

### Barang masuk dan keluar

- Pembelian berhasil → stok bertambah sesuai jumlah barang.
- Penjualan berhasil → stok berkurang sesuai jumlah barang.
- Penjualan dibatalkan → stok dikembalikan.
- Pembelian dibatalkan → stok dikurangi kembali, asalkan stok saat ini mencukupi.

Contoh: pembelian 5 unit lalu penjualan 3 unit menyisakan 2 unit. Pembelian 5 unit tadi tidak boleh dibatalkan karena akan membuat stok menjadi negatif.

### Satu transaksi harus utuh

Penyimpanan header transaksi, rincian barang, dan pembaruan stok dilakukan menggunakan `DB::transaction`. Jika proses gagal, perubahan database dalam transaksi tersebut dibatalkan.

Produk dikunci menggunakan `lockForUpdate()` saat stok diperiksa dan diperbarui. Ini membantu menjaga konsistensi ketika beberapa proses mengakses produk yang sama.

### Angka final berasal dari server

Subtotal dan total dihitung di server berdasarkan harga produk. Angka yang tampil di browser adalah estimasi. Jika stok tidak cukup, server menolak transaksi dengan pesan validasi yang jelas.

Stok rendah ditandai ketika **stock ≤ minimum_stock**. Peringatan ini membantu menentukan produk yang perlu dibeli lagi; penjualan tetap mengikuti ketersediaan stok sebenarnya.

### Riwayat tetap tersimpan

Pembatalan mengubah status transaksi menjadi `cancelled`, menyimpan alasan dan petugas pembatalan, lalu menyesuaikan stok. Transaksi tidak dihapus dari riwayat dan tidak bisa dibatalkan dua kali.

## 🗂 Peta project

| Lokasi                                    | Fungsinya                                                    |
| ----------------------------------------- | ------------------------------------------------------------ |
| `app/Http/Controllers/`                   | Logika autentikasi, CRUD, transaksi, dashboard, dan laporan. |
| `app/Http/Middleware/AdminMiddleware.php` | Pembatasan halaman khusus Admin.                             |
| `app/Models/`                             | Model data dan relasi antartabel.                            |
| `database/migrations/`                    | Struktur tabel database.                                     |
| `database/seeders/`                       | Akun dan data contoh untuk pengujian.                        |
| `resources/views/layouts/app.blade.php`   | Layout utama, sidebar, navbar, dan notifikasi.               |
| `resources/views/`                        | Tampilan halaman menggunakan Blade.                          |
| `routes/web.php`                          | Daftar route dan middleware aplikasi.                        |

Tabel utama yang digunakan: `users`, `categories`, `products`, `suppliers`, `purchases`, `purchase_items`, `sales`, dan `sale_items`.

Satu kategori memiliki banyak produk. Satu pembelian terhubung ke pemasok dan petugas, sedangkan satu penjualan terhubung ke petugas. Setiap transaksi memiliki beberapa detail barang yang masing-masing merujuk ke produk.

## 🛡 Perlindungan yang diterapkan

- CSRF token pada form yang mengubah data.
- Validasi input di server.
- Pembatasan mass assignment melalui `$fillable` dan penetapan field tertentu secara eksplisit.
- Password disimpan dalam bentuk hash.
- Pembatasan percobaan login.
- Middleware untuk akses khusus Admin.
- Foreign key dan pemeriksaan relasi sebelum menghapus data master.
- Konfigurasi sensitif ditempatkan pada `.env`.

Saat membagikan source code, gunakan `.env.example` untuk contoh konfigurasi. Jangan unggah `.env` pribadi atau backup database yang berisi data sensitif ke repository publik.

## 🧪 Pengujian manual

Berikut hasil pengujian lokal yang telah dikonfirmasi selama pengembangan:

| Skenario                                                          | Hasil yang diamati                                                           |
| ----------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| Penjualan beberapa produk dengan salah satu stok tidak cukup      | Penjualan ditolak, tidak ada transaksi baru, dan stok kedua produk tetap.    |
| Pembatalan penjualan                                              | Stok kembali dan status menjadi Dibatalkan; pembatalan ulang tidak tersedia. |
| Staff membuka `/users`, `/reports`, dan `/cancellations` langsung | Akses ditolak.                                                               |
| Staff membuka Produk, Pembelian, dan Penjualan                    | Halaman dapat diakses.                                                       |
| Membatalkan pembelian 5 unit setelah 3 unit terjual               | Stok tetap 2; pembatalan tidak mengurangi stok menjadi negatif.              |
| Laporan penjualan dengan filter Dibatalkan                        | Transaksi tampil dan total penjualan dikonfirmasi pada filter tersebut Rp0.  |

Catatan: hasil di atas berasal dari pengujian manual. Uji permintaan bersamaan dan pengujian otomatis belum didokumentasikan di README ini.

## 🔧 Kalau ada kendala

| Kendala                                       | Coba langkah ini                                                                       |
| --------------------------------------------- | -------------------------------------------------------------------------------------- |
| Halaman tidak bisa dibuka                     | Pastikan `php artisan serve` masih berjalan dan buka alamat yang tercetak di terminal. |
| Koneksi database gagal                        | Pastikan MySQL aktif, database tersedia, dan konfigurasi `.env` sesuai.                |
| Perubahan `.env` belum terbaca                | Jalankan `php artisan config:clear`.                                                   |
| Error 419 saat mengirim form                  | Muat ulang halaman, login kembali bila perlu, lalu kirim ulang form yang baru.         |
| Tampilan belum berubah setelah mengedit Blade | Simpan file, refresh browser, dan bila perlu jalankan `php artisan view:clear`.        |
| CSS atau ikon tidak muncul                    | Periksa koneksi internet karena aset tampilan dimuat melalui CDN.                      |
| Halaman Admin menunjukkan 403                 | Pastikan akun yang digunakan memiliki role Admin.                                      |
| Tidak bisa menghapus data master              | Periksa apakah data masih dipakai produk atau riwayat transaksi.                       |

---
