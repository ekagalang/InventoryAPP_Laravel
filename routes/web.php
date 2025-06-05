<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman utama, bisa mengarahkan ke login atau dashboard
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard'); // Atau barang.index jika itu dashboard utama Anda
    }
    return view('auth.login');
})->name('home');

// Dashboard bawaan Breeze (sudah otomatis dilindungi auth dari definisinya di auth.php atau di sini)
Route::get('/dashboard', [DashboardController::class, 'index'])
     ->middleware(['auth', 'verified'])
     ->name('dashboard');


// Grup route yang memerlukan otentikasi
Route::middleware(['auth', 'verified'])->group(function () { // Menggunakan 'auth' dan 'verified' (opsional jika ingin email terverifikasi)
    // Route untuk profil pengguna
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Resource routes untuk modul CRUD Anda
    Route::resource('barang', BarangController::class);
    Route::resource('kategori', KategoriController::class);
    Route::resource('unit', UnitController::class);
    Route::resource('lokasi', LokasiController::class);

    // === ROUTE UNTUK MANAJEMEN STOK ===
    Route::get('/stok/pergerakan', [StockMovementController::class, 'index'])->name('stok.pergerakan.index'); // Daftar Riwayat Pergerakan Stok
    Route::get('/stok/masuk/create', [StockMovementController::class, 'createMasuk'])->name('stok.masuk.create'); // Form Barang Masuk
    Route::post('/stok/masuk', [StockMovementController::class, 'storeMasuk'])->name('stok.masuk.store'); // Simpan Barang Masuk

    Route::get('/stok/keluar/create', [StockMovementController::class, 'createKeluar'])->name('stok.keluar.create');
    Route::post('/stok/keluar', [StockMovementController::class, 'storeKeluar'])->name('stok.keluar.store');

    // TAMBAHKAN ROUTE INI UNTUK KOREKSI STOK:
    Route::get('/stok/koreksi/create', [StockMovementController::class, 'createAdjustment'])->name('stok.koreksi.create');
    Route::post('/stok/koreksi', [StockMovementController::class, 'storeAdjustment'])->name('stok.koreksi.store');

        // === ROUTE UNTUK MANAJEMEN PENGGUNA OLEH ADMIN ===
    // Kita bisa grouping dengan prefix 'admin' agar lebih rapi URL-nya
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        // Anda bisa tambahkan route lain khusus admin di sini
        // === ROUTE UNTUK MANAJEMEN PENGAJUAN BARANG OLEH ADMIN/STAF ===
        Route::get('/pengajuan-barang', [ItemRequestController::class, 'adminIndex'])->name('pengajuan.barang.index'); // Daftar semua pengajuan
        Route::get('/pengajuan-barang/{itemRequest}', [ItemRequestController::class, 'adminShow'])->name('pengajuan.barang.show'); // Detail pengajuan
        Route::post('/pengajuan-barang/{itemRequest}/approve', [ItemRequestController::class, 'approve'])->name('pengajuan.barang.approve'); // Aksi setujui
        Route::post('/pengajuan-barang/{itemRequest}/reject', [ItemRequestController::class, 'reject'])->name('pengajuan.barang.reject');   // Aksi tolak
        Route::post('/pengajuan-barang/{itemRequest}/process', [ItemRequestController::class, 'process'])->name('pengajuan.barang.process');
    });

    // === ROUTE UNTUK PENGAJUAN BARANG OLEH PENGGUNA ===
    Route::get('/pengajuan-barang', [ItemRequestController::class, 'myRequests'])->name('pengajuan.barang.index'); // Daftar pengajuan milik pengguna
    Route::get('/pengajuan-barang/create', [ItemRequestController::class, 'create'])->name('pengajuan.barang.create'); // Form buat pengajuan
    Route::post('/pengajuan-barang', [ItemRequestController::class, 'store'])->name('pengajuan.barang.store');     // Simpan pengajuan baru
    // Nanti kita bisa tambahkan route untuk show detail pengajuan, cancel, dll.
    // Route::get('/pengajuan-barang/{itemRequest}', [ItemRequestController::class, 'show'])->name('pengajuan.barang.show');
    // Route::put('/pengajuan-barang/{itemRequest}/cancel', [ItemRequestController::class, 'cancel'])->name('pengajuan.barang.cancel');

    // === ROUTE UNTUK LAPORAN ===
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/stok-barang', [LaporanController::class, 'stokBarang'])->name('stok.barang');
        Route::get('/barang-masuk', [LaporanController::class, 'barangMasuk'])->name('barang.masuk');
        Route::get('/barang-keluar', [LaporanController::class, 'barangKeluar'])->name('barang.keluar');
    });

    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/{id}/baca', [NotificationController::class, 'markAsReadAndRedirect'])->name('notifikasi.markAsReadAndRedirect');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'markAllAsRead'])->name('notifikasi.markAllAsRead');

    // Anda bisa menambahkan route lain yang memerlukan login di sini
    // Misalnya, nanti untuk manajemen stok, laporan, dll.
});

// Ini akan memuat route-route otentikasi (login, register, logout, dll.)
require __DIR__.'/auth.php';