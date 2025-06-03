<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori; // Untuk filter
use App\Models\Lokasi;  // Untuk filter
use App\Models\StockMovement; // Import StockMovement
use App\Models\User;        // Import User untuk filter pencatat
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    public function stokBarang(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('view-laporan-stok')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat laporan stok barang.');
        }

        $filterKategoriId = $request->input('kategori_id');
        $filterLokasiId = $request->input('lokasi_id');
        $searchTerm = $request->input('search');

        $query = Barang::with(['kategori', 'unit', 'lokasi'])
                       ->orderBy('nama_barang', 'asc');

        if ($filterKategoriId) {
            $query->where('kategori_id', $filterKategoriId);
        }

        if ($filterLokasiId) {
            $query->where('lokasi_id', $filterLokasiId);
        }

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_barang', 'like', "%{$searchTerm}%")
                  ->orWhere('kode_barang', 'like', "%{$searchTerm}%");
            });
        }

        $barangs = $query->paginate(20)->appends($request->query());

        // Data untuk dropdown filter
        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();
        $lokasis = Lokasi::orderBy('nama_lokasi', 'asc')->get();

        return view('laporan.stok_barang', compact(
            'barangs',
            'kategoris',
            'lokasis',
            'filterKategoriId',
            'filterLokasiId',
            'searchTerm'
        ));
    }

    public function barangMasuk(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('view-laporan-barang-masuk')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat laporan barang masuk.');
        }

        $filterBarangId = $request->input('barang_id');
        $filterUserId = $request->input('user_id'); // Filter berdasarkan pencatat
        $filterTanggalMulai = $request->input('tanggal_mulai');
        $filterTanggalAkhir = $request->input('tanggal_akhir');

        $query = StockMovement::where('tipe_pergerakan', 'masuk')
                              ->with(['barang.unit', 'user']) // Eager load relasi
                              ->orderBy('tanggal_pergerakan', 'desc')
                              ->orderBy('created_at', 'desc');

        if ($filterBarangId) {
            $query->where('barang_id', $filterBarangId);
        }

        if ($filterUserId) {
            $query->where('user_id', $filterUserId);
        }

        if ($filterTanggalMulai) {
            $query->whereDate('tanggal_pergerakan', '>=', $filterTanggalMulai);
        }

        if ($filterTanggalAkhir) {
            $query->whereDate('tanggal_pergerakan', '<=', $filterTanggalAkhir);
        }

        $barangMasuk = $query->paginate(15)->appends($request->query());

        // Data untuk dropdown filter
        $barangs = Barang::orderBy('nama_barang', 'asc')->get();
        $users = User::orderBy('name', 'asc')->get(); // Ambil semua user untuk filter pencatat

        return view('laporan.barang_masuk', compact(
            'barangMasuk',
            'barangs',
            'users',
            'filterBarangId',
            'filterUserId',
            'filterTanggalMulai',
            'filterTanggalAkhir'
        ));
    }

    public function barangKeluar(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('view-laporan-barang-keluar')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat laporan barang keluar.');
        }

        $filterBarangId = $request->input('barang_id');
        $filterUserId = $request->input('user_id'); // Filter berdasarkan pencatat/pemroses
        $filterTanggalMulai = $request->input('tanggal_mulai');
        $filterTanggalAkhir = $request->input('tanggal_akhir');
        // Anda bisa tambahkan filter lain, misalnya berdasarkan keperluan dari ItemRequest jika terhubung

        $query = StockMovement::where('tipe_pergerakan', 'keluar') // Fokus pada barang KELUAR
                              ->with(['barang.unit', 'user']) // Eager load relasi
                              ->orderBy('tanggal_pergerakan', 'desc')
                              ->orderBy('created_at', 'desc');

        if ($filterBarangId) {
            $query->where('barang_id', $filterBarangId);
        }

        if ($filterUserId) {
            $query->where('user_id', $filterUserId); // User yang tercatat di stock_movements (pemroses)
        }

        if ($filterTanggalMulai) {
            $query->whereDate('tanggal_pergerakan', '>=', $filterTanggalMulai);
        }

        if ($filterTanggalAkhir) {
            $query->whereDate('tanggal_pergerakan', '<=', $filterTanggalAkhir);
        }

        $barangKeluar = $query->paginate(15)->appends($request->query());

        // Data untuk dropdown filter
        $barangs = Barang::orderBy('nama_barang', 'asc')->get();
        $users = User::orderBy('name', 'asc')->get(); // Ambil semua user untuk filter pencatat/pemroses

        return view('laporan.barang_keluar', compact(
            'barangKeluar',
            'barangs',
            'users',
            'filterBarangId',
            'filterUserId',
            'filterTanggalMulai',
            'filterTanggalAkhir'
        ));
    }

    // Method untuk laporan lain akan ditambahkan di sini
}