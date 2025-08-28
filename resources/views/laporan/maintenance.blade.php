@extends('layouts.app')

@section('title', 'Laporan Maintenance & Pembayaran')

@section('content')
<div class="container-fluid">
    <h1>Laporan Maintenance & Pembayaran Rutin</h1>

    {{-- Panel Filter --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Filter Laporan</h5>
            <form action="{{ route('laporan.maintenance') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label for="jenis_filter" class="form-label">Jenis</label>
                    <select class="form-select form-select-sm" id="jenis_filter" name="jenis">
                        <option value="semua" {{ $filterJenis == 'semua' ? 'selected' : '' }}>Semua</option>
                        <option value="maintenance" {{ $filterJenis == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="pembayaran" {{ $filterJenis == 'pembayaran' ? 'selected' : '' }}>Pembayaran</option>
                    </select>
                </div>
                <div class="col-md-2">
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
                    <label for="kategori_pembayaran_filter" class="form-label">Kategori Bayar</label>
                    <select class="form-select form-select-sm" id="kategori_pembayaran_filter" name="kategori_pembayaran">
                        <option value="">Semua Kategori</option>
                        <option value="platform" {{ $filterKategoriPembayaran == 'platform' ? 'selected' : '' }}>Platform</option>
                        <option value="utilitas" {{ $filterKategoriPembayaran == 'utilitas' ? 'selected' : '' }}>Utilitas (IPL)</option>
                        <option value="asuransi" {{ $filterKategoriPembayaran == 'asuransi' ? 'selected' : '' }}>Asuransi</option>
                        <option value="sewa" {{ $filterKategoriPembayaran == 'sewa' ? 'selected' : '' }}>Sewa</option>
                        <option value="berlangganan" {{ $filterKategoriPembayaran == 'berlangganan' ? 'selected' : '' }}>Berlangganan</option>
                        <option value="lainnya" {{ $filterKategoriPembayaran == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status_filter" class="form-label">Status</label>
                    <select class="form-select form-select-sm" id="status_filter" name="status">
                        <option value="">Semua Status</option>
                        <option value="Dijadwalkan" {{ $filterStatus == 'Dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                        <option value="Selesai" {{ $filterStatus == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Dibatalkan" {{ $filterStatus == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        <option value="aktif" {{ $filterStatus == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ $filterStatus == 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
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

    {{-- Panel Ringkasan Terpadu --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-check-circle"></i> Total Terealisasi</h6>
                </div>
                <div class="card-body">
                    <h3 class="fw-bold text-success mb-3">Rp {{ number_format($grandTotalTerealisasi, 0, ',', '.') }}</h3>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Maintenance Selesai:</small><br>
                            <strong>Rp {{ number_format($totalMaintenanceTerealisasi, 0, ',', '.') }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Maintenance Berulang:</small><br>
                            <strong>Rp {{ number_format($totalMaintenanceScheduleTerealisasi, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <small class="text-muted">Pembayaran Rutin:</small><br>
                            <strong>Rp {{ number_format($totalPaymentTerealisasi, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bi bi-clock"></i> Total Akan Datang</h6>
                </div>
                <div class="card-body">
                    <h3 class="fw-bold text-warning mb-3">Rp {{ number_format($grandTotalWillCome, 0, ',', '.') }}</h3>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Maintenance Terjadwal:</small><br>
                            <strong>Rp {{ number_format($totalMaintenanceWillCome, 0, ',', '.') }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Maintenance Berulang:</small><br>
                            <strong>Rp {{ number_format($totalMaintenanceScheduleWillCome, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <small class="text-muted">Pembayaran Rutin:</small><br>
                            <strong>Rp {{ number_format($totalPaymentWillCome, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Total Keseluruhan --}}
    <div class="card shadow-sm mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-calculator"></i> Total Keseluruhan</h5>
        </div>
        <div class="card-body text-center">
            <h2 class="fw-bold text-primary">Rp {{ number_format($grandTotalTerealisasi + $grandTotalWillCome, 0, ',', '.') }}</h2>
            <p class="text-muted mb-0">Kombinasi total terealisasi dan akan datang dari Maintenance & Pembayaran Rutin</p>
        </div>
    </div>

    {{-- GRAFIK TERPADU --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Status Maintenance</h6>
                    <div class="chart-container" style="height: 200px;">
                        <canvas id="chartMaintenanceStatus"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Kategori Pembayaran</h6>
                    <div class="chart-container" style="height: 200px;">
                        <canvas id="chartPaymentKategori"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Biaya Gabungan (12 Bulan)</h6>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="chartBiayaGabungan"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- AKHIR GRAFIK TERPADU --}}

    {{-- Navigation Tabs --}}
    <div class="card shadow-sm">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $filterJenis !== 'pembayaran' ? 'active' : '' }}" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance-content" type="button" role="tab">
                        <i class="bi bi-tools"></i> Maintenance ({{ $maintenances->total() ?? 0 }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $filterJenis === 'pembayaran' ? 'active' : '' }}" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment-content" type="button" role="tab">
                        <i class="bi bi-credit-card"></i> Pembayaran Rutin ({{ $payments->total() ?? 0 }})
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="reportTabsContent">
                {{-- Tab Maintenance --}}
                <div class="tab-pane fade {{ $filterJenis !== 'pembayaran' ? 'show active' : '' }}" id="maintenance-content" role="tabpanel">
                    @if($maintenances->isEmpty())
                        <div class="alert alert-info text-center">
                            Tidak ada data maintenance yang sesuai dengan filter.
                        </div>
                    @else
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
                            <div class="d-flex justify-content-center mt-3">
                                {{ $maintenances->links() }}
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Tab Pembayaran Rutin --}}
                <div class="tab-pane fade {{ $filterJenis === 'pembayaran' ? 'show active' : '' }}" id="payment-content" role="tabpanel">
                    @if($payments->isEmpty())
                        <div class="alert alert-info text-center">
                            Tidak ada data pembayaran rutin yang sesuai dengan filter.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Nama Pembayaran</th>
                                        <th>Kategori</th>
                                        <th>Penerima/Vendor</th>
                                        <th class="text-end">Nominal</th>
                                        <th>Status</th>
                                        <th>Dicatat Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payments as $key => $item)
                                        <tr>
                                            <td>{{ $payments->firstItem() + $key }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->tanggal_mulai)->isoFormat('DD MMM YYYY') }}</td>
                                            <td>
                                                <a href="{{ route('admin.payments.show', $item->id) }}">{{ $item->nama_pembayaran }}</a>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    @if($item->kategori == 'platform') bg-info
                                                    @elseif($item->kategori == 'utilitas') bg-warning text-dark
                                                    @elseif($item->kategori == 'asuransi') bg-success
                                                    @elseif($item->kategori == 'sewa') bg-primary
                                                    @elseif($item->kategori == 'berlangganan') bg-secondary
                                                    @else bg-dark @endif">
                                                    {{ $item->kategori_label }}
                                                </span>
                                            </td>
                                            <td>{{ $item->penerima ?? '-' }}</td>
                                            <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="badge 
                                                    @if($item->status == 'aktif') bg-success 
                                                    @elseif($item->status == 'nonaktif') bg-secondary
                                                    @else bg-danger @endif">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $item->user->name ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">Tidak ada data pembayaran rutin yang sesuai dengan filter Anda.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($payments->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $payments->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
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

    // Grafik Kategori Pembayaran
    const ctxPayment = document.getElementById('chartPaymentKategori').getContext('2d');
    const paymentData = @json($paymentPerKategori);
    
    const chartPayment = new Chart(ctxPayment, {
        type: 'doughnut',
        data: {
            labels: paymentData.map(item => {
                const labels = {
                    'platform': 'Platform',
                    'utilitas': 'Utilitas (IPL)',
                    'asuransi': 'Asuransi',
                    'sewa': 'Sewa',
                    'berlangganan': 'Berlangganan',
                    'lainnya': 'Lainnya'
                };
                return labels[item.kategori] || item.kategori;
            }),
            datasets: [{
                label: 'Jumlah',
                data: paymentData.map(item => item.jumlah),
                backgroundColor: [
                    '#17a2b8', // platform - info
                    '#ffc107', // utilitas - warning
                    '#28a745', // asuransi - success
                    '#007bff', // sewa - primary
                    '#6c757d', // berlangganan - secondary
                    '#343a40'  // lainnya - dark
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

    // Grafik Biaya Gabungan per Bulan
    const ctxBiaya = document.getElementById('chartBiayaGabungan').getContext('2d');
    const biayaGabungan = @json($biayaGabunganPerBulan);
    
    const labelsGabungan = biayaGabungan.map(item => {
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return monthNames[item.bulan - 1] + ' ' + item.tahun;
    });

    const chartBiaya = new Chart(ctxBiaya, {
        type: 'bar',
        data: {
            labels: labelsGabungan,
            datasets: [{
                label: 'Maintenance (Rp)',
                data: biayaGabungan.map(item => item.total_maintenance),
                backgroundColor: 'rgba(255, 159, 64, 0.8)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }, {
                label: 'Pembayaran (Rp)',
                data: biayaGabungan.map(item => item.total_pembayaran),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
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
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
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