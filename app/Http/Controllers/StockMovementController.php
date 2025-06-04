<?php

namespace App\Http\Controllers;

use App\Models\Barang; // Import model Barang
use App\Models\StockMovement; // Import model StockMovement
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang login
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;

class StockMovementController extends Controller
{
    /**
     * Menampilkan form untuk mencatat barang masuk.
     */
    public function createMasuk()
    {
        if (!Auth::user()->hasPermissionTo('stok-pergerakan-list')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat riwayat pergerakan stok.');
        }
        $barangs = Barang::orderBy('nama_barang', 'asc')->get(); // Ambil semua barang untuk dipilih
        return view('stok.create_masuk', compact('barangs'));
    }

    // ... (method createMasuk sudah ada)
    public function storeMasuk(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('stok-pergerakan-list')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat riwayat pergerakan stok.');
        }
        $validatedData = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'kuantitas' => 'required|integer|min:1',
            'tanggal_pergerakan' => 'required|date',
            'catatan' => 'nullable|string|max:1000',
        ]);

        // Ambil barang untuk mendapatkan stok sebelumnya (opsional jika observer yang handle semua)
        // $barang = Barang::findOrFail($validatedData['barang_id']);
        // $stokSebelumnya = $barang->stok;

        StockMovement::create([
            'barang_id' => $validatedData['barang_id'],
            'user_id' => Auth::id(), // ID pengguna yang sedang login
            'tipe_pergerakan' => 'masuk',
            'kuantitas' => $validatedData['kuantitas'],
            // 'stok_sebelumnya' => $stokSebelumnya, // Observer akan mengisi ini
            // 'stok_setelahnya' => $stokSebelumnya + $validatedData['kuantitas'], // Observer akan mengisi ini
            'tanggal_pergerakan' => $validatedData['tanggal_pergerakan'],
            'catatan' => $validatedData['catatan'],
        ]);

        // Observer StockMovementObserver->created() akan otomatis:
        // 1. Mengupdate $barang->stok
        // 2. Mengisi $stockMovement->stok_sebelumnya
        // 3. Mengisi $stockMovement->stok_setelahnya

        return redirect()->route('stok.pergerakan.index') // Arahkan ke riwayat stok nanti
                        ->with('success', 'Barang masuk berhasil dicatat!');
    }

    // Method index akan kita buat berikutnya
    public function index(Request $request) // Tambahkan Request $request
    {
        if (!Auth::user()->hasPermissionTo('stok-pergerakan-list')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat riwayat pergerakan stok.');
        }
        // Ambil semua input filter dari request
        $filterBarangId = $request->input('barang_id');
        $filterTipePergerakan = $request->input('tipe_pergerakan');
        $filterTanggalMulai = $request->input('tanggal_mulai');
        $filterTanggalAkhir = $request->input('tanggal_akhir');

        // Query dasar untuk StockMovement dengan eager loading
        $query = StockMovement::with(['barang', 'user']);

        // Terapkan filter jika ada
        if ($filterBarangId) {
            $query->where('barang_id', $filterBarangId);
        }

        if ($filterTipePergerakan) {
            $query->where('tipe_pergerakan', $filterTipePergerakan);
        }

        if ($filterTanggalMulai) {
            $query->whereDate('tanggal_pergerakan', '>=', $filterTanggalMulai);
        }

        if ($filterTanggalAkhir) {
            $query->whereDate('tanggal_pergerakan', '<=', $filterTanggalAkhir);
        }

        $stockMovements = $query->orderBy('tanggal_pergerakan', 'desc')
                                ->orderBy('created_at', 'desc')
                                ->paginate(15)
                                ->appends($request->query()); // Penting untuk paginasi dengan filter

        // Ambil data barang untuk dropdown filter
        $barangs = Barang::orderBy('nama_barang', 'asc')->get();

        return view('stok.index_pergerakan', compact(
            'stockMovements',
            'barangs', // Kirim data barang ke view
            'filterBarangId', // Kirim nilai filter kembali ke view
            'filterTipePergerakan',
            'filterTanggalMulai',
            'filterTanggalAkhir'
        ));
    }

    public function createKeluar()
    {
        if (!Auth::user()->hasPermissionTo('stok-pergerakan-list')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat riwayat pergerakan stok.');
        }
        $barangs = Barang::orderBy('nama_barang', 'asc')->get(); // Ambil semua barang untuk dipilih
        return view('stok.create_keluar', compact('barangs'));
    }

    public function storeKeluar(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('stok-pergerakan-list')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat riwayat pergerakan stok.');
        }
        $validatedData = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'kuantitas' => 'required|integer|min:1',
            'tanggal_pergerakan' => 'required|date',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $barang = Barang::findOrFail($validatedData['barang_id']);

        // Validasi tambahan: pastikan kuantitas keluar tidak melebihi stok tersedia
        // Ini adalah aturan bisnis umum, Anda bisa sesuaikan.
        if ($barang->stok < $validatedData['kuantitas']) {
            return back()->withErrors(['kuantitas' => 'Kuantitas keluar melebihi stok yang tersedia (Stok: ' . $barang->stok . ').'])->withInput();
        }

        StockMovement::create([
            'barang_id' => $validatedData['barang_id'],
            'user_id' => Auth::id(),
            'tipe_pergerakan' => 'keluar', // Tipe pergerakan adalah 'keluar'
            'kuantitas' => $validatedData['kuantitas'],
            // 'stok_sebelumnya' akan diisi oleh observer
            // 'stok_setelahnya' akan diisi oleh observer
            'tanggal_pergerakan' => $validatedData['tanggal_pergerakan'],
            'catatan' => $validatedData['catatan'],
        ]);

        return redirect()->route('stok.pergerakan.index')
                        ->with('success', 'Barang keluar berhasil dicatat!');
    }

    public function createAdjustment()
    {
        if (!Auth::user()->hasPermissionTo('stok-koreksi')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melakukan koreksi stok.');
        }

        // Ambil semua barang untuk dipilih, beserta informasi stok saat ini
        $barangs = Barang::orderBy('nama_barang', 'asc')->get()->map(function ($barang) {
            $barang->info_display = $barang->nama_barang . ' (Stok Sistem: ' . $barang->stok . ' ' . ($barang->unit->singkatan_unit ?? $barang->unit->nama_unit ?? '') . ')';
            return $barang;
        });

        return view('stok.create_adjustment', compact('barangs'));
    }

    /**
     * Menyimpan data koreksi stok.
     */
    public function storeAdjustment(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('stok-koreksi')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menyimpan koreksi stok.');
        }

        $validatedData = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'stok_fisik_baru' => 'required|integer|min:0', // Stok fisik tidak boleh negatif
            'alasan_koreksi' => 'required|string|max:1000',
            'tanggal_koreksi' => 'required|date|before_or_equal:today', // Tanggal koreksi, tidak boleh di masa depan
        ]);

        $barang = Barang::findOrFail($validatedData['barang_id']);
        $stokSistemSebelumnya = $barang->stok;
        $stokFisikBaru = (int)$validatedData['stok_fisik_baru'];

        $kuantitasDisesuaikan = $stokFisikBaru - $stokSistemSebelumnya;
        $tipePergerakanKoreksi = '';

        if ($kuantitasDisesuaikan == 0) {
            return redirect()->route('stok.koreksi.create')->with('info', 'Tidak ada perubahan stok. Stok fisik sama dengan stok sistem.');
        } elseif ($kuantitasDisesuaikan > 0) {
            $tipePergerakanKoreksi = 'koreksi-tambah';
        } else { // $kuantitasDisesuaikan < 0
            $tipePergerakanKoreksi = 'koreksi-kurang';
            $kuantitasDisesuaikan = abs($kuantitasDisesuaikan); // Kuantitas di StockMovement selalu positif
        }

        DB::beginTransaction();
        try {
            StockMovement::create([
                'barang_id' => $barang->id,
                'user_id' => Auth::id(),
                'tipe_pergerakan' => $tipePergerakanKoreksi,
                'kuantitas' => $kuantitasDisesuaikan,
                // 'stok_sebelumnya' akan diisi oleh observer dengan $stokSistemSebelumnya
                // 'stok_setelahnya' akan diisi oleh observer dengan $stokFisikBaru
                'tanggal_pergerakan' => $validatedData['tanggal_koreksi'] . ' ' . now()->format('H:i:s'), // Gabungkan tanggal dari input dengan waktu saat ini
                'catatan' => 'Koreksi Stok/Stok Opname. Alasan: ' . $validatedData['alasan_koreksi'],
            ]);
            // StockMovementObserver akan otomatis mengupdate $barang->stok menjadi $stokFisikBaru

            DB::commit();

            return redirect()->route('stok.pergerakan.index')
                             ->with('success', 'Koreksi stok untuk barang "'.$barang->nama_barang.'" berhasil disimpan. Stok baru: ' . $stokFisikBaru);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->with('error', 'Gagal menyimpan koreksi stok: ' . $e->getMessage())
                             ->withInput();
        }
    }

    // Method lain akan ditambahkan nanti
}