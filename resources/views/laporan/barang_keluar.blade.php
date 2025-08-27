@extends('layouts.app')

@section('title', 'Laporan Barang Keluar')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Laporan Barang Keluar</h1>
        {{-- Tombol Aksi (misal: Print, Export - nanti) --}}
    </div>

    {{-- FORM FILTER --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Filter Laporan</h5>
            <form action="{{ route('laporan.barang.keluar') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="barang_id_filter" class="form-label">Barang</label>
                    <select class="form-select form-select-sm" id="barang_id_filter" name="barang_id">
                        <option value="">-- Semua Barang --</option>
                        @foreach ($barangs as $barang)
                            <option value="{{ $barang->id }}" {{ $filterBarangId == $barang->id ? 'selected' : '' }}>
                                {{ $barang->nama_barang }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="user_id_filter" class="form-label">Diproses Oleh</label>
                    <select class="form-select form-select-sm" id="user_id_filter" name="user_id">
                        <option value="">-- Semua User --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ $filterUserId == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tanggal_mulai_filter" class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control form-control-sm" id="tanggal_mulai_filter" name="tanggal_mulai" value="{{ $filterTanggalMulai }}">
                </div>
                <div class="col-md-3">
                    <label for="tanggal_akhir_filter" class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control form-control-sm" id="tanggal_akhir_filter" name="tanggal_akhir" value="{{ $filterTanggalAkhir }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>
    {{-- AKHIR FORM FILTER --}}

    {{-- GRAFIK BARANG KELUAR --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Tren Barang Keluar (6 Bulan Terakhir)</h5>
                    <canvas id="chartBarangKeluar" width="400" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
    {{-- AKHIR GRAFIK BARANG KELUAR --}}

    <div class="card shadow-sm">
        <div class="card-body">
            @if($barangKeluar->isEmpty())
                <div class="alert alert-info text-center">
                    Tidak ada data barang keluar yang sesuai dengan filter Anda.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tgl. Pergerakan</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th class="text-end">Kuantitas Keluar</th>
                                <th>Unit</th>
                                <th>Stok Sebelum</th>
                                <th>Stok Setelah</th>
                                <th>Diproses Oleh</th>
                                <th>Catatan/Keperluan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($barangKeluar as $key => $movement)
                                <tr>
                                    <td>{{ $barangKeluar->firstItem() + $key }}</td>
                                    <td>{{ $movement->tanggal_pergerakan ? \Carbon\Carbon::parse($movement->tanggal_pergerakan)->isoFormat('DD MMM YY, HH:mm') : $movement->created_at->isoFormat('DD MMM YY, HH:mm') }}</td>
                                    <td>{{ $movement->barang->kode_barang ?? '-' }}</td>
                                    <td>
                                        @if($movement->barang)
                                            <a href="{{ route('barang.show', $movement->barang_id) }}">{{ $movement->barang->nama_barang }}</a>
                                        @else
                                            <span class="text-muted">Barang Dihapus</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-danger">-{{ number_format($movement->kuantitas, 0, ',', '.') }}</td>
                                    <td>{{ $movement->barang->unit->singkatan_unit ?? $movement->barang->unit->nama_unit ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($movement->stok_sebelumnya, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($movement->stok_setelahnya, 0, ',', '.') }}</td>
                                    <td>{{ $movement->user->name ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($movement->catatan, 50) ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($barangKeluar->hasPages())
        <div class="card-footer">
            {{ $barangKeluar->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grafik Barang Keluar per Bulan
    const ctx = document.getElementById('chartBarangKeluar').getContext('2d');
    
    // Prepare data untuk grafik
    const chartData = @json($barangKeluarPerBulan);
    const labels = chartData.map(item => {
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return monthNames[item.bulan - 1] + ' ' + item.tahun;
    });
    const data = chartData.map(item => item.total_keluar);

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Barang Keluar',
                data: data,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
});
</script>
@endpush
@endsection