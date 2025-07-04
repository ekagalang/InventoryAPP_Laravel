@extends('layouts.app')

@section('title', 'Pengajuan Barang Saya')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Pengajuan Barang Saya</h1>
        @can('pengajuan-barang-create')
            <a href="{{ route('pengajuan.barang.pilihTipe') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Buat Pengajuan Baru</a>
        @endcan
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($itemRequests->isEmpty())
                <div class="alert alert-info text-center">
                    Anda belum memiliki pengajuan barang. <br>
                    @can('pengajuan-barang-create')
                        <a href="{{ route('pengajuan.barang.pilihTipe') }}">Buat pengajuan baru sekarang.</a>
                    @endcan
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Tgl. Pengajuan</th>
                                <th class="text-center">Barang Diminta</th>
                                <th class="text-center">Kuantitas</th>
                                <th class="text-center">Keperluan</th>
                                <th class="text-center">Tgl. Dibutuhkan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Catatan Approval</th>
                                <th class="text-center">Aksi</th>
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
                                    <td> {{-- <-- TAMBAHKAN SEL KOLOM AKSI INI --}}
                                        {{-- Tampilkan tombol Batalkan hanya jika status 'Diajukan' DAN user punya izin --}}
                                        @if($request->status == 'Diajukan')
                                            @can('pengajuan-barang-cancel-own')
                                            <form action="{{ route('pengajuan.barang.cancel', $request->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?');">
                                                @csrf
                                                @method('PUT') {{-- Kita gunakan method PUT untuk update status --}}
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Batalkan Pengajuan">
                                                    Batalkan
                                                </button>
                                            </form>
                                            @endcan
                                        @else
                                            - {{-- Tampilkan strip jika tidak ada aksi yang bisa dilakukan --}}
                                        @endif
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