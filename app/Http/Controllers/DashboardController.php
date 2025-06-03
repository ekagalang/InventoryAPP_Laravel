<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;
use App\Models\ItemRequest;
use App\Models\Unit; // Kita tambahkan hitungan Unit & Lokasi juga
use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('view-dashboard')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat dashboard.');
        }

        $totalBarang = Barang::count();
        $totalKategori = Kategori::count();
        $totalUnit = Unit::count(); // Tambahan
        $totalLokasi = Lokasi::count(); // Tambahan
        $totalPengguna = User::count();

        $pengajuanDiajukan = ItemRequest::where('status', 'Diajukan')->count();
        $pengajuanDisetujui = ItemRequest::where('status', 'Disetujui')->count(); // Menunggu diproses

        // Data lain yang mungkin menarik (contoh)
        $barangAktif = Barang::where('status', 'aktif')->count();
        // $barangStokMinimum = Barang::where('stok', '<=', DB::raw('stok_minimum'))->count(); // Jika ada kolom stok_minimum

        return view('dashboard', compact(
            'totalBarang',
            'totalKategori',
            'totalUnit',
            'totalLokasi',
            'totalPengguna',
            'pengajuanDiajukan',
            'pengajuanDisetujui',
            'barangAktif'
            // 'barangStokMinimum'
        ));
    }
}