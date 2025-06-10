<?php

namespace App\Http\Controllers;

use App\Models\Barang; // Import model Barang
use App\Models\ItemRequest; // Import model ItemRequest
use App\Models\StockMovement; // Jika belum ada
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang login
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PengajuanBaruNotification;
use App\Notifications\PengajuanStatusUpdateNotification;


class ItemRequestController extends Controller
{
    public function __construct()
    {
        // Kita akan terapkan permission check di setiap method
    }

    /**
     * Menampilkan form untuk membuat pengajuan barang baru.
     */
    public function create(string $tipe) // Terima parameter $tipe
    {
        if (!Auth::user()->hasPermissionTo('pengajuan-barang-create')) {
            abort(403, 'AKSES DITOLAK.');
        }

        // Validasi tipe
        if (!in_array($tipe, ['permintaan', 'peminjaman'])) {
            abort(404, 'Tipe pengajuan tidak valid.');
        }

        // Filter barang berdasarkan tipe
        $tipeItem = ($tipe == 'permintaan') ? 'habis_pakai' : 'aset';

        $barangs = Barang::where('tipe_item', $tipeItem)
                        ->where('status', 'aktif') 
                        ->orderBy('nama_barang', 'asc')
                        ->get();

        return view('pengajuan_barang.create', compact('barangs', 'tipe')); // Kirim $tipe ke view
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('pengajuan-barang-create')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk membuat pengajuan barang.');
        }

