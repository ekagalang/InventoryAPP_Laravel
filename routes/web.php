<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\MaintenanceScheduleController;
use App\Http\Controllers\Admin\RecurringPaymentController;
use App\Http\Controllers\Admin\PaymentScheduleController;


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
    Route::patch('/profile', [ProfileController::class, 'update'])->middleware('throttle:submit')->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->middleware('throttle:submit')->name('profile.destroy');

    // Resource routes untuk modul CRUD Anda
    Route::resource('barang', BarangController::class);
    
    // Bulk operations routes
    Route::post('/barang/bulk-delete', [BarangController::class, 'bulkDelete'])->middleware('throttle:submit')->name('barang.bulk-delete');
    Route::post('/barang/bulk-update', [BarangController::class, 'bulkUpdate'])->middleware('throttle:submit')->name('barang.bulk-update');
    Route::post('/barang/bulk-export', [BarangController::class, 'bulkExport'])->name('barang.bulk-export');
    Route::resource('kategori', KategoriController::class);
    Route::resource('unit', UnitController::class);
    Route::resource('lokasi', LokasiController::class);

    // === ROUTE UNTUK MANAJEMEN STOK (dengan rate limiting) ===
    Route::get('/stok/pergerakan', [StockMovementController::class, 'index'])->name('stok.pergerakan.index'); // Daftar Riwayat Pergerakan Stok
    Route::get('/stok/masuk/create', [StockMovementController::class, 'createMasuk'])->name('stok.masuk.create'); // Form Barang Masuk
    Route::post('/stok/masuk', [StockMovementController::class, 'storeMasuk'])->middleware('throttle:submit')->name('stok.masuk.store'); // Simpan Barang Masuk

    Route::get('/stok/keluar/create', [StockMovementController::class, 'createKeluar'])->name('stok.keluar.create');
    Route::post('/stok/keluar', [StockMovementController::class, 'storeKeluar'])->middleware('throttle:submit')->name('stok.keluar.store');

    // TAMBAHKAN ROUTE INI UNTUK KOREKSI STOK:
    Route::get('/stok/koreksi/create', [StockMovementController::class, 'createAdjustment'])->name('stok.koreksi.create');
    Route::post('/stok/koreksi', [StockMovementController::class, 'storeAdjustment'])->middleware('throttle:submit')->name('stok.koreksi.store');

        // === ROUTE UNTUK MANAJEMEN PENGGUNA OLEH ADMIN ===
    // Kita bisa grouping dengan prefix 'admin' agar lebih rapi URL-nya
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        // Permission CRUD routes juga akan ada di sini nanti
        Route::resource('permissions', PermissionController::class);
        Route::resource('maintenances', MaintenanceController::class);
        
        // Route untuk MaintenanceSchedule
        Route::get('/maintenances/{maintenance}/schedules', [MaintenanceScheduleController::class, 'show'])->name('maintenances.schedules');
        Route::patch('/maintenance-schedules/{schedule}/complete', [MaintenanceScheduleController::class, 'markCompleted'])->middleware('throttle:submit')->name('maintenance-schedules.complete');
        Route::patch('/maintenance-schedules/{schedule}/cancel', [MaintenanceScheduleController::class, 'markCancelled'])->middleware('throttle:submit')->name('maintenance-schedules.cancel');
        Route::patch('/maintenance-schedules/{schedule}', [MaintenanceScheduleController::class, 'update'])->middleware('throttle:submit')->name('maintenance-schedules.update');
        
        // Route untuk RecurringPayment
        Route::resource('payments', RecurringPaymentController::class);
        
        // Route untuk PaymentSchedule
        Route::get('/payments/{payment}/schedules', [PaymentScheduleController::class, 'show'])->name('payments.schedules');
        Route::patch('/payment-schedules/{schedule}/paid', [PaymentScheduleController::class, 'markPaid'])->middleware('throttle:submit')->name('payment-schedules.paid');
        Route::patch('/payment-schedules/{schedule}/overdue', [PaymentScheduleController::class, 'markOverdue'])->middleware('throttle:submit')->name('payment-schedules.overdue');
        Route::patch('/payment-schedules/{schedule}/cancel', [PaymentScheduleController::class, 'markCancelled'])->middleware('throttle:submit')->name('payment-schedules.cancel');
        Route::patch('/payment-schedules/{schedule}', [PaymentScheduleController::class, 'update'])->middleware('throttle:submit')->name('payment-schedules.update');

        // === PINDAHKAN SEMUA ROUTE MANAJEMEN PENGAJUAN KE DALAM GRUP INI ===
        Route::get('/pengajuan-barang', [ItemRequestController::class, 'adminIndex'])->name('pengajuan.barang.index');
        Route::get('/pengajuan-barang/{itemRequest}', [ItemRequestController::class, 'adminShow'])->name('pengajuan.barang.show');
        Route::post('/pengajuan-barang/{itemRequest}/approve', [ItemRequestController::class, 'approve'])->middleware('throttle:submit')->name('pengajuan.barang.approve');
        Route::post('/pengajuan-barang/{itemRequest}/reject', [ItemRequestController::class, 'reject'])->middleware('throttle:submit')->name('pengajuan.barang.reject');
        Route::post('/pengajuan-barang/{itemRequest}/process', [ItemRequestController::class, 'process'])->middleware('throttle:submit')->name('pengajuan.barang.process');
        Route::put('/pengajuan-barang/{itemRequest}/return', [ItemRequestController::class, 'storeReturn'])->middleware('throttle:submit')->name('pengajuan.barang.return');
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    // === ROUTE UNTUK PENGAJUAN BARANG OLEH PENGGUNA (dengan rate limiting) ===
    Route::get('/pengajuan-barang/pilih-tipe', [ItemRequestController::class, 'pilihTipe'])->name('pengajuan.barang.pilihTipe');
    Route::get('/pengajuan-barang/create/{tipe}', [ItemRequestController::class, 'create'])->name('pengajuan.barang.create');
    Route::get('/pengajuan-barang', [ItemRequestController::class, 'myRequests'])->name('pengajuan.barang.index');
    Route::post('/pengajuan-barang', [ItemRequestController::class, 'store'])->middleware('throttle:submit')->name('pengajuan.barang.store');
    Route::put('/pengajuan-barang/{itemRequest}/cancel', [ItemRequestController::class, 'cancelOwnRequest'])->middleware('throttle:submit')->name('pengajuan.barang.cancel');
    // Jika Anda tidak menggunakan halaman detail untuk user biasa, sebaiknya hapus route 'show' ini untuk menghindari kebingungan
    // Route::get('/pengajuan-barang/{itemRequest}', [ItemRequestController::class, 'show'])->name('pengajuan.barang.show'); 

    // === ROUTE UNTUK LAPORAN ===
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/stok-barang', [LaporanController::class, 'stokBarang'])->name('stok.barang');
        Route::get('/barang-masuk', [LaporanController::class, 'barangMasuk'])->name('barang.masuk');
        Route::get('/barang-keluar', [LaporanController::class, 'barangKeluar'])->name('barang.keluar');
        Route::get('/maintenance', [LaporanController::class, 'maintenance'])->name('maintenance');
    });

    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/{id}/baca', [NotificationController::class, 'markAsReadAndRedirect'])->name('notifikasi.markAsReadAndRedirect');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'markAllAsRead'])->name('notifikasi.markAllAsRead');
    
    // Route::prefix('laporan')->name('laporan.')->group(function () {
    //     Route::get('/stok-barang', [LaporanController::class, 'stokBarang'])->name('stok.barang');
    //     Route::get('/stok-barang/export', [LaporanController::class, 'exportStokBarang'])->name('stok.barang.export'); // <-- TAMBAHKAN INI
        // ... route laporan lainnya ...
    // });

    // Anda bisa menambahkan route lain yang memerlukan login di sini
    // Misalnya, nanti untuk manajemen stok, laporan, dll.
});

// Ini akan memuat route-route otentikasi (login, register, logout, dll.)
require __DIR__.'/auth.php';