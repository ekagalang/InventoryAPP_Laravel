@extends('layouts.app')

@section('title', 'Laporan Stok Barang Saat Ini')

@section('content')
<div class="container-fluid"> {{-- Gunakan container-fluid untuk layout laporan yang lebih lebar --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Laporan Stok Barang Saat Ini</h1>
        {{-- Tombol Aksi (misal: Print, Export - nanti) --}}
        {{-- <div>
            <button class="btn btn-secondary" onclick="window.print();"><i class="bi bi-printer"></i> Cetak</button>
        </div> --}}
    </div>

    {{-- FORM FILTER --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Filter Laporan</h5>
            <form action="{{ route('laporan.stok.barang') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="search_filter" class="form-label">Cari Barang (Nama/Kode)</label>
                    <input type="text" class="form-control form-control-sm" id="search_filter" name="search" value="{{ $searchTerm }}">
                </div>
                <div class="col-md-3">
                    <label for="kategori_id_filter" class="form-label">Kategori</label>
                    <select class="form-select form-select-sm" id="kategori_id_filter" name="kategori_id">
                        <option value="">-- Semua Kategori --</option>
                        @foreach ($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" {{ $filterKategoriId == $kategori->id ? 'selected' : '' }}>
                                {{ $kategori->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="lokasi_id_filter" class="form-label">Lokasi</label>
                    <select class="form-select form-select-sm" id="lokasi_id_filter" name="lokasi_id">
                        <option value="">-- Semua Lokasi --</option>
                        @foreach ($lokasis as $lokasi)
                            <option value="{{ $lokasi->id }}" {{ $filterLokasiId == $lokasi->id ? 'selected' : '' }}>
                                {{ $lokasi->nama_lokasi }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm me-2">Filter</button>
                    <a href="{{ route('laporan.stok.barang') }}" class="btn btn-secondary btn-sm me-2">Reset</a>
                    {{-- <a href="{{ route('laporan.stok.barang.export', request()->query()) }}" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel"></i> Ekspor</a> --}}
                </div>
            </form>
        </div>
    </div>
    {{-- AKHIR FORM FILTER --}}

    {{-- GRAFIK STOK --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Distribusi Stok per Kategori</h5>
                    <div class="chart-container">
                        <canvas id="chartStokKategori"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Distribusi Stok per Lokasi</h5>
                    <div class="chart-container">
                        <canvas id="chartStokLokasi"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- AKHIR GRAFIK STOK --}}

    <div class="card shadow-sm">
        <div class="card-body">
            @if($barangs->isEmpty())
                <div class="alert alert-info text-center">
                    Tidak ada data barang yang sesuai dengan filter Anda, atau belum ada data barang sama sekali.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped table-sm"> {{-- table-sm untuk tabel yang lebih ringkas --}}
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Unit</th>
                                <th>Lokasi</th>
                                <th class="text-end">Stok Saat Ini</th>
                                {{-- Opsional: Kolom Nilai
                                <th class="text-end">Harga Beli</th>
                                <th class="text-end">Total Nilai Stok</th>
                                --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($barangs as $key => $barang)
                                <tr>
                                    <td>{{ $barangs->firstItem() + $key }}</td>
                                    <td>{{ $barang->kode_barang ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('barang.show', $barang->id) }}">{{ $barang->nama_barang }}</a>
                                    </td>
                                    <td>{{ $barang->kategori->nama_kategori ?? '-' }}</td>
                                    <td>{{ $barang->unit->singkatan_unit ?? $barang->unit->nama_unit ?? '-' }}</td>
                                    <td>{{ $barang->lokasi->nama_lokasi ?? '-' }}</td>
                                    <td class="text-end fw-bold">{{ number_format($barang->stok, 0, ',', '.') }}</td>
                                    {{-- Opsional: Kolom Nilai
                                    <td class="text-end">Rp {{ number_format($barang->harga_beli ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format(($barang->stok ?? 0) * ($barang->harga_beli ?? 0), 0, ',', '.') }}</td>
                                    --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($barangs->hasPages())
        <div class="card-footer">
            {{ $barangs->links() }}
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
        max-height: 300px;
        overflow: hidden;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grafik Stok per Kategori
    const ctxKategori = document.getElementById('chartStokKategori').getContext('2d');
    const chartStokKategori = new Chart(ctxKategori, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($stokPerKategori->pluck('nama_kategori')) !!},
            datasets: [{
                label: 'Stok',
                data: {!! json_encode($stokPerKategori->pluck('total_stok')) !!},
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
                    '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384', '#36A2EB', '#FFCE56'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });

    // Grafik Stok per Lokasi
    const ctxLokasi = document.getElementById('chartStokLokasi').getContext('2d');
    const chartStokLokasi = new Chart(ctxLokasi, {
        type: 'bar',
        data: {
            labels: {!! json_encode($stokPerLokasi->pluck('nama_lokasi')) !!},
            datasets: [{
                label: 'Total Stok',
                data: {!! json_encode($stokPerLokasi->pluck('total_stok')) !!},
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
                        font: {
                            size: 11
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 11
                        },
                        maxRotation: 45
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endpush
@endsection