        $validatedData = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'tipe_pengajuan' => 'required|in:permintaan,peminjaman',
            'kuantitas_diminta' => 'required|integer|min:1',
            'tanggal_dibutuhkan' => 'nullable|date|after_or_equal:today',
            'keperluan' => 'required|string|max:1000',
        ]);

        $barang = Barang::findOrFail($validatedData['barang_id']);

        // Opsional: Validasi tambahan jika kuantitas diminta melebihi stok (tergantung aturan bisnis)
        // Jika pengajuan bisa melebihi stok dan menunggu restock, maka validasi ini tidak perlu.
        // Jika harus dari stok tersedia, maka validasi ini penting.
        // if ($barang->stok < $validatedData['kuantitas_diminta']) {
        //     return back()->withErrors(['kuantitas_diminta' => 'Kuantitas yang diminta melebihi stok tersedia (Stok: ' . $barang->stok . ').'])->withInput();
        // }

        $itemRequest = ItemRequest::create([
            'user_id' => Auth::id(), // ID pengguna yang mengajukan
            'barang_id' => $validatedData['barang_id'],
            'tipe_pengajuan' => $validatedData['tipe_pengajuan'],
            'kuantitas_diminta' => $validatedData['kuantitas_diminta'],
            'keperluan' => $validatedData['keperluan'],
            'tanggal_dibutuhkan' => $validatedData['tanggal_dibutuhkan'],
            'status' => 'Diajukan', // Status awal
        ]);

        // Kirim notifikasi ke Admin & StafGudang
        $usersToNotify = User::role(['Admin', 'StafGudang'])->get();
        if ($usersToNotify->isNotEmpty()) {
            // Eager load relasi untuk mencegah N+1 problem jika notifikasi di-queue
            $itemRequest->load('pemohon', 'barang'); 
            Notification::send($usersToNotify, new PengajuanBaruNotification($itemRequest));
        }

        // Arahkan ke halaman daftar pengajuan milik pengguna (akan kita buat nanti)
        return redirect()->route('pengajuan.barang.index') 
                        ->with('success', 'Pengajuan barang Anda berhasil dikirim dan sedang menunggu persetujuan.');
    }

    public function myRequests()
    {
        if (!Auth::user()->hasPermissionTo('pengajuan-barang-list-own')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat daftar pengajuan Anda.');
        }

        $itemRequests = ItemRequest::where('user_id', Auth::id())
                                  ->with(['barang.unit', 'pemohon']) // Eager load barang dengan unitnya, dan pemohon (meskipun sudah Auth::id())
                                  ->orderBy('created_at', 'desc') // Urutkan berdasarkan terbaru
                                  ->paginate(10); // Paginasi

        return view('pengajuan_barang.index', compact('itemRequests'));
    }

    public function adminIndex(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('pengajuan-barang-list-all')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat semua pengajuan barang.');
        }

        $query = ItemRequest::with(['barang.unit', 'pemohon', 'approver'])
                            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status (opsional, bisa ditambahkan nanti)
        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }
        // Filter berdasarkan pemohon (opsional)
        if ($request->filled('pemohon_filter')) {
            $query->where('user_id', $request->pemohon_filter);
        }
        // Filter berdasarkan barang (opsional)
        if ($request->filled('barang_filter')) {
            $query->where('barang_id', $request->barang_filter);
        }


        $allItemRequests = $query->paginate(15)->appends($request->query());

        // Data untuk filter (opsional)
        // $users = User::orderBy('name')->get();
        // $barangs = Barang::orderBy('nama_barang')->get();

        return view('admin.pengajuan_barang.index', compact(
            'allItemRequests'
            // 'users', 
            // 'barangs' 
        ));
    }

    /**
     * Menampilkan detail satu pengajuan barang untuk Admin/Staf.
     */
    public function adminShow(ItemRequest $itemRequest)
    {
        // Bisa gunakan permission 'pengajuan-barang-list-all' atau buat yg lebih spesifik 'pengajuan-barang-view-detail'
        if (!Auth::user()->hasAnyPermission(['pengajuan-barang-list-all', 'pengajuan-barang-approve', 'pengajuan-barang-process'])) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat detail pengajuan ini.');
        }

        // Eager load semua relasi yang mungkin ditampilkan atau dibutuhkan
        $itemRequest->load(['barang.unit', 'pemohon', 'approver', 'pemroses']);
        
        // Ambil data barang terbaru untuk info stok, karena stok bisa berubah
        $barangTerkait = Barang::find($itemRequest->barang_id);

        return view('admin.pengajuan_barang.show', compact('itemRequest', 'barangTerkait'));
    }

    /**
     * Menyetujui pengajuan barang.
     */
    public function approve(Request $request, ItemRequest $itemRequest)
    {
        if (!Auth::user()->hasPermissionTo('pengajuan-barang-approve')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menyetujui pengajuan.');
        }

        // Hanya pengajuan dengan status 'Diajukan' yang bisa disetujui
        if ($itemRequest->status !== 'Diajukan') {
            return redirect()->route('admin.pengajuan.barang.show', $itemRequest->id)
                             ->with('error', 'Pengajuan ini tidak bisa disetujui (status saat ini: '.$itemRequest->status.').');
        }

        $request->validate([
            'kuantitas_disetujui' => 'required|integer|min:1|max:' . $itemRequest->kuantitas_diminta, // Tidak boleh lebih dari yang diminta
            'catatan_approval' => 'nullable|string|max:1000',
        ]);

        // Validasi tambahan: kuantitas disetujui tidak boleh melebihi stok barang saat ini
        $barang = Barang::findOrFail($itemRequest->barang_id);
        if ($barang->stok < $request->kuantitas_disetujui) {
             return redirect()->route('admin.pengajuan.barang.show', $itemRequest->id)
                             ->withErrors(['kuantitas_disetujui' => 'Kuantitas disetujui (' . $request->kuantitas_disetujui . ') melebihi stok tersedia saat ini (Stok: ' . $barang->stok . '). Harap update stok barang atau sesuaikan kuantitas.'])
                             ->withInput();
        }


        $itemRequest->status = 'Disetujui';
        $itemRequest->kuantitas_disetujui = $request->kuantitas_disetujui;
        $itemRequest->approved_by = Auth::id();
        $itemRequest->approved_at = now();
        $itemRequest->catatan_approval = $request->catatan_approval;
        $itemRequest->save();

        // Kirim notifikasi ke pemohon
        if ($itemRequest->pemohon) { // Pastikan relasi pemohon ada
        // Eager load relasi barang untuk data di notifikasi
            $itemRequest->load('barang');
            $itemRequest->pemohon->notify(new PengajuanStatusUpdateNotification($itemRequest));
        }

        return redirect()->route('admin.pengajuan.barang.show', $itemRequest->id)
                         ->with('success', 'Pengajuan barang telah ditolak.');
    }

    /**
     * Menolak pengajuan barang.
     */
    public function reject(Request $request, ItemRequest $itemRequest)
    {
        if (!Auth::user()->hasPermissionTo('pengajuan-barang-approve')) { // Permission yang sama dengan approve
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menolak pengajuan.');
        }

        // Hanya pengajuan dengan status 'Diajukan' yang bisa ditolak
        if ($itemRequest->status !== 'Diajukan') {
            return redirect()->route('admin.pengajuan.barang.show', $itemRequest->id)
                             ->with('error', 'Pengajuan ini tidak bisa ditolak (status saat ini: '.$itemRequest->status.').');
        }

        $request->validate([
            'catatan_approval' => 'required|string|max:1000', // Catatan wajib diisi saat menolak
        ]);

        $itemRequest->status = 'Ditolak';
        $itemRequest->approved_by = Auth::id(); // Tetap catat siapa yang menolak
        $itemRequest->approved_at = now();    // Dan kapan
        $itemRequest->catatan_approval = $request->catatan_approval;
        $itemRequest->kuantitas_disetujui = 0; // Kuantitas disetujui jadi 0
        $itemRequest->save();

        if ($itemRequest->pemohon) {
            $itemRequest->load('barang');
            $itemRequest->pemohon->notify(new PengajuanStatusUpdateNotification($itemRequest));
        }

        return redirect()->route('admin.pengajuan.barang.show', $itemRequest->id)
                         ->with('success', 'Pengajuan barang telah ditolak.');
    }

    public function process(Request $requestInput, ItemRequest $itemRequest) // Ganti nama $request menjadi $requestInput agar tidak konflik
    {
        if (!Auth::user()->hasPermissionTo('pengajuan-barang-process')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk memproses pengajuan barang.');
        }

        // Hanya pengajuan dengan status 'Disetujui' yang bisa diproses
        if ($itemRequest->status !== 'Disetujui') {
            return redirect()->route('admin.pengajuan.barang.show', $itemRequest->id)
                             ->with('error', 'Pengajuan ini tidak bisa diproses (status saat ini: '.$itemRequest->status.'). Hanya pengajuan yang "Disetujui" yang bisa diproses.');
        }

        // Pastikan kuantitas disetujui ada dan lebih dari 0
        if (empty($itemRequest->kuantitas_disetujui) || $itemRequest->kuantitas_disetujui <= 0) {
            return redirect()->route('admin.pengajuan.barang.show', $itemRequest->id)
                             ->with('error', 'Kuantitas yang disetujui belum ditentukan atau nol. Tidak dapat memproses.');
        }

        // Validasi input jika ada form untuk catatan pemroses
        $validatedData = $requestInput->validate([
            'catatan_pemroses' => 'nullable|string|max:1000',
        ]);

        // Cek stok terakhir SEBELUM membuat StockMovement.
        // Ini penting karena StockMovementObserver akan langsung mengubah stok barang.
        $barang = Barang::findOrFail($itemRequest->barang_id);
        if ($barang->stok < $itemRequest->kuantitas_disetujui) {
            return redirect()->route('admin.pengajuan.barang.show', $itemRequest->id)
                             ->with('error', 'Gagal memproses: Stok barang saat ini ('.$barang->stok.') tidak mencukupi untuk kuantitas yang disetujui ('.$itemRequest->kuantitas_disetujui.'). Harap perbarui stok atau batalkan persetujuan.');
        }

        // Gunakan database transaction untuk memastikan semua operasi berhasil atau semua dibatalkan
        DB::beginTransaction();
        try {
            // 1. Buat record Stock Movement (barang keluar)
            // Observer akan otomatis mengupdate stok barang dan mengisi stok_sebelumnya/stok_setelahnya di StockMovement
            StockMovement::create([
                'barang_id' => $itemRequest->barang_id,
                'user_id' => Auth::id(), // User yang memproses
                'tipe_pergerakan' => 'keluar',
                'kuantitas' => $itemRequest->kuantitas_disetujui,
                'tanggal_pergerakan' => now(), // Atau bisa dari input form jika tanggal proses bisa diatur
                'catatan' => 'Pengeluaran barang berdasarkan Pengajuan ID: ' . $itemRequest->id . ($validatedData['catatan_pemroses'] ? ' - Catatan Pemroses: ' . $validatedData['catatan_pemroses'] : ''),
                // stok_sebelumnya dan stok_setelahnya akan diisi oleh StockMovementObserver
            ]);

            // 2. Update status ItemRequest menjadi 'Diproses'
            $itemRequest->status = 'Diproses';
            $itemRequest->processed_by = Auth::id();
            $itemRequest->processed_at = now();
            $itemRequest->catatan_pemroses = $validatedData['catatan_pemroses']; // Simpan catatan dari form
            $itemRequest->save();

            DB::commit(); // Semua operasi berhasil

            if ($itemRequest->pemohon) {
                $itemRequest->load('barang');
                $itemRequest->pemohon->notify(new PengajuanStatusUpdateNotification($itemRequest));
            }

            return redirect()->route('admin.pengajuan.barang.show', $itemRequest->id)
                             ->with('success', 'Pengajuan barang berhasil diproses dan stok telah diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack(); // Ada kesalahan, batalkan semua operasi
            return redirect()->route('admin.pengajuan.barang.show', $itemRequest->id)
                             ->with('error', 'Terjadi kesalahan saat memproses pengajuan: ' . $e->getMessage());
        }
    }

    public function cancelOwnRequest(ItemRequest $itemRequest) // Menggunakan Route Model Binding
    {
        // Pengecekan 1: Pastikan user yang login adalah pemilik pengajuan
        if ($itemRequest->user_id !== Auth::id()) {
            abort(403, 'AKSES DITOLAK: Anda bukan pemilik pengajuan ini.');
        }

        // Pengecekan 2: Pastikan user memiliki permission untuk membatalkan
        if (!Auth::user()->hasPermissionTo('pengajuan-barang-cancel-own')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk membatalkan pengajuan.');
        }

        // Pengecekan 3: Hanya pengajuan dengan status 'Diajukan' yang bisa dibatalkan
        if ($itemRequest->status !== 'Diajukan') {
            return redirect()->route('pengajuan.barang.index')
                            ->with('error', 'Pengajuan ini tidak dapat dibatalkan (Status saat ini: '.$itemRequest->status.').');
        }

        // Jika semua pengecekan lolos, update status
        $itemRequest->status = 'Dibatalkan';
        $itemRequest->save();

        // Kirim notifikasi ke pemohon untuk konfirmasi pembatalan
        if ($itemRequest->pemohon) {
            $itemRequest->load('barang');
            $itemRequest->pemohon->notify(new PengajuanStatusUpdateNotification($itemRequest));
        }

        return redirect()->route('pengajuan.barang.index')
                        ->with('success', 'Pengajuan barang (ID: '.$itemRequest->id.') berhasil dibatalkan.');
    }

    public function storeReturn(Request $request, ItemRequest $itemRequest)
    {
        if (!Auth::user()->hasPermissionTo('pengajuan-barang-return')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk mencatat pengembalian barang.');
        }

        // Hanya pengajuan dengan status 'Diproses' yang bisa dikembalikan
        if ($itemRequest->status !== 'Diproses') {
            return redirect()->route('admin.pengajuan.barang.show', $itemRequest->id)
                            ->with('error', 'Pengajuan ini tidak dalam status "Diproses" dan tidak dapat dikembalikan.');
        }

        $validatedData = $request->validate([
            'catatan_pengembalian' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // 1. Buat record Stock Movement (barang masuk tipe 'pengembalian')
            StockMovement::create([
                'barang_id' => $itemRequest->barang_id,
                'user_id' => Auth::id(), // User yang menerima pengembalian
                'tipe_pergerakan' => 'pengembalian',
                'kuantitas' => $itemRequest->kuantitas_disetujui, // Kuantitas sesuai yang disetujui/dikeluarkan
                'tanggal_pergerakan' => now(),
                'catatan' => 'Pengembalian barang dari Pengajuan ID: ' . $itemRequest->id . ($validatedData['catatan_pengembalian'] ? ' - Catatan Pengembalian: ' . $validatedData['catatan_pengembalian'] : ''),
            ]);

            // 2. Update status ItemRequest menjadi 'Dikembalikan'
            $itemRequest->status = 'Dikembalikan';
            $itemRequest->returned_by_staff_id = Auth::id();
            $itemRequest->returned_at = now();
            $itemRequest->catatan_pengembalian = $validatedData['catatan_pengembalian'];
            $itemRequest->save();

            DB::commit();

            return redirect()->route('admin.pengajuan.barang.show', $itemRequest->id)
                            ->with('success', 'Pengembalian barang berhasil dicatat dan stok telah dikembalikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.pengajuan.barang.show', $itemRequest->id)
                            ->with('error', 'Terjadi kesalahan saat mencatat pengembalian: ' . $e->getMessage());
        }
    }

    public function pilihTipe()
    {
        // Permission check, pastikan user bisa membuat salah satu tipe pengajuan
        if (!Auth::user()->hasAnyPermission(['pengajuan-barang-create'])) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk membuat pengajuan.');
        }
        return view('pengajuan_barang.pilih_tipe');
    }

    // Method store akan kita isi nanti
    // Method myRequests juga nanti
}