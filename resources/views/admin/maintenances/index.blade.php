@extends('layouts.app')
@section('title', 'Jadwal Maintenance')
@section('content')
<div class="container">
    {{-- Panel Kontrol (Filter & Tombol Aksi) --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filter Jadwal</h5>
            <a href="{{ route('admin.maintenances.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Tambah Jadwal</a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.maintenances.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control form-control-sm" name="search" placeholder="Cari nama perbaikan..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status_filter" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="Dijadwalkan" {{ request('status_filter') == 'Dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                        <option value="Selesai" {{ request('status_filter') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Dibatalkan" {{ request('status_filter') == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.maintenances.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Data --}}
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Daftar Jadwal Maintenance</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Perbaikan</th>
                            <th>Barang Terkait</th>
                            <th>Tanggal</th>
                            <th>Biaya</th>
                            <th>Status</th>
                            <th>Lampiran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($maintenances as $item)
                            <tr>
                                <td>{{ $item->nama_perbaikan }}</td>
                                <td>{{ $item->barang->nama_barang ?? 'Umum/Lainnya' }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_maintenance)->isoFormat('DD MMM YYYY') }}</td>
                                <td>
                                    {{ $item->nama_perbaikan }}
                                    @if($item->is_recurring)
                                        <i class="bi bi-arrow-repeat text-muted" title="Jadwal Berulang setiap {{ $item->recurrence_interval }} {{ $item->recurrence_unit }}"></i>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($item->biaya, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge 
                                        @if($item->status == 'Selesai') bg-success 
                                        @elseif($item->status == 'Dijadwalkan') bg-warning 
                                        @else bg-danger @endif">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->lampiran)
                                        <a href="{{ asset('storage/' . $item->lampiran) }}" target="_blank" class="btn btn-outline-secondary btn-sm" title="Lihat Lampiran">
                                            <i class="bi bi-paperclip"></i>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.maintenances.show', $item->id) }}" class="btn btn-info btn-sm" title="Lihat Detail"><i class="bi bi-eye"></i></a>
                                    @if($item->is_recurring)
                                        <a href="{{ route('admin.maintenances.schedules', $item->id) }}" class="btn btn-primary btn-sm" title="Lihat Jadwal"><i class="bi bi-calendar-event"></i></a>
                                    @endif
                                    <a href="{{ route('admin.maintenances.edit', $item->id) }}" class="btn btn-warning btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                    <form action="{{ route('admin.maintenances.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus jadwal ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data jadwal maintenance.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($maintenances->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $maintenances->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection