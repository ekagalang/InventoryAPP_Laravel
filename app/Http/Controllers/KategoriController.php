<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Pastikan Auth di-import
use Illuminate\Auth\Access\AuthorizationException; // Atau gunakan abort()

class KategoriController extends Controller
{
    public function __construct()
    {
        // Pengecekan permission dilakukan di setiap method.
    }

    public function index()
    {
        if (!Auth::user()->hasPermissionTo('kategori-list')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat daftar kategori.');
        }
        $kategoris = Kategori::withCount('barangs')->orderBy('nama_kategori', 'asc')->paginate(10);
        return view('kategori.index', compact('kategoris'));
    }

    public function create()
    {
        if (!Auth::user()->hasPermissionTo('kategori-create')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menambah kategori.');
        }
        return view('kategori.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('kategori-create')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menyimpan kategori.');
        }
        
        $validatedData = $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategoris,nama_kategori',
            'deskripsi_kategori' => 'nullable|string',
        ]);
        Kategori::create($validatedData);
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function show(Kategori $kategori) // Asumsi Anda ingin method show, jika tidak, bisa dihapus
    {
        // Gunakan 'kategori-list' atau buat permission 'kategori-show' jika perlu lebih spesifik
        if (!Auth::user()->hasPermissionTo('kategori-list')) { 
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat detail kategori ini.');
        }
        // Jika ada view khusus untuk show kategori, misalnya:
        // return view('kategori.show', compact('kategori'));
        // Untuk saat ini, mungkin tidak ada view show khusus untuk kategori, bisa diarahkan ke edit atau index.
        // Atau, jika tidak ada view show, method ini bisa dihapus dari controller dan Route::resource.
        // Jika hanya untuk data API, tetap perlu proteksi.
        // Untuk konsistensi, jika tidak ada view show, mungkin lebih baik tidak ada method show di controller resource web.
        // Saya akan asumsikan Anda mungkin belum membuat view kategori.show.blade.php.
        // Jadi, kita bisa komentari atau hapus method ini jika tidak digunakan.
        // Untuk sekarang, saya biarkan ada dengan proteksi.
        return view('kategori.edit', compact('kategori')); // Contoh: arahkan ke edit jika tidak ada view show
    }

    public function edit(Kategori $kategori)
    {
        if (!Auth::user()->hasPermissionTo('kategori-edit')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk mengedit kategori ini.');
        }
        return view('kategori.edit', compact('kategori'));
    }

    public function update(Request $request, Kategori $kategori)
    {
        if (!Auth::user()->hasPermissionTo('kategori-edit')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk memperbarui kategori ini.');
        }
        
        $validatedData = $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategoris,nama_kategori,' . $kategori->id,
            'deskripsi_kategori' => 'nullable|string',
        ]);
        $kategori->update($validatedData);
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy(Kategori $kategori)
    {
        if (!Auth::user()->hasPermissionTo('kategori-delete')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menghapus kategori ini.');
        }
        
        // Tambahkan logika pengecekan apakah kategori masih digunakan oleh barang (opsional tapi bagus)
        if ($kategori->barangs()->count() > 0) {
            return redirect()->route('kategori.index')->with('error', 'Kategori "'.$kategori->nama_kategori.'" tidak dapat dihapus karena masih digunakan oleh beberapa barang.');
        }

        $kategori->delete();
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus!');
    }
}