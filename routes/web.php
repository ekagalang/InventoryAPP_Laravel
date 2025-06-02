<?php

// app()->booted(function () {
//     if (request()->is('barang')) { // Hanya dd jika mengakses route /barang untuk membatasi efeknya
//         dd(config('permission.models.permission'));
//     }
// });

// app()->booted(function () {
//     if (request()->is('barang')) { // Hanya dd jika mengakses route /barang
//         try {
//             $permissionInstance = resolve(Spatie\Permission\Contracts\Permission::class);
//             dd("Berhasil me-resolve PermissionContract. Instance:", $permissionInstance, "Config value:", config('permission.models.permission'));
//         } catch (\Exception $e) {
//             dd("Gagal me-resolve PermissionContract. Error:", $e->getMessage(), "Config value:", config('permission.models.permission'));
//         }
//     }
// });

// app()->booted(function () {
//     if (request()->is('barang')) { // Hanya dd jika mengakses route /barang
//         try {
//             $permissionModel = new \Spatie\Permission\Models\Permission(); // Coba buat instance langsung
//             dd("Berhasil membuat instance Spatie\Permission\Models\Permission secara langsung.", $permissionModel);
//         } catch (\Throwable $e) { // Menangkap semua jenis error/exception
//             dd("GAGAL membuat instance Spatie\Permission\Models\Permission secara langsung. Error:", $e->getMessage(), $e);
//         }
//     }
// });

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\StockMovementController;

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
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


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

    // Anda bisa menambahkan route lain yang memerlukan login di sini
    // Misalnya, nanti untuk manajemen stok, laporan, dll.
});

// Ini akan memuat route-route otentikasi (login, register, logout, dll.)
require __DIR__.'/auth.php';