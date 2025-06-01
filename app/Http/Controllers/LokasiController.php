<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lokasis = Lokasi::withCount('barangs')->orderBy('nama_lokasi', 'asc')->paginate(10);
        return view('lokasi.index', compact('lokasis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('lokasi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_lokasi' => 'required|string|max:255|unique:lokasis,nama_lokasi',
            'kode_lokasi' => 'nullable|string|max:50|unique:lokasis,kode_lokasi',
            'deskripsi_lokasi' => 'nullable|string',
        ]);

        Lokasi::create($validatedData);

        return redirect()->route('lokasi.index')->with('success', 'Lokasi berhasil ditambahkan!');
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
    public function edit(Lokasi $lokasi) // Route Model Binding
    {
        return view('lokasi.edit', compact('lokasi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lokasi $lokasi) // Route Model Binding
    {
        $validatedData = $request->validate([
            'nama_lokasi' => 'required|string|max:255|unique:lokasis,nama_lokasi,' . $lokasi->id,
            'kode_lokasi' => 'nullable|string|max:50|unique:lokasis,kode_lokasi,' . $lokasi->id,
            'deskripsi_lokasi' => 'nullable|string',
        ]);

        $lokasi->update($validatedData);

        return redirect()->route('lokasi.index')->with('success', 'Lokasi berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lokasi $lokasi) // Route Model Binding
    {
        $lokasi->delete();

        return redirect()->route('lokasi.index')->with('success', 'Lokasi berhasil dihapus! Barang yang terkait telah diatur ulang (tidak memiliki lokasi).');
    }
}
