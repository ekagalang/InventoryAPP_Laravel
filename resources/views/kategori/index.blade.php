@extends('layouts.app')

@section('title', 'Daftar Kategori Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Daftar Kategori Barang</h1>
    <a href="{{ route('kategori.create') }}" class="btn btn-primary">Tambah Kategori Baru</a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif


<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Barang</th>
                    <th>Aksi</th>
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
                            {{-- <a href="{{ route('kategori.show', $kategori->id) }}" class="btn btn-info btn-sm" title="Detail"><i class="bi bi-eye"></i> Detail</a> --}}
                            <a href="{{ route('kategori.edit', $kategori->id) }}" class="btn btn-warning btn-sm" title="Edit"><i class="bi bi-pencil-square"></i> Edit</a>
                            <form action="{{ route('kategori.destroy', $kategori->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Barang yang terkait dengan kategori ini akan diatur ulang (tidak memiliki kategori).');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="bi bi-trash"></i> Hapus</button>
                            </form>
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