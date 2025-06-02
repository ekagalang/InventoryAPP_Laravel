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

        // === ROUTE UNTUK MANAJEMEN PENGGUNA OLEH ADMIN ===
    // Kita bisa grouping dengan prefix 'admin' agar lebih rapi URL-nya
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        // Anda bisa tambahkan route lain khusus admin di sini
    });

    // Anda bisa menambahkan route lain yang memerlukan login di sini
    // Misalnya, nanti untuk manajemen stok, laporan, dll.
});

// Ini akan memuat route-route otentikasi (login, register, logout, dll.)
require __DIR__.'/auth.php';