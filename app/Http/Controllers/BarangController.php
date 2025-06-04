<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Unit;
use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Penting untuk Auth::user()
use Illuminate\Auth\Access\AuthorizationException; // Untuk menampilkan error 403

class BarangController extends Controller
{
    public function __construct()
    {
        // Kita tidak lagi menggunakan middleware Spatie di sini karena masalah sebelumnya.
        // Pengecekan permission akan dilakukan di setiap method.
    }

    public function index()
    {
        if (!Auth::user()->hasPermissionTo('barang-list')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat daftar barang.');
        }
        $barangs = Barang::with(['kategori', 'unit', 'lokasi'])->orderBy('created_at', 'desc')->paginate(10);
        return view('barang.index', compact('barangs'));
    }

    public function create()
    {
        if (!Auth::user()->hasPermissionTo('barang-create')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menambah barang.');
        }
        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();
        $units = Unit::orderBy('nama_unit', 'asc')->get();
        $lokasis = Lokasi::orderBy('nama_lokasi', 'asc')->get();
        return view('barang.create', compact('kategoris', 'units', 'lokasis'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('barang-create')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menyimpan barang.');
        }
        // ... validasi data ...
        $validatedData = $request->validate([ /* ... aturan validasi Anda ... */
            'nama_barang' => 'required|string|max:255',
            'kode_barang' => 'nullable|string|max:50|unique:barangs,kode_barang',
            'deskripsi'   => 'nullable|string',
            'kategori_id' => 'nullable|exists:kategoris,id',
            'unit_id'     => 'nullable|exists:units,id',
            'lokasi_id'   => 'nullable|exists:lokasis,id',
            'stok'        => 'nullable|integer|min:0',
            'stok_minimum'=> 'nullable|integer|min:0',
            'harga_beli'  => 'nullable|numeric|min:0',
            'status'      => 'required|in:aktif,rusak,hilang,dipinjam',
            'gambar'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // ... logika simpan gambar ...
        if ($request->hasFile('gambar')) {
            $imageName = time().'.'.$request->gambar->extension();
            $request->gambar->move(public_path('images/barangs'), $imageName);
            $validatedData['gambar'] = $imageName;
        } else {
            $validatedData['gambar'] = null;
        }
        
        Barang::create($validatedData);
        return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan!');
    }

    public function show(Barang $barang)
    {
        if (!Auth::user()->hasPermissionTo('barang-show')) {
            // Alternatif: jika 'barang-show' tidak ada, bisa pakai 'barang-list'
            // if (!Auth::user()->hasAnyPermission(['barang-show', 'barang-list'])) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat detail barang ini.');
        }
        // Eager load relasi jika belum ter-load (Route Model Binding biasanya tidak eager load by default)
        $barang->load(['kategori', 'unit', 'lokasi']);
        return view('barang.show', compact('barang'));
    }

    public function edit(Barang $barang)
    {
        if (!Auth::user()->hasPermissionTo('barang-edit')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk mengedit barang ini.');
        }
        $barang->load(['kategori', 'unit', 'lokasi']); // Load relasi untuk form
        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();
        $units = Unit::orderBy('nama_unit', 'asc')->get();
        $lokasis = Lokasi::orderBy('nama_lokasi', 'asc')->get();
        return view('barang.edit', compact('barang', 'kategoris', 'units', 'lokasis'));
    }

    public function update(Request $request, Barang $barang)
    {
        if (!Auth::user()->hasPermissionTo('barang-edit')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk memperbarui barang ini.');
        }
        // ... validasi data (pastikan aturan unique diupdate dengan benar) ...
        $validatedData = $request->validate([ /* ... aturan validasi Anda, sesuaikan unique rule ... */
            'nama_barang' => 'required|string|max:255',
            'kode_barang' => 'nullable|string|max:50|unique:barangs,kode_barang,' . $barang->id,
            'deskripsi'   => 'nullable|string',
            'kategori_id' => 'nullable|exists:kategoris,id',
            'unit_id'     => 'nullable|exists:units,id',
            'lokasi_id'   => 'nullable|exists:lokasis,id',
            'stok'        => 'nullable|integer|min:0',
            'stok_minimum'=> 'nullable|integer|min:0',
            'harga_beli'  => 'nullable|numeric|min:0',
            'status'      => 'required|in:aktif,rusak,hilang,dipinjam',
            'gambar'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // ... logika update gambar ...
        if ($request->hasFile('gambar')) {
            if ($barang->gambar) {
                $oldImagePath = public_path('images/barangs/' . $barang->gambar);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $imageName = time().'.'.$request->gambar->extension();
            $request->gambar->move(public_path('images/barangs'), $imageName);
            $validatedData['gambar'] = $imageName;
        }
        
        $barang->update($validatedData);
        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui!');
    }

    public function destroy(Barang $barang)
    {
        if (!Auth::user()->hasPermissionTo('barang-delete')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk menghapus barang ini.');
        }
        // ... logika hapus gambar dan barang ...
        if ($barang->gambar) {
            $imagePath = public_path('images/barangs/' . $barang->gambar);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $barang->delete();
        return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus!');
    }
}