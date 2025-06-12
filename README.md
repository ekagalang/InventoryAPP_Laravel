# Aplikasi Inventaris (Inventory Management App)

Aplikasi Inventaris ini adalah sebuah sistem berbasis web yang dibangun menggunakan **Laravel** untuk mengelola inventaris barang dan aset secara komprehensif. Aplikasi ini dirancang untuk membantu organisasi dalam melacak stok, mengelola pergerakan barang, menangani alur permintaan/peminjaman, serta mengatur hak akses pengguna dengan sistem yang terstruktur dan aman.

Aplikasi ini mencakup berbagai fitur inti yang menjadikannya solusi solid untuk manajemen inventaris, mulai dari pengelolaan data master hingga laporan, notifikasi otomatis, dan audit trail.

![Dashboard Aplikasi Inventaris](https://drive.google.com/uc?export=view&id=1GT4uZTDfMCGzqdNBPvPu1RVOCWe2nuaq)

---

## Fitur Utama

### 1. 👑 Manajemen Peran & Hak Akses (Roles & Permissions)
Sistem keamanan yang fleksibel dan granular menggunakan `spatie/laravel-permission`.
- **Manajemen Pengguna:** Admin dapat melakukan CRUD penuh untuk data pengguna.
- **Manajemen Peran (Roles):** Admin memiliki UI untuk membuat, mengedit, dan menghapus peran (misalnya, Admin, StafGudang, Viewer).
- **Manajemen Hak Akses (Permissions):** Admin dapat secara dinamis membuat, mengedit, dan menghapus hak akses spesifik dari antarmuka web.
- **Assignment Dinamis:** Admin dapat dengan mudah memberikan atau mengubah peran untuk setiap pengguna, serta mengatur hak akses apa saja yang dimiliki oleh setiap peran.

### 2. 📦 Manajemen Data Master & Stok
Fondasi data yang kuat untuk memastikan konsistensi di seluruh aplikasi.
- **Manajemen Barang & Aset:** CRUD penuh untuk data barang, lengkap dengan pembedaan antara **Barang Habis Pakai** dan **Aset** (barang pinjaman).
- **Manajemen Kategori, Unit, dan Lokasi:** CRUD penuh untuk mengelola kategori barang, satuan (Pcs, Box, dll.), dan lokasi penyimpanan fisik.
- **Update Stok Otomatis:** Setiap transaksi (masuk, keluar, koreksi, pengembalian) akan secara otomatis memperbarui jumlah stok utama pada data barang menggunakan Laravel Observer.
- **Koreksi Stok (Stok Opname):** Fitur untuk menyesuaikan jumlah stok di sistem agar sesuai dengan jumlah fisik di lapangan.

### 3. 📑 Sistem Permintaan & Peminjaman Barang
Alur kerja yang terstruktur untuk mengelola permintaan dan peminjaman barang.
- **Alur Ganda:** Pengguna bisa membuat pengajuan untuk **"Minta Barang"** (untuk barang habis pakai) atau **"Pinjam Aset"**.
- **Proses Persetujuan (Approval Workflow):** Admin atau Staf Gudang dapat meninjau, **menyetujui (approve)**, atau **menolak (reject)** setiap pengajuan.
- **Pemrosesan & Pengeluaran Barang:** Pengajuan yang disetujui dapat diproses, yang akan membuat catatan barang keluar dan mengurangi stok.
- **Alur Pengembalian Aset:** Aset yang dipinjam dapat dicatat saat dikembalikan, yang akan menambah kembali stok secara otomatis.
- **Pembatalan oleh Pengguna:** Pengguna dapat membatalkan pengajuannya sendiri selama statusnya masih "Diajukan".

### 4. 📊 Dashboard, Laporan & Notifikasi
Menyajikan data menjadi informasi yang berguna dan memberikan peringatan proaktif.
- **Dashboard Interaktif:** Menampilkan ringkasan statistik kunci dan **grafik visual** untuk komposisi status barang serta tren pergerakan stok.
- **Laporan Lengkap:** Laporan Stok Barang, Barang Masuk, dan Barang Keluar dengan fitur filter.
- **Sistem Notifikasi:** Pemberitahuan otomatis untuk stok minimum dan setiap tahapan alur pengajuan barang, tampil di navbar dan memiliki halaman riwayat notifikasi.

### 5. 🛡️ Pelacakan & Riwayat (Audit Trail)
Meningkatkan akuntabilitas dan keamanan dengan mencatat semua aktivitas penting.
- **Log Aktivitas Pengguna:** Menggunakan `spatie/laravel-activitylog` untuk secara otomatis mencatat setiap aksi Create, Update, dan Delete pada data-data penting (Barang, Kategori, User, dll.).
- **Tampilan Log:** Admin dapat melihat riwayat semua aktivitas sistem, termasuk detail perubahan data (nilai sebelum dan sesudah), siapa pelakunya, dan kapan terjadinya.

---

## Teknologi yang Digunakan

- **Backend:** Laravel Framework
- **Frontend:** Blade, Bootstrap 5, Chart.js
- **Database:** MySQL
- **Paket Utama:**
    - `laravel/breeze` (untuk scaffolding otentikasi awal)
    - `spatie/laravel-permission` (untuk manajemen Peran & Hak Akses)
    - `maatwebsite/excel` (untuk fungsionalitas Ekspor ke Excel)
    - `spatie/laravel-activitylog` (untuk Audit Trail/Log Aktivitas)

---

## Cara Instalasi & Setup Lokal

Berikut adalah langkah-langkah untuk menjalankan proyek ini di lingkungan development lokal.

1.  **Clone repositori ini:**
    ```bash
    git clone [URL_REPO_ANDA]
    ```

2.  **Pindah ke direktori proyek:**
    ```bash
    cd [NAMA_FOLDER_PROYEK]
    ```

3.  **Install dependensi Composer:**
    ```bash
    composer install
    ```

4.  **Salin file `.env.example` menjadi `.env`:**
    ```bash
    cp .env.example .env
    ```

5.  **Buat Application Key baru:**
    ```bash
    php artisan key:generate
    ```

6.  **Konfigurasi koneksi database Anda di dalam file `.env`**. Pastikan Anda sudah membuat database kosong untuk proyek ini.

7.  **Jalankan migrasi dan seeder:**
    Perintah ini akan membuat semua tabel di database dan mengisinya dengan data awal (termasuk peran, hak akses, dan user admin default).
    ```bash
    php artisan migrate:fresh --seed
    ```

8.  **Buat symbolic link untuk storage:**
    Ini penting agar file yang di-upload (seperti gambar barang) bisa diakses dari web.
    ```bash
    php artisan storage:link
    ```

9.  **Install dependensi frontend:**
    ```bash
    npm install
    ```

10. **Jalankan server development:**
    Buka dua terminal.
    * Di terminal pertama, jalankan server Laravel:
        ```bash
        php artisan serve
        ```
    * Di terminal kedua, jalankan Vite untuk kompilasi aset:
        ```bash
        npm run dev
        ```

11. **Selesai!**
    * Buka aplikasi di `http://localhost:8000`.
    * Login dengan akun Admin default yang dibuat oleh seeder:
        * **Email:** `admin@example.com`
        * **Password:** `password123` (atau sesuai yang Anda atur di `RolesAndPermissionsSeeder.php`)
    * Anda juga bisa mendaftar sebagai pengguna baru.
