<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kategoris = Kategori::withCount('barangs')->orderBy('nama_kategori', 'asc')->paginate(10);
        // $kategoris = Kategori::orderBy('nama_kategori', 'asc')->paginate(10); // Ambil semua kategori, urutkan & paginasi
        return view('kategori.index', compact('kategoris')); // Kirim data ke view
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kategori.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategoris,nama_kategori',
            'deskripsi_kategori' => 'nullable|string',
        ]);

        Kategori::create($validatedData);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kategori $kategori) // Route Model Binding
    {
        return view('kategori.edit', compact('kategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kategori $kategori) // Route Model Binding
    {
        $validatedData = $request->validate([
            // Pastikan nama_kategori unik kecuali untuk kategori ini sendiri
            'nama_kategori' => 'required|string|max:255|unique:kategoris,nama_kategori,' . $kategori->id,
            'deskripsi_kategori' => 'nullable|string',
        ]);

        $kategori->update($validatedData);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kategori $kategori) // Route Model Binding
    {
        // Opsi: Cek apakah ada barang yang terkait sebelum menghapus,
        // meskipun kita sudah set onDelete('set null').
        // Ini bisa digunakan untuk memberikan pesan yang lebih spesifik jika diperlukan.
        // if ($kategori->barangs()->count() > 0) {
        //     return redirect()->route('kategori.index')->with('error', 'Kategori tidak dapat dihapus karena masih memiliki barang terkait. Harap pindahkan barang ke kategori lain atau atur ulang kategori barang tersebut.');
        // }

        $kategori->delete();

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus! Barang yang terkait telah diatur ulang (tidak memiliki kategori).');
    }
}
