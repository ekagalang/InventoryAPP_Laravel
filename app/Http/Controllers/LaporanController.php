<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori; // Untuk filter
use App\Models\Lokasi;  // Untuk filter
use App\Models\StockMovement; // Import StockMovement
use App\Models\User;        // Import User untuk filter pencatat
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel; // <-- IMPORT FACADE EXCEL
use App\Models\Maintenance;
// use App\Exports\BarangStokExport;

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

        // Data untuk grafik stok berdasarkan kategori
        $stokPerKategori = Barang::join('kategoris', 'barangs.kategori_id', '=', 'kategoris.id')
            ->selectRaw('kategoris.nama_kategori, SUM(barangs.stok) as total_stok')
            ->groupBy('kategoris.id', 'kategoris.nama_kategori')
            ->get();

        // Data untuk grafik stok berdasarkan lokasi
        $stokPerLokasi = Barang::join('lokasis', 'barangs.lokasi_id', '=', 'lokasis.id')
            ->selectRaw('lokasis.nama_lokasi, SUM(barangs.stok) as total_stok')
            ->groupBy('lokasis.id', 'lokasis.nama_lokasi')
            ->get();

        return view('laporan.stok_barang', compact(
            'barangs',
            'kategoris',
            'lokasis',
            'filterKategoriId',
            'filterLokasiId',
            'searchTerm',
            'stokPerKategori',
            'stokPerLokasi'
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

        // Data untuk grafik barang masuk per bulan (6 bulan terakhir)
        $barangMasukPerBulan = StockMovement::where('tipe_pergerakan', 'masuk')
            ->whereDate('tanggal_pergerakan', '>=', now()->subMonths(6))
            ->selectRaw('YEAR(tanggal_pergerakan) as tahun, MONTH(tanggal_pergerakan) as bulan, SUM(jumlah) as total_masuk')
            ->groupByRaw('YEAR(tanggal_pergerakan), MONTH(tanggal_pergerakan)')
            ->orderByRaw('YEAR(tanggal_pergerakan), MONTH(tanggal_pergerakan)')
            ->get();

        return view('laporan.barang_masuk', compact(
            'barangMasuk',
            'barangs',
            'users',
            'filterBarangId',
            'filterUserId',
            'filterTanggalMulai',
            'filterTanggalAkhir',
            'barangMasukPerBulan'
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

        // Data untuk grafik barang keluar per bulan (6 bulan terakhir)
        $barangKeluarPerBulan = StockMovement::where('tipe_pergerakan', 'keluar')
            ->whereDate('tanggal_pergerakan', '>=', now()->subMonths(6))
            ->selectRaw('YEAR(tanggal_pergerakan) as tahun, MONTH(tanggal_pergerakan) as bulan, SUM(jumlah) as total_keluar')
            ->groupByRaw('YEAR(tanggal_pergerakan), MONTH(tanggal_pergerakan)')
            ->orderByRaw('YEAR(tanggal_pergerakan), MONTH(tanggal_pergerakan)')
            ->get();

        return view('laporan.barang_keluar', compact(
            'barangKeluar',
            'barangs',
            'users',
            'filterBarangId',
            'filterUserId',
            'filterTanggalMulai',
            'filterTanggalAkhir',
            'barangKeluarPerBulan'
        ));
    }

    public function exportStokBarang(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('view-laporan-stok')) { // Gunakan permission yang sama
            abort(403, 'AKSES DITOLAK.');
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = 'laporan-stok-barang-' . $timestamp . '.xlsx';

        return Excel::download(new BarangStokExport($request), $fileName);
    }

    public function maintenance(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('view-laporan-maintenance')) {
            abort(403, 'AKSES DITOLAK.');
        }

        $filterBarangId = $request->input('barang_id');
        $filterStatus = $request->input('status');
        $filterTanggalMulai = $request->input('tanggal_mulai');
        $filterTanggalAkhir = $request->input('tanggal_akhir');

        $query = Maintenance::with(['barang', 'pencatat'])->latest();

        // Terapkan filter
        if ($filterBarangId) {
            $query->where('barang_id', $filterBarangId);
        }
        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }
        if ($filterTanggalMulai) {
            $query->whereDate('tanggal_maintenance', '>=', $filterTanggalMulai);
        }
        if ($filterTanggalAkhir) {
            $query->whereDate('tanggal_maintenance', '<=', $filterTanggalAkhir);
        }

        // Hitung total biaya HANYA dari hasil yang terfilter
        $totalBiaya = $query->sum('biaya');

        $maintenances = $query->paginate(15)->appends($request->query());

        // Data untuk dropdown filter
        $barangs = Barang::orderBy('nama_barang', 'asc')->get();

        // Data untuk grafik maintenance berdasarkan status
        $maintenancePerStatus = Maintenance::selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->get();

        // Data untuk grafik biaya maintenance per bulan (6 bulan terakhir)
        $biayaMaintenancePerBulan = Maintenance::whereDate('tanggal_maintenance', '>=', now()->subMonths(6))
            ->selectRaw('YEAR(tanggal_maintenance) as tahun, MONTH(tanggal_maintenance) as bulan, SUM(biaya) as total_biaya')
            ->groupByRaw('YEAR(tanggal_maintenance), MONTH(tanggal_maintenance)')
            ->orderByRaw('YEAR(tanggal_maintenance), MONTH(tanggal_maintenance)')
            ->get();

        return view('laporan.maintenance', compact(
            'maintenances',
            'barangs',
            'totalBiaya', // Kirim total biaya ke view
            'filterBarangId',
            'filterStatus',
            'filterTanggalMulai',
            'filterTanggalAkhir',
            'maintenancePerStatus',
            'biayaMaintenancePerBulan'
        ));
    }

    // Method untuk laporan lain akan ditambahkan di sini
}