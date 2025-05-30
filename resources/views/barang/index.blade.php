@extends('layouts.app') {{-- Memberitahu Blade untuk menggunakan layouts/app.blade.php --}}

@section('title', 'Daftar Barang') {{-- Mengatur judul halaman spesifik --}}

@section('content') {{-- Memulai section konten --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Daftar Barang</h1>
    <a href="{{ route('barang.create') }}" class="btn btn-primary">Tambah Barang Baru</a>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-light">
                <tr>
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">Nama Barang</th>
                    <th class="text-center align-middle">Kategori</th> {{-- KOLOM BARU --}}
                    <th class="text-center align-middle">Kode</th>
                    <th class="text-center align-middle">Stok</th>
                    <th class="text-center align-middle">Status</th>
                    <th class="text-center align-middle">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($barangs as $key => $barang)
                    <tr>
                        <td class="text-center align-middle">{{ $barangs->firstItem() + $key }}</td>
                        <td class="align-middle">{{ $barang->nama_barang }}</td> {{-- Biarkan nama barang rata kiri --}}
                        <td class="align-middle">{{ $barang->kategori->nama_kategori ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $barang->kode_barang ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $barang->stok }}</td>
                        <td class="text-center align-middle">
                            @if ($barang->status == 'aktif')
                                <span class="badge bg-success text-capitalize">{{ $barang->status }}</span>
                            @elseif ($barang->status == 'rusak')
                                <span class="badge bg-warning text-capitalize">{{ $barang->status }}</span>
                            @elseif ($barang->status == 'hilang')
                                <span class="badge bg-danger text-capitalize">{{ $barang->status }}</span>
                            @else
                                <span class="badge bg-secondary text-capitalize">{{ $barang->status }}</span>
                            @endif
                        </td>
                        <td class="text-center align-middle"> {{-- Kolom Aksi --}}
                            <a href="{{ route('barang.show', $barang->id) }}" class="btn btn-info btn-sm" title="Detail"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('barang.edit', $barang->id) }}" class="btn btn-warning btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>
                            <form action="{{ route('barang.destroy', $barang->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center align-middle">Tidak ada data barang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($barangs->hasPages())
        <div class="card-footer">
            {{ $barangs->links() }}
        </div>
    @endif
</div>
@endsection {{-- Mengakhiri section konten --}}

{{-- @push('scripts')
    <script>
        // Tambahkan script JS spesifik untuk halaman ini jika perlu
        // console.log('Script untuk halaman index barang.');
    </script>
@endpush --}}