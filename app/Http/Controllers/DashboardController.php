<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;
use App\Models\ItemRequest;
use App\Models\Unit;
use App\Models\Lokasi;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('view-dashboard')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat dashboard.');
        }

        // --- Data Statistik untuk Kartu ---
        $totalBarang = Barang::count();
        $totalKategori = Kategori::count();
        $totalUnit = Unit::count();
        $totalLokasi = Lokasi::count();
        $totalPengguna = User::count();
        $pengajuanDiajukan = ItemRequest::where('status', 'Diajukan')->count();
        $pengajuanDisetujui = ItemRequest::where('status', 'Disetujui')->count();
        $barangAktif = Barang::where('status', 'aktif')->count();

        // === DATA BARU UNTUK PANEL AKSI CEPAT & GRAFIK ===

        // 1. Ambil 5 pengajuan terbaru yang menunggu persetujuan (untuk Admin/Staf)
        $pengajuanMenunggu = null;
        if (Auth::user()->hasPermissionTo('pengajuan-barang-approve')) {
            $pengajuanMenunggu = ItemRequest::where('status', 'Diajukan')
                                            ->with('pemohon', 'barang')
                                            ->latest()
                                            ->take(5)
                                            ->get();
        }

        // 2. Ambil 5 barang yang stoknya kritis
        $barangStokKritis = Barang::where('status', 'aktif')
                                  ->where('stok_minimum', '>', 0)
                                  ->whereColumn('stok', '<=', 'stok_minimum')
                                  ->orderBy('stok', 'asc')
                                  ->take(5)
                                  ->get();

        // 3. Data untuk Grafik Status Barang (Donut Chart)
        $barangByStatus = Barang::query()
                                ->select('status', DB::raw('count(*) as total'))
                                ->groupBy('status')
                                ->pluck('total', 'status')
                                ->all();

        // 4. Data untuk Grafik Pergerakan Stok 6 Bulan Terakhir (Bar Chart)
        $pergerakanStok = StockMovement::query()
                                ->select(
                                    DB::raw('DATE_FORMAT(tanggal_pergerakan, "%Y-%m") as bulan'),
                                    DB::raw('SUM(CASE WHEN tipe_pergerakan IN ("masuk", "koreksi-tambah", "pengembalian") THEN kuantitas ELSE 0 END) as total_masuk'),
                                    DB::raw('SUM(CASE WHEN tipe_pergerakan IN ("keluar", "koreksi-kurang") THEN kuantitas ELSE 0 END) as total_keluar')
                                )
                                ->where('tanggal_pergerakan', '>=', now()->subMonths(6))
                                ->groupBy('bulan')
                                ->orderBy('bulan', 'asc')
                                ->get();

        // Kirim semua variabel ke view, termasuk data baru
        return view('dashboard', compact(
            'totalBarang', 'totalPengguna', 'pengajuanDiajukan', 'pengajuanDisetujui',
            'barangByStatus', 
            'pergerakanStok',
            'pengajuanMenunggu',
            'barangStokKritis'
        ));
    }
}