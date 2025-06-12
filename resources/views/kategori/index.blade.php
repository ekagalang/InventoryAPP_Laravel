@extends('layouts.app')

@section('title', 'Daftar Kategori Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Daftar Kategori Barang</h1>
    @can('kategori-create')
    <div class="mb-3">
        <a href="{{ route('kategori.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah Kategori Baru</a>
    </div>
    @endcan
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-hover table-striped table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Nama Kategori</th>
                    <th class="text-center">Deskripsi</th>
                    <th class="text-center">Jumlah Barang</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kategoris as $key => $kategori)
                    <tr>
                        <td>{{ $kategoris->firstItem() + $key }}</td>
                        <td>{{ $kategori->nama_kategori }}</td>
                        <td>{{ Str::limit($kategori->deskripsi_kategori, 50, '...') ?? '-' }}</td>
                        <td>{{ $kategori->barangs_count ?? $kategori->barangs->count() }}</td> {{-- Menampilkan jumlah barang terkait --}}
                        <td>
                            @can('kategori-edit')
                                <a href="{{ route('kategori.edit', $kategori->id) }}" class="btn btn-warning btn-sm" title="Edit"><i class="bi bi-pencil-square"></i> Edit</a>
                            @endcan
                            @can('kategori-delete')
                                <form action="{{ route('kategori.destroy', $kategori->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data kategori.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($kategoris->hasPages())
    <div class="card-footer">
        {{ $kategoris->links() }}
    </div>
    @endif
</div>
@endsection