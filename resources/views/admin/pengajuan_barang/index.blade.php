@extends('layouts.app')

@section('title', 'Daftar Semua Pengajuan Barang')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Daftar Semua Pengajuan Barang</h1>
        {{-- Tombol aksi lain bisa ditambahkan di sini jika perlu --}}
    </div>

    {{-- TODO: Tambahkan Form Filter di sini jika diperlukan nanti --}}

    <div class="card shadow-sm">
        <div class="card-body">
            @if($allItemRequests->isEmpty())
                <div class="alert alert-info text-center">
                    Belum ada data pengajuan barang.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tgl. Diajukan</th>
                                <th>Pemohon</th>
                                <th>Barang Diminta</th>
                                <th>Qty Diminta</th>
                                <th>Keperluan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allItemRequests as $key => $request)
                                <tr>
                                    <td>{{ $allItemRequests->firstItem() + $key }}</td>
                                    <td>{{ $request->created_at->isoFormat('DD MMM YY, HH:mm') }}</td>
                                    <td>{{ $request->pemohon->name ?? 'N/A' }}</td>
                                    <td>
                                        {{ $request->barang->nama_barang ?? 'N/A' }}
                                        @if($request->barang && $request->barang->kode_barang)
                                            <br><small class="text-muted">{{ $request->barang->kode_barang }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($request->kuantitas_diminta, 0, ',', '.') }} {{ $request->barang->unit->singkatan_unit ?? $request->barang->unit->nama_unit ?? '' }}</td>
                                    <td>{{ Str::limit($request->keperluan, 50) }}</td>
                                    <td>
                                        @if($request->status == 'Diajukan')
                                            <span class="badge bg-warning text-dark">{{ $request->status }}</span>
                                        @elseif($request->status == 'Disetujui')
                                            <span class="badge bg-success">{{ $request->status }}</span>
                                        @elseif($request->status == 'Ditolak')
                                            <span class="badge bg-danger">{{ $request->status }}</span>
                                        @elseif($request->status == 'Diproses')
                                            <span class="badge bg-info">{{ $request->status }}</span>
                                        @elseif($request->status == 'Dibatalkan')
                                            <span class="badge bg-secondary">{{ $request->status }}</span>
                                        @else
                                            <span class="badge bg-light text-dark">{{ $request->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.pengajuan.barang.show', $request->id) }}" class="btn btn-info btn-sm" title="Lihat Detail & Aksi"><i class="bi bi-eye-fill"></i> Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($allItemRequests->hasPages())
        <div class="card-footer">
            {{ $allItemRequests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection