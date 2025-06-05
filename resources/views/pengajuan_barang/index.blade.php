@extends('layouts.app')

@section('title', 'Pengajuan Barang Saya')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Pengajuan Barang Saya</h1>
        @can('pengajuan-barang-create')
            <a href="{{ route('pengajuan.barang.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Buat Pengajuan Baru</a>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            @if($itemRequests->isEmpty())
                <div class="alert alert-info text-center">
                    Anda belum memiliki pengajuan barang. <br>
                    @can('pengajuan-barang-create')
                        <a href="{{ route('pengajuan.barang.create') }}">Buat pengajuan baru sekarang.</a>
                    @endcan
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tgl. Pengajuan</th>
                                <th>Barang Diminta</th>
                                <th>Kuantitas</th>
                                <th>Keperluan</th>
                                <th>Tgl. Dibutuhkan</th>
                                <th>Status</th>
                                <th>Catatan Approval</th>
                                {{-- <th>Aksi</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($itemRequests as $key => $request)
                                <tr>
                                    <td>{{ $itemRequests->firstItem() + $key }}</td>
                                    <td>{{ $request->created_at->isoFormat('DD MMM YYYY, HH:mm') }}</td>
                                    <td>
                                        {{ $request->barang->nama_barang ?? 'N/A' }}
                                        @if($request->barang && $request->barang->kode_barang)
                                            <br><small class="text-muted">{{ $request->barang->kode_barang }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($request->kuantitas_diminta, 0, ',', '.') }} {{ $request->barang->unit->singkatan_unit ?? $request->barang->unit->nama_unit ?? '' }}</td>
                                    <td>{{ Str::limit($request->keperluan, 50) }}</td>
                                    <td>{{ $request->tanggal_dibutuhkan ? \Carbon\Carbon::parse($request->tanggal_dibutuhkan)->isoFormat('DD MMM YYYY') : '-' }}</td>
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
                                    <td>{{ Str::limit($request->catatan_approval, 50) ?? '-' }}</td>
                                    <td>
                                        @if($request->status == 'Diajukan')
                                            @can('pengajuan-barang-cancel-own')
                                            <form action="#" method="POST" onsubmit="return confirm('Batalkan pengajuan ini?');">
                                                @csrf
                                                @method('PUT') {{-- Atau method lain sesuai route cancel --}}
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Batalkan</button>
                                            </form>
                                            @endcan
                                        @endif
                                        <a href="#" class="btn btn-info btn-sm">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($itemRequests->hasPages())
        <div class="card-footer">
            {{ $itemRequests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection