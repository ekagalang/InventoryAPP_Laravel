@extends('layouts.app')

@section('title', 'Detail Pengajuan Barang No. ' . $itemRequest->id)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Detail Pengajuan Barang #{{ $itemRequest->id }}</h1>
        <a href="{{ route('admin.pengajuan.barang.index') }}" class="btn btn-secondary">Kembali ke Daftar Pengajuan</a>
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

    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Informasi Pengajuan</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Pemohon:</strong> {{ $itemRequest->pemohon->name ?? 'N/A' }}</p>
                    <p><strong>Email Pemohon:</strong> {{ $itemRequest->pemohon->email ?? 'N/A' }}</p>
                    <p><strong>Tanggal Pengajuan:</strong> {{ $itemRequest->created_at->isoFormat('DD MMMM YYYY, HH:mm') }}</p>
                    <p><strong>Status Saat Ini:</strong> 
                        @if($itemRequest->status == 'Diajukan')
                            <span class="badge bg-warning text-dark">{{ $itemRequest->status }}</span>
                        @elseif($itemRequest->status == 'Disetujui')
                            <span class="badge bg-success">{{ $itemRequest->status }}</span>
                        @elseif($itemRequest->status == 'Ditolak')
                            <span class="badge bg-danger">{{ $itemRequest->status }}</span>
                        @elseif($itemRequest->status == 'Diproses')
                            <span class="badge bg-info">{{ $itemRequest->status }}</span>
                        @elseif($itemRequest->status == 'Dibatalkan')
                            <span class="badge bg-secondary">{{ $itemRequest->status }}</span>
                        @else
                            <span class="badge bg-light text-dark">{{ $itemRequest->status }}</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Barang Diminta:</strong> {{ $itemRequest->barang->nama_barang ?? 'N/A' }} (Kode: {{ $itemRequest->barang->kode_barang ?? 'N/A' }})</p>
                    <p><strong>Stok Tersedia Saat Ini:</strong> {{ $barangTerkait->stok ?? 'N/A' }} {{ $itemRequest->barang->unit->singkatan_unit ?? $itemRequest->barang->unit->nama_unit ?? '' }}</p>
                    <p><strong>Kuantitas Diminta:</strong> {{ number_format($itemRequest->kuantitas_diminta, 0, ',', '.') }} {{ $itemRequest->barang->unit->singkatan_unit ?? $itemRequest->barang->unit->nama_unit ?? '' }}</p>
                    @if($itemRequest->kuantitas_disetujui !== null)
                    <p><strong>Kuantitas Disetujui:</strong> <span class="fw-bold">{{ number_format($itemRequest->kuantitas_disetujui, 0, ',', '.') }}</span> {{ $itemRequest->barang->unit->singkatan_unit ?? $itemRequest->barang->unit->nama_unit ?? '' }}</p>
                    @endif
                    <p><strong>Tanggal Dibutuhkan:</strong> {{ $itemRequest->tanggal_dibutuhkan ? \Carbon\Carbon::parse($itemRequest->tanggal_dibutuhkan)->isoFormat('DD MMMM YYYY') : '-' }}</p>
                    <p><strong>Keperluan:</strong> {{ $itemRequest->keperluan ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    @if ($itemRequest->status == 'Diajukan' && Auth::user()->hasPermissionTo('pengajuan-barang-approve'))
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">Aksi Persetujuan</h5>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- Form Approve --}}
                <div class="col-md-6 border-end">
                    <h6>Setujui Pengajuan</h6>
                    <form action="{{ route('admin.pengajuan.barang.approve', $itemRequest->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="kuantitas_disetujui" class="form-label">Kuantitas Disetujui <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('kuantitas_disetujui') is-invalid @enderror" id="kuantitas_disetujui" name="kuantitas_disetujui" value="{{ old('kuantitas_disetujui', $itemRequest->kuantitas_diminta) }}" min="1" max="{{ $itemRequest->kuantitas_diminta }}" required>
                            <small class="form-text text-muted">Stok barang saat ini: {{ $barangTerkait->stok ?? 'N/A' }}. Maksimal bisa disetujui: {{ $itemRequest->kuantitas_diminta }}.</small>
                            @error('kuantitas_disetujui')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="catatan_approval_approve" class="form-label">Catatan Persetujuan (Opsional)</label>
                            <textarea class="form-control @error('catatan_approval') is-invalid @enderror" id="catatan_approval_approve" name="catatan_approval" rows="2">{{ old('catatan_approval') }}</textarea>
                            @error('catatan_approval')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-circle-fill"></i> Setujui</button>
                    </form>
                </div>

                {{-- Form Reject --}}
                <div class="col-md-6">
                    <h6>Tolak Pengajuan</h6>
                    <form action="{{ route('admin.pengajuan.barang.reject', $itemRequest->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="catatan_approval_reject" class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('catatan_approval', 'rejectForm') is-invalid @enderror" id="catatan_approval_reject" name="catatan_approval" rows="3" required>{{ old('catatan_approval') }}</textarea>
                            @error('catatan_approval', 'rejectForm') {{-- Menggunakan error bag berbeda jika perlu --}}
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menolak pengajuan ini?');"><i class="bi bi-x-circle-fill"></i> Tolak</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if ($itemRequest->status == 'Diproses' && Auth::user()->hasPermissionTo('pengajuan-barang-return'))
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Aksi Pengembalian Barang</h5>
            </div>
            <div class="card-body">
                <p>Catat pengembalian untuk barang ini. Stok akan dikembalikan sejumlah kuantitas yang disetujui sebelumnya.</p>
                <form action="{{ route('admin.pengajuan.barang.return', $itemRequest->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin mencatat pengembalian untuk barang ini? Stok akan dikembalikan ke sistem.');">
                    @csrf
                    @method('PUT') {{-- Menggunakan PUT untuk update status --}}

                    <div class="mb-3">
                        <p class="mb-1"><strong>Barang:</strong> {{ $itemRequest->barang->nama_barang ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Kuantitas yang akan dikembalikan:</strong> <span class="fw-bold">{{ number_format($itemRequest->kuantitas_disetujui, 0, ',', '.') }}</span> {{ $itemRequest->barang->unit->singkatan_unit ?? $itemRequest->barang->unit->nama_unit ?? '' }}</p>
                    </div>

                    <div class="mb-3">
                        <label for="catatan_pengembalian" class="form-label">Catatan Pengembalian (Opsional, misal: kondisi barang)</label>
                        <textarea class="form-control @error('catatan_pengembalian') is-invalid @enderror" id="catatan_pengembalian" name="catatan_pengembalian" rows="2">{{ old('catatan_pengembalian') }}</textarea>
                        @error('catatan_pengembalian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-in-down"></i> Catat Pengembalian</button>
                </form>
            </div>
        </div>
    @endif


    {{-- Blok untuk menampilkan Detail Keputusan & Pemrosesan --}}
    @if ($itemRequest->status == 'Disetujui' || $itemRequest->status == 'Ditolak' || $itemRequest->status == 'Diproses' || $itemRequest->status == 'Dibatalkan' || $itemRequest->status == 'Dikembalikan')
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Detail Keputusan & Pemrosesan</h5>
            </div>
            <div class="card-body">
                {{-- ... (kode detail approval dan proses yang sudah ada) ... --}}

                {{-- TAMBAHKAN INI UNTUK MENAMPILKAN INFO PENGEMBALIAN --}}
                @if ($itemRequest->status == 'Dikembalikan')
                <p><strong>Diterima Kembali oleh:</strong> {{ $itemRequest->penerimaPengembalian->name ?? 'N/A' }}</p>
                <p><strong>Tanggal Dikembalikan:</strong> {{ $itemRequest->returned_at ? $itemRequest->returned_at->isoFormat('DD MMMM YYYY, HH:mm') : '-' }}</p>
                <p><strong>Catatan Pengembalian:</strong> {{ $itemRequest->catatan_pengembalian ?? '-' }}</p>
                @endif
            </div>
        </div>
    @endif

    @if ($itemRequest->status == 'Disetujui' && Auth::user()->hasPermissionTo('pengajuan-barang-process'))
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Proses Pengeluaran Barang</h5>
            </div>
            <div class="card-body">
                <p>Pengajuan ini telah disetujui. Klik tombol di bawah untuk memproses pengeluaran barang dari stok.</p>
                <p><strong>Pemohon:</strong> {{ $itemRequest->pemohon->name ?? 'N/A' }}</p>
                <p><strong>Barang:</strong> {{ $itemRequest->barang->nama_barang ?? 'N/A' }}</p>
                <p><strong>Kuantitas Disetujui:</strong> <span class="fw-bold">{{ number_format($itemRequest->kuantitas_disetujui, 0, ',', '.') }}</span> {{ $itemRequest->barang->unit->singkatan_unit ?? $itemRequest->barang->unit->nama_unit ?? '' }}</p>
                <p class="text-danger small">Pastikan stok barang tersedia (Stok Saat Ini: {{ $barangTerkait->stok ?? 'N/A' }}) sebelum memproses.</p>

                <form action="{{ route('admin.pengajuan.barang.process', $itemRequest->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin memproses pengajuan ini dan mengeluarkan barang dari stok? Aksi ini akan mengurangi stok barang.');">
                    @csrf
                    <div class="mb-3">
                        <label for="catatan_pemroses" class="form-label">Catatan Pemroses (Opsional)</label>
                        <textarea class="form-control @error('catatan_pemroses') is-invalid @enderror" id="catatan_pemroses" name="catatan_pemroses" rows="2">{{ old('catatan_pemroses') }}</textarea>
                        @error('catatan_pemroses')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-info"><i class="bi bi-box-arrow-up-right"></i> Proses & Keluarkan Barang</button>
                </form>
            </div>
        </div>
    @endif

    {{-- Blok untuk menampilkan Detail Keputusan & Pemrosesan (jika statusnya bukan 'Diajukan') --}}
    @if ($itemRequest->status == 'Disetujui' || $itemRequest->status == 'Ditolak' || $itemRequest->status == 'Diproses' || $itemRequest->status == 'Dibatalkan')
        <div class="card shadow-sm">
            {{-- ... (konten detail keputusan yang sudah ada sebelumnya) ... --}}
            {{-- Pastikan bagian ini menampilkan info jika sudah diproses --}}
            @if ($itemRequest->status == 'Diproses' && $itemRequest->processed_by)
            <hr>
            <p><strong>Diproses oleh:</strong> {{ $itemRequest->pemroses->name ?? 'N/A' }}</p>
            <p><strong>Tanggal Diproses:</strong> {{ $itemRequest->processed_at ? $itemRequest->processed_at->isoFormat('DD MMMM YYYY, HH:mm') : '-' }}</p>
            <p><strong>Catatan Pemroses:</strong> {{ $itemRequest->catatan_pemroses ?? '-' }}</p>
            @endif
        </div>
    @endif

    @if ($itemRequest->status == 'Disetujui' || $itemRequest->status == 'Ditolak' || $itemRequest->status == 'Diproses' || $itemRequest->status == 'Dibatalkan')
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Detail Keputusan & Pemrosesan</h5>
        </div>
        <div class="card-body">
            @if ($itemRequest->approved_by)
            <p><strong>Ditindaklanjuti oleh:</strong> {{ $itemRequest->approver->name ?? 'N/A' }}</p>
            <p><strong>Tanggal Keputusan:</strong> {{ $itemRequest->approved_at ? $itemRequest->approved_at->isoFormat('DD MMMM YYYY, HH:mm') : '-' }}</p>
            <p><strong>Catatan Approval/Penolakan:</strong> {{ $itemRequest->catatan_approval ?? '-' }}</p>
            @endif

            @if ($itemRequest->status == 'Diproses' && $itemRequest->tipe_pengajuan == 'peminjaman' && Auth::user()->hasPermissionTo('pengajuan-barang-return'))
            <hr>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Aksi Pengembalian Aset</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.pengajuan.barang.return', $itemRequest->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin mencatat pengembalian untuk aset ini?');">
                        @csrf
                        @method('PUT')

                        <p><strong>Diproses oleh:</strong> {{ $itemRequest->pemroses->name ?? 'N/A' }}</p>
                        <p><strong>Tanggal Diproses:</strong> {{ $itemRequest->processed_at ? $itemRequest->processed_at->isoFormat('DD MMMM YYYY, HH:mm') : '-' }}</p>
                        <p><strong>Catatan Pemroses:</strong> {{ $itemRequest->catatan_pemroses ?? '-' }}</p>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-in-down"></i> Catat Pengembalian</button>
                </form>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection