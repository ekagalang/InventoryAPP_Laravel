@extends('layouts.app')

@section('title', 'Laporan Biaya Maintenance')

@section('content')
<div class="container-fluid">
    <h1>Laporan Biaya Maintenance</h1>

    {{-- Panel Filter --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Filter Laporan</h5>
            <form action="{{ route('laporan.maintenance') }}" method="GET" class="row g-3 align-items-end">
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
                    <label for="status_filter" class="form-label">Status</label>
                    <select class="form-select form-select-sm" id="status_filter" name="status">
                        <option value="">Semua Status</option>
                        <option value="Dijadwalkan" {{ $filterStatus == 'Dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                        <option value="Selesai" {{ $filterStatus == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Dibatalkan" {{ $filterStatus == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
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
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Panel Ringkasan --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Ringkasan Biaya (berdasarkan filter)</h5>
        </div>
        <div class="card-body">
            <h3 class="fw-bold">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</h3>
            <p class="text-muted mb-0">Total biaya dari {{ $maintenances->total() }} catatan maintenance.</p>
        </div>
    </div>

    {{-- GRAFIK MAINTENANCE --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Status Maintenance</h5>
                    <canvas id="chartMaintenanceStatus" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Biaya Maintenance per Bulan</h5>
                    <canvas id="chartBiayaMaintenance" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    {{-- AKHIR GRAFIK MAINTENANCE --}}

    {{-- Tabel Data --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between">
             <h5 class="mb-0">Detail Catatan Maintenance</h5>
             {{-- Tombol Ekspor bisa ditambahkan di sini nanti --}}
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Perbaikan</th>
                            <th>Barang Terkait</th>
                            <th class="text-end">Biaya</th>
                            <th>Status</th>
                            <th>Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($maintenances as $key => $item)
                            <tr>
                                <td>{{ $maintenances->firstItem() + $key }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal_maintenance)->isoFormat('DD MMM YYYY') }}</td>
                                <td>
                                    <a href="{{ route('admin.maintenances.show', $item->id) }}">{{ $item->nama_perbaikan }}</a>
                                </td>
                                <td>{{ $item->barang->nama_barang ?? 'Umum/Lainnya' }}</td>
                                <td class="text-end">Rp {{ number_format($item->biaya, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge 
                                        @if($item->status == 'Selesai') bg-success 
                                        @elseif($item->status == 'Dijadwalkan') bg-warning text-dark
                                        @else bg-secondary @endif">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td>{{ $item->pencatat->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data maintenance yang sesuai dengan filter Anda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($maintenances->hasPages())
        <div class="card-footer bg-white d-flex justify-content-center">
            {{ $maintenances->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grafik Status Maintenance
    const ctxStatus = document.getElementById('chartMaintenanceStatus').getContext('2d');
    const statusData = @json($maintenancePerStatus);
    
    const chartStatus = new Chart(ctxStatus, {
        type: 'pie',
        data: {
            labels: statusData.map(item => item.status),
            datasets: [{
                label: 'Jumlah',
                data: statusData.map(item => item.jumlah),
                backgroundColor: [
                    '#28a745', // Selesai - hijau
                    '#ffc107', // Dijadwalkan - kuning
                    '#6c757d'  // Dibatalkan - abu
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Grafik Biaya Maintenance per Bulan
    const ctxBiaya = document.getElementById('chartBiayaMaintenance').getContext('2d');
    const biayaData = @json($biayaMaintenancePerBulan);
    
    const labels = biayaData.map(item => {
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return monthNames[item.bulan - 1] + ' ' + item.tahun;
    });
    const data = biayaData.map(item => item.total_biaya);

    const chartBiaya = new Chart(ctxBiaya, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Biaya (Rp)',
                data: data,
                backgroundColor: 'rgba(255, 159, 64, 0.8)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Total Biaya: Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection