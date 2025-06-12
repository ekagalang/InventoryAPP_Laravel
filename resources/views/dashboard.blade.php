@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>

    {{-- Baris untuk Kartu Statistik Utama --}}
    <div class="row">
        {{-- Kartu Total Barang --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Jenis Barang</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalBarang ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-box-seam-fill fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kartu Pengajuan Baru (Hanya untuk Approver) --}}
        @can('pengajuan-barang-approve')
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pengajuan Baru (Menunggu)</div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $pengajuanDiajukan ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-journal-arrow-down fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Kartu Disetujui Menunggu Proses (Hanya untuk Approver/Pemroses) --}}
        @can('pengajuan-barang-process')
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-start-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">Disetujui (Menunggu Proses)</div>
                                <div class="h5 mb-0 fw-bold text-gray-800">{{ $pengajuanDisetujui ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-journal-check fs-2 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        {{-- Kartu Stok Kritis --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Barang Stok Kritis</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">{{ $barangStokKritis->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle-fill fs-2 text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Baris untuk Grafik dan Panel Aksi --}}
    <div class="row">
        {{-- Kolom Kiri: Grafik & Pengajuan Terbaru --}}
        <div class="col-xl-8 col-lg-7">
            {{-- Grafik Pergerakan Stok --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Pergerakan Stok (6 Bulan Terakhir)</h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height:320px">
                        <canvas id="pergerakanStokChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Panel Pengajuan Terbaru (Hanya untuk Admin/Staf) --}}
            @can('pengajuan-barang-approve')
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Pengajuan Terbaru (Menunggu Persetujuan)</h6>
                    <a href="{{ route('admin.pengajuan.barang.index', ['status_filter' => 'Diajukan']) }}">Lihat Semua &rarr;</a>
                </div>
                <div class="card-body p-0">
                    @if($pengajuanMenunggu && $pengajuanMenunggu->isNotEmpty())
                        <div class="list-group list-group-flush">
                            @foreach($pengajuanMenunggu as $request)
                                <a href="{{ route('admin.pengajuan.barang.show', $request->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $request->barang->nama_barang ?? 'N/A' }} (Qty: {{ $request->kuantitas_diminta }})</h6>
                                        <small>{{ $request->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1 small text-muted">Diajukan oleh: {{ $request->pemohon->name ?? 'N/A' }}</p>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-muted my-4">Tidak ada pengajuan baru.</p>
                    @endif
                </div>
            </div>
            @endcan
        </div>

        {{-- Kolom Kanan: Donut Chart & Stok Kritis --}}
        <div class="col-xl-4 col-lg-5">
            {{-- Grafik Status Barang --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Komposisi Status Barang</h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height:250px">
                        <canvas id="statusDonutChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Panel Stok Kritis --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-danger">Barang dengan Stok Kritis</h6>
                    <a href="{{ route('laporan.stok.barang') }}">Lihat Laporan &rarr;</a>
                </div>
                <div class="card-body p-0">
                     @if($barangStokKritis && $barangStokKritis->isNotEmpty())
                        <div class="list-group list-group-flush">
                            @foreach($barangStokKritis as $barang)
                                <a href="{{ route('barang.show', $barang->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <span class="fw-bold">{{ $barang->nama_barang }}</span>
                                        <span class="text-danger">Stok: {{ $barang->stok }}</span>
                                    </div>
                                    <small class="text-muted">Stok Min: {{ $barang->stok_minimum }}</small>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-muted my-4">Tidak ada barang dengan stok kritis. Bagus!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- CSS Kustom untuk Kartu Statistik --}}
<style>
    .card .border-start-primary { border-left: 0.25rem solid #4e73df !important; }
    .card .border-start-success { border-left: 0.25rem solid #1cc88a !important; }
    .card .border-start-info { border-left: 0.25rem solid #36b9cc !important; }
    .card .border-start-warning { border-left: 0.25rem solid #f6c23e !important; }
    .card .border-start-danger { border-left: 0.25rem solid #e74a3b !important; }
    .text-gray-300 { color: #dddfeb !important; }
    .text-gray-800 { color: #5a5c69 !important; }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Data dari Controller
        const barangByStatusData = @json($barangByStatus ?? []);
        const pergerakanStokData = @json($pergerakanStok ?? []);

        // 1. Grafik Donat untuk Status Barang
        const statusDonutCtx = document.getElementById('statusDonutChart');
        if (statusDonutCtx && Object.keys(barangByStatusData).length > 0) {
            const statusLabels = Object.keys(barangByStatusData);
            const statusData = Object.values(barangByStatusData);
            new Chart(statusDonutCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
                    datasets: [{
                        data: statusData,
                        backgroundColor: [
                            'rgba(25, 135, 84, 0.7)',  // success (aktif)
                            'rgba(255, 193, 7, 0.7)',  // warning (rusak)
                            'rgba(220, 53, 69, 0.7)',  // danger (hilang)
                            'rgba(108, 117, 125, 0.7)', // secondary (dipinjam)
                        ],
                        borderWidth: 1
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // 2. Grafik Batang untuk Pergerakan Stok
        const pergerakanStokCtx = document.getElementById('pergerakanStokChart');
        if (pergerakanStokCtx && pergerakanStokData.length > 0) {
            const bulanLabels = pergerakanStokData.map(item => new Date(item.bulan + '-02').toLocaleString('id-ID', { month: 'long', year: 'numeric' }));
            const dataMasuk = pergerakanStokData.map(item => item.total_masuk);
            const dataKeluar = pergerakanStokData.map(item => item.total_keluar);

            new Chart(pergerakanStokCtx, {
                type: 'bar',
                data: {
                    labels: bulanLabels,
                    datasets: [
                        {
                            label: 'Barang Masuk',
                            data: dataMasuk,
                            backgroundColor: 'rgba(25, 135, 84, 0.6)',
                        },
                        {
                            label: 'Barang Keluar',
                            data: dataKeluar,
                            backgroundColor: 'rgba(220, 53, 69, 0.6)',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }
    });
</script>
@endpush