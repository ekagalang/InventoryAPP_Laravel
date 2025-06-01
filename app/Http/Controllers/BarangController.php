<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Unit;
use App\Models\Lokasi;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barangs = Barang::with(['kategori', 'unit', 'lokasi'])->orderBy('created_at', 'desc')->paginate(10);
        // $barangs = Barang::orderBy('created_at', 'desc')->paginate(10); // Ambil semua barang, urutkan & paginasi
        return view('barang.index', compact('barangs')); // Kirim data ke view
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get(); // Ambil semua kategori
        $units = Unit::orderBy('nama_unit', 'asc')->get();
        $lokasis = Lokasi::orderBu('nama_lokasi', 'asc')->get();
        return view('barang.create', compact('kategoris', 'units', 'lokasis')); // Kirim data kategoris ke view
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kode_barang' => 'nullable|string|max:50|unique:barangs,kode_barang',
            'deskripsi'   => 'nullable|string',
            'kategori_id' => 'nullable|exists:kategoris,id', // Validasi bahwa kategori_id ada di tabel kategoris
            'unit_id'     => 'nullable|exists:units,id',
            'lokasi_id'   => 'nullable|exists:lokasis,id',
            'stok'        => 'nullable|integer|min:0',
            'harga_beli'  => 'nullable|numeric|min:0',
            'status'      => 'required|in:aktif,rusak,hilang,dipinjam',
            'gambar'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // 2. Handle Upload Gambar (jika ada)
        if ($request->hasFile('gambar')) {
            $imageName = time().'.'.$request->gambar->extension();
            // Simpan gambar ke public/images/barangs atau storage/app/public/images/barangs
            // Contoh ke public:
            $request->gambar->move(public_path('images/barangs'), $imageName);
            // Atau jika menggunakan storage link (lebih direkomendasikan):
            // $path = $request->file('gambar')->store('public/images/barangs');
            // $imageName = basename($path);

            $validatedData['gambar'] = $imageName; // Simpan nama file ke database
        } else {
            $validatedData['gambar'] = null;
        }


        // 3. Buat dan Simpan Data Barang Baru
        Barang::create($validatedData);

        // 4. Redirect dengan Pesan Sukses
        return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Barang $barang)
    {
        return view('barang.show', compact('barang'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barang $barang)
    {
        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get(); // Ambil semua kategori
        $units = Unit::orderBy('nama_unit', 'asc')->get();
        $lokasis = Lokasi::orderBy('nama_lokasi', 'asc')->get();
        return view('barang.edit', compact('barang', 'kategoris', 'units', 'lokasis')); // Kirim data barang dan kategoris
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barang $barang)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kode_barang' => 'nullable|string|max:50|unique:barangs,kode_barang,' . $barang->id,
            'deskripsi'   => 'nullable|string',
            'kategori_id' => 'nullable|exists:kategoris,id', // Validasi bahwa kategori_id ada di tabel kategoris
            'unit_id'     => 'nullable|exists:units,id',
            'lokasi_id'   => 'nullable|exists:lokasis,id',
            'stok'        => 'nullable|integer|min:0',
            'harga_beli'  => 'nullable|numeric|min:0',
            'status'      => 'required|in:aktif,rusak,hilang,dipinjam',
            'gambar'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // 2. Handle Upload Gambar (jika ada gambar baru)
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada dan jika gambar baru diupload
            if ($barang->gambar) {
                $oldImagePath = public_path('images/barangs/' . $barang->gambar);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $imageName = time().'.'.$request->gambar->extension();
            $request->gambar->move(public_path('images/barangs'), $imageName);
            $validatedData['gambar'] = $imageName; // Update nama file gambar baru
        } else {
            // Jika tidak ada gambar baru diupload, jangan ubah field gambar di database
            // Kecuali jika kita ingin ada opsi "hapus gambar saat ini"
            // Untuk sekarang, kita biarkan gambar lama jika tidak ada upload baru.
            // $validatedData['gambar'] = $barang->gambar; // ini tidak perlu karena field gambar tidak ada di $validatedData jika tidak diupload
        }

        // 3. Update Data Barang
        $barang->update($validatedData);

        // 4. Redirect dengan Pesan Sukses
        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $barang) // Menggunakan Route Model Binding
{
    // 1. Hapus Gambar Terkait (jika ada)
    if ($barang->gambar) {
        $imagePath = public_path('images/barangs/' . $barang->gambar);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // 2. Hapus Data Barang dari Database
    $barang->delete();

    // 3. Redirect dengan Pesan Sukses
    return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus!');
}
}
