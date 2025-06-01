@extends('layouts.app')

@section('title', 'Riwayat Pergerakan Stok')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Riwayat Pergerakan Stok</h1>
        
        <div>
            <a href="{{ route('stok.masuk.create') }}" class="btn btn-success"><i class="bi bi-plus-circle"></i> Catat Barang Masuk</a>
            <a href="{{ route('stok.keluar.create') }}" class="btn btn-danger"><i class="bi bi-dash-circle"></i> Catat Barang Keluar</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

        <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Filter Riwayat</h5>
            <form action="{{ route('stok.pergerakan.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="barang_id_filter" class="form-label">Barang</label>
                    <select class="form-select" id="barang_id_filter" name="barang_id">
                        <option value="">-- Semua Barang --</option>
                        @foreach ($barangs as $barang)
                            <option value="{{ $barang->id }}" {{ $filterBarangId == $barang->id ? 'selected' : '' }}>
                                {{ $barang->nama_barang }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="tipe_pergerakan_filter" class="form-label">Tipe</label>
                    <select class="form-select" id="tipe_pergerakan_filter" name="tipe_pergerakan">
                        <option value="">-- Semua Tipe --</option>
                        <option value="masuk" {{ $filterTipePergerakan == 'masuk' ? 'selected' : '' }}>Masuk</option>
                        <option value="keluar" {{ $filterTipePergerakan == 'keluar' ? 'selected' : '' }}>Keluar</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tanggal_mulai_filter" class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" id="tanggal_mulai_filter" name="tanggal_mulai" value="{{ $filterTanggalMulai }}">
                </div>
                <div class="col-md-3">
                    <label for="tanggal_akhir_filter" class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" id="tanggal_akhir_filter" name="tanggal_akhir" value="{{ $filterTanggalAkhir }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($stockMovements->isEmpty())
                <div class="alert alert-info text-center">
                    Belum ada data pergerakan stok.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Barang</th>
                                <th>Tipe</th>
                                <th>Kuantitas</th>
                                <th>Stok Sebelum</th>
                                <th>Stok Setelah</th>
                                <th>Dicatat Oleh</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stockMovements as $key => $movement)
                                <tr>
                                    <td>{{ $stockMovements->firstItem() + $key }}</td>
                                    <td>{{ $movement->tanggal_pergerakan ? \Carbon\Carbon::parse($movement->tanggal_pergerakan)->isoFormat('DD MMM YYYY, HH:mm') : '-' }}</td>
                                    <td>
                                        @if($movement->barang)
                                            <a href="{{ route('barang.show', $movement->barang_id) }}">{{ $movement->barang->nama_barang }}</a>
                                            <br><small class="text-muted">{{ $movement->barang->kode_barang ?? '' }}</small>
                                        @else
                                            <span class="text-muted">Barang Dihapus</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($movement->tipe_pergerakan == 'masuk')
                                            <span class="badge bg-success text-capitalize"><i class="bi bi-arrow-down-circle-fill"></i> {{ $movement->tipe_pergerakan }}</span>
                                        @elseif ($movement->tipe_pergerakan == 'keluar')
                                            <span class="badge bg-danger text-capitalize"><i class="bi bi-arrow-up-circle-fill"></i> {{ $movement->tipe_pergerakan }}</span>
                                        @else
                                            <span class="badge bg-secondary text-capitalize">{{ $movement->tipe_pergerakan }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end {{ $movement->tipe_pergerakan == 'masuk' ? 'text-success fw-bold' : 'text-danger fw-bold' }}">
                                        {{ $movement->tipe_pergerakan == 'masuk' ? '+' : '-' }}{{ number_format($movement->kuantitas, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">{{ number_format($movement->stok_sebelumnya, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($movement->stok_setelahnya, 0, ',', '.') }}</td>
                                    <td>{{ $movement->user->name ?? 'Sistem/Tidak Diketahui' }}</td>
                                    <td>{{ Str::limit($movement->catatan, 70, '...') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($stockMovements->hasPages())
        <div class="card-footer">
            {{ $stockMovements->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
{{-- Jika ada script khusus untuk halaman ini --}}
@endpush