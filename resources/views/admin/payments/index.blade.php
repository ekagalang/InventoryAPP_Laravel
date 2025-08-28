@extends('layouts.app')

@section('title', 'Pembayaran Rutin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pembayaran Rutin</h1>
        <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Pembayaran
        </a>
    </div>

    {{-- Filter --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.payments.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Cari</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="{{ request('search') }}" placeholder="Nama pembayaran">
                </div>
                <div class="col-md-3">
                    <label for="kategori_filter" class="form-label">Kategori</label>
                    <select class="form-select form-select-sm" id="kategori_filter" name="kategori_filter">
                        <option value="">-- Semua Kategori --</option>
                        <option value="platform" {{ request('kategori_filter') == 'platform' ? 'selected' : '' }}>Platform Digital</option>
                        <option value="utilitas" {{ request('kategori_filter') == 'utilitas' ? 'selected' : '' }}>Utilitas (IPL)</option>
                        <option value="asuransi" {{ request('kategori_filter') == 'asuransi' ? 'selected' : '' }}>Asuransi</option>
                        <option value="sewa" {{ request('kategori_filter') == 'sewa' ? 'selected' : '' }}>Sewa</option>
                        <option value="berlangganan" {{ request('kategori_filter') == 'berlangganan' ? 'selected' : '' }}>Berlangganan</option>
                        <option value="lainnya" {{ request('kategori_filter') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status_filter" class="form-label">Status</label>
                    <select class="form-select form-select-sm" id="status_filter" name="status_filter">
                        <option value="">-- Semua Status --</option>
                        <option value="aktif" {{ request('status_filter') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status_filter') == 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
                        <option value="selesai" {{ request('status_filter') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm me-2">Filter</button>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Daftar Pembayaran Rutin</h6>
        </div>
        <div class="card-body">
            @if($payments->isEmpty())
                <div class="alert alert-info text-center">
                    Tidak ada data pembayaran rutin yang ditemukan.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Pembayaran</th>
                                <th>Kategori</th>
                                <th>Penerima</th>
                                <th>Nominal</th>
                                <th>Mulai</th>
                                <th>Berulang</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $index => $payment)
                                <tr>
                                    <td>{{ $payments->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $payment->nama_pembayaran }}</strong>
                                        @if($payment->deskripsi)
                                            <br><small class="text-muted">{{ Str::limit($payment->deskripsi, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge 
                                            @if($payment->kategori == 'platform') bg-info
                                            @elseif($payment->kategori == 'utilitas') bg-warning
                                            @elseif($payment->kategori == 'asuransi') bg-success
                                            @elseif($payment->kategori == 'sewa') bg-primary
                                            @elseif($payment->kategori == 'berlangganan') bg-secondary
                                            @else bg-dark @endif">
                                            {{ $payment->kategori_label }}
                                        </span>
                                    </td>
                                    <td>{{ $payment->penerima ?? '-' }}</td>
                                    <td>Rp {{ number_format($payment->nominal, 0, ',', '.') }}</td>
                                    <td>{{ $payment->tanggal_mulai->format('d M Y') }}</td>
                                    <td>
                                        @if($payment->is_recurring)
                                            <i class="bi bi-arrow-repeat text-success" title="Berulang"></i>
                                            Setiap {{ $payment->recurrence_interval }} {{ $payment->recurrence_unit }}
                                        @else
                                            <i class="bi bi-dash text-muted" title="Sekali saja"></i> Sekali
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge 
                                            @if($payment->status == 'aktif') bg-success
                                            @elseif($payment->status == 'nonaktif') bg-secondary  
                                            @else bg-danger @endif">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-info btn-sm" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($payment->is_recurring)
                                            <a href="{{ route('admin.payments.schedules', $payment) }}" class="btn btn-primary btn-sm" title="Lihat Jadwal">
                                                <i class="bi bi-calendar-event"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin ingin menghapus pembayaran ini?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($payments->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $payments->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection