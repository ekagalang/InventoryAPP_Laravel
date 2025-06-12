@extends('layouts.app') {{-- Memberitahu Blade untuk menggunakan layouts/app.blade.php --}}

@section('title', 'Daftar Barang') {{-- Mengatur judul halaman spesifik --}}

@section('content') {{-- Memulai section konten --}}
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Filter Barang</h5>
        @can('barang-create')
            <a href="{{ route('barang.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Tambah Barang Baru</a>
        @endcan
    </div>
    <div class="card-body">
        <form action="{{ route('barang.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search_filter" class="form-label">Cari (Nama/Kode)</label>
                <input type="text" class="form-control form-control-sm" id="search_filter" name="search" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label for="tipe_item_filter" class="form-label">Tipe Item</label>
                <select name="tipe_item_filter" id="tipe_item_filter" class="form-select form-select-sm">
                    <option value="">Semua Tipe</option>
                    <option value="habis_pakai" {{ request('tipe_item_filter') == 'habis_pakai' ? 'selected' : '' }}>Barang Habis Pakai</option>
                    <option value="aset" {{ request('tipe_item_filter') == 'aset' ? 'selected' : '' }}>Aset (Barang Pinjaman)</option>
                </select>
            </div>
            {{-- Anda bisa tambahkan filter lain di sini (Kategori, Lokasi) --}}
            <div class="col-md-3 d-flex align-items-end">
                <div>
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('barang.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-hover table-striped table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">Nama Barang</th>
                    <th class="text-center">Tipe Item</th>
                    <th class="text-center">Kategori</th>
                    <th class="text-center">Unit</th>
                    <th class="text-center">Lokasi</th>
                    <th class="text-center">Kode</th>
                    <th class="text-center">Stok</th>
                    <th class="text-center">Stok Min.</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($barangs as $key => $barang)
                    <tr>
                        <td class="text-center align-middle">{{ $barangs->firstItem() + $key }}</td>
                        <td class="text-center align-middle">{{ $barang->nama_barang }}</td> {{-- Biarkan nama barang rata kiri --}}
                        <td>
                            @if($barang->tipe_item == 'aset')
                                <span class="badge bg-info">Aset</span>
                            @else
                                <span class="badge bg-secondary">Habis Pakai</span>
                            @endif
                        </td>
                        <td class="text-center align-middle">{{ $barang->kategori->nama_kategori ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $barang->unit->singkatan_unit ?? ($barang->unit->nama_unit ?? '-') }}</td>
                        <td class="text-center align-middle">{{ $barang->lokasi->nama_lokasi ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $barang->kode_barang ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $barang->stok }}</td>
                        <td class="text-center align-middle">{{ number_format($barang->stok_minimum, 0, ',', '.') }}</td>
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
                        <td>
                            @can('barang-show')
                                <a href="{{ route('barang.show', $barang->id) }}" class="btn btn-info btn-sm" title="Detail"><i class="bi bi-eye"></i> Detail</a>
                            @endcan
                            @can('barang-edit')
                                <a href="{{ route('barang.edit', $barang->id) }}" class="btn btn-warning btn-sm" title="Edit"><i class="bi bi-pencil-square"></i> Edit</a>
                            @endcan
                            @can('barang-delete')
                                <form action="{{ route('barang.destroy', $barang->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                            @endcan
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