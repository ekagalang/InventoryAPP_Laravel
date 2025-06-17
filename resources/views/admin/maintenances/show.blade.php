@extends('layouts.app')

@section('title', 'Detail Maintenance - ' . $maintenance->nama_perbaikan)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Detail Jadwal Maintenance</h1>
        <div>
            <a href="{{ route('admin.maintenances.index') }}" class="btn btn-secondary btn-sm">Kembali ke Daftar</a>
            @can('maintenance-manage')
                <a href="{{ route('admin.maintenances.edit', $maintenance->id) }}" class="btn btn-warning btn-sm">Edit Jadwal</a>
            @endcan
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Informasi #{{ $maintenance->id }}: {{ $maintenance->nama_perbaikan }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h6>Deskripsi</h6>
                    <p>{{ $maintenance->deskripsi ?? '-' }}</p>
                </div>
                <div class="col-md-4">
                    <h6>Detail</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <strong>Status:</strong> 
                            <span class="badge 
                                @if($maintenance->status == 'Selesai') bg-success 
                                @elseif($maintenance->status == 'Dijadwalkan') bg-warning text-dark
                                @else bg-danger @endif">
                                {{ $maintenance->status }}
                            </span>
                        </li>
                        <li class="list-group-item px-0">
                            <strong>Tanggal:</strong> 
                            {{ \Carbon\Carbon::parse($maintenance->tanggal_maintenance)->isoFormat('dddd, DD MMMM YYYY') }}
                        </li>
                        <li class="list-group-item px-0">
                            <strong>Biaya:</strong> 
                            Rp {{ number_format($maintenance->biaya, 0, ',', '.') }}
                        </li>
                        <li class="list-group-item px-0">
                            <strong>Barang Terkait:</strong> 
                            {{ $maintenance->barang->nama_barang ?? 'Umum/Lainnya' }}
                        </li>
                         <li class="list-group-item px-0">
                            <strong>Dicatat oleh:</strong> 
                            {{ $maintenance->pencatat->name ?? 'N/A' }}
                        </li>
                         <li class="list-group-item px-0">
                            <strong>Lampiran:</strong>
                            @if($maintenance->lampiran)
                                <a href="{{ asset('storage/' . $maintenance->lampiran) }}" target="_blank">Lihat Lampiran</a>
                            @else
                                -
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
