<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load barangs_count untuk optimasi
        $units = Unit::withCount('barangs')->orderBy('nama_unit', 'asc')->paginate(10);
        return view('unit.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('unit.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('barang-create')) {
            abort(403, 'ANDA TIDAK MEMILIKI IZIN UNTUK MENAMBAH BARANG.');
        }

        $validatedData = $request->validate([
            'nama_unit' => 'required|string|max:255|unique:units,nama_unit',
            'singkatan_unit' => 'nullable|string|max:50|unique:units,singkatan_unit',
            'deskripsi_unit' => 'nullable|string',
        ]);

        Unit::create($validatedData);

        return redirect()->route('unit.index')->with('success', 'Unit berhasil ditambahkan!');
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
    public function edit(Unit $unit)
    {
        if (!Auth::user()->hasPermissionTo('barang-create')) {
            abort(403, 'ANDA TIDAK MEMILIKI IZIN UNTUK MENAMBAH BARANG.');
        }

        return view('unit.edit', compact('unit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit) // Route Model Binding
    {
        $validatedData = $request->validate([
            'nama_unit' => 'required|string|max:255|unique:units,nama_unit,' . $unit->id,
            'singkatan_unit' => 'nullable|string|max:50|unique:units,singkatan_unit,' . $unit->id,
            'deskripsi_unit' => 'nullable|string',
        ]);

        $unit->update($validatedData);

        return redirect()->route('unit.index')->with('success', 'Unit berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit) // Route Model Binding
    {
        $unit->delete();

        return redirect()->route('unit.index')->with('success', 'Unit berhasil dihapus! Barang yang terkait telah diatur ulang (tidak memiliki unit).');
    }
}
