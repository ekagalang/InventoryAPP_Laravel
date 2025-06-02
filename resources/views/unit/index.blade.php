@extends('layouts.app')

@section('title', 'Daftar Unit Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Daftar Unit Barang</h1>
    @can('unit-create')
    <div class="mb-3">
        <a href="{{ route('unit.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah Unit Baru</a>
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
                    <th>Nama Unit</th>
                    <th>Singkatan</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Barang Terkait</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($units as $key => $unit)
                    <tr>
                        <td>{{ $units->firstItem() + $key }}</td>
                        <td>{{ $unit->nama_unit }}</td>
                        <td>{{ $unit->singkatan_unit ?? '-' }}</td>
                        <td>{{ Str::limit($unit->deskripsi_unit, 50, '...') ?? '-' }}</td>
                        <td>{{ $unit->barangs_count }}</td>
                        <td>
                            @can('unit-edit')
                                <a href="{{ route('unit.edit', $unit->id) }}" class="btn btn-warning btn-sm" title="Edit"><i class="bi bi-pencil-square"></i> Edit</a>
                            @endcan
                            @can('unit-delete')
                                <form action="{{ route('unit.destroy', $unit->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data unit.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($units->hasPages())
    <div class="card-footer">
        {{ $units->links() }}
    </div>
    @endif
</div>
@endsection