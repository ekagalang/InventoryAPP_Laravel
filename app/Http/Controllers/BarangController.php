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

    public function index(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('barang-list')) {
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin untuk melihat daftar barang.');
        }

        $query = Barang::with(['kategori', 'unit', 'lokasi']);

        // Advanced Search & Filtering
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('lokasi_id')) {
            $query->where('lokasi_id', $request->lokasi_id);
        }

        if ($request->filled('tipe_item')) {
            $query->where('tipe_item', $request->tipe_item);
        }

        if ($request->filled('stok_min')) {
            $query->where('stok', '>=', $request->stok_min);
        }

        if ($request->filled('stok_max')) {
            $query->where('stok', '<=', $request->stok_max);
        }

        if ($request->filled('low_stock_only') && $request->low_stock_only) {
            $query->whereRaw('stok <= stok_minimum');
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $barangs = $query->paginate(15)->appends($request->query());
        
        // Data for filters
        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();
        $units = Unit::orderBy('nama_unit', 'asc')->get();
        $lokasis = Lokasi::orderBy('nama_lokasi', 'asc')->get();
        
        // Current filters for the view
        $currentFilters = $request->only([
            'search', 'kategori_id', 'unit_id', 'lokasi_id', 'tipe_item', 
            'stok_min', 'stok_max', 'low_stock_only', 'sort', 'direction'
        ]);

        return view('barang.index', compact('barangs', 'kategoris', 'units', 'lokasis', 'currentFilters'));
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
            'tipe_item'   => 'required|in:habis_pakai,aset',
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

    /**
     * Bulk delete multiple barang
     */
    public function bulkDelete(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('barang-delete')) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:barangs,id'
        ]);

        try {
            $deletedCount = 0;
            foreach ($request->ids as $id) {
                $barang = Barang::findOrFail($id);
                
                // Delete image if exists
                if ($barang->gambar) {
                    $imagePath = public_path('images/barangs/' . $barang->gambar);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                
                $barang->delete();
                $deletedCount++;
            }

            return response()->json([
                'success' => true, 
                'message' => "{$deletedCount} item(s) deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error deleting items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update multiple barang
     */
    public function bulkUpdate(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('barang-edit')) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:barangs,id',
            'data' => 'required|array'
        ]);

        try {
            $updateData = $request->data;
            $updatedCount = Barang::whereIn('id', $request->ids)->update($updateData);

            return response()->json([
                'success' => true, 
                'message' => "{$updatedCount} item(s) updated successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error updating items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk export selected barang
     */
    public function bulkExport(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('barang-list')) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:barangs,id'
        ]);

        try {
            $barangs = Barang::with(['kategori', 'unit', 'lokasi'])
                            ->whereIn('id', $request->ids)
                            ->get();

            $filename = 'selected_barangs_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($barangs) {
                $file = fopen('php://output', 'w');
                
                // CSV headers
                fputcsv($file, [
                    'ID', 'Nama Barang', 'Kode Barang', 'Tipe Item', 
                    'Kategori', 'Unit', 'Lokasi', 'Stok', 'Stok Minimum', 
                    'Deskripsi', 'Created At'
                ]);
                
                // CSV data
                foreach ($barangs as $barang) {
                    fputcsv($file, [
                        $barang->id,
                        $barang->nama_barang,
                        $barang->kode_barang,
                        ucfirst($barang->tipe_item),
                        $barang->kategori->nama_kategori ?? '',
                        $barang->unit->nama_unit ?? '',
                        $barang->lokasi->nama_lokasi ?? '',
                        $barang->stok,
                        $barang->stok_minimum,
                        $barang->deskripsi,
                        $barang->created_at->format('Y-m-d H:i:s')
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error exporting items: ' . $e->getMessage());
        }
    }
}