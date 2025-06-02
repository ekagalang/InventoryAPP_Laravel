@extends('layouts.app')

@section('title', 'Daftar Lokasi Penempatan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Daftar Lokasi Penempatan</h1>
@can('lokasi-create')
    <div class="mb-3">
        <a href="{{ route('lokasi.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah Lokasi Baru</a>
    </div>
@endcan
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
                    <th>Nama Lokasi</th>
                    <th>Kode Lokasi</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Barang Terkait</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lokasis as $key => $lokasi)
                    <tr>
                        <td>{{ $lokasis->firstItem() + $key }}</td>
                        <td>{{ $lokasi->nama_lokasi }}</td>
                        <td>{{ $lokasi->kode_lokasi ?? '-' }}</td>
                        <td>{{ Str::limit($lokasi->deskripsi_lokasi, 50, '...') ?? '-' }}</td>
                        <td>{{ $lokasi->barangs_count }}</td>
                        <td>
                            @can('lokasi-edit')
                                <a href="{{ route('lokasi.edit', $lokasi->id) }}" class="btn btn-warning btn-sm" title="Edit"><i class="bi bi-pencil-square"></i> Edit</a>
                            @endcan
                            @can('lokasi-delete')
                                <form action="{{ route('lokasi.destroy', $lokasi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data lokasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($lokasis->hasPages())
    <div class="card-footer">
        {{ $lokasis->links() }}
    </div>
    @endif
</div>
@endsection