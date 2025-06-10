@extends('layouts.app')

@section('title', 'Pilih Tipe Pengajuan')

@section('content')
<div class="container text-center">
    <h1 class="mb-4">Buat Pengajuan Baru</h1>
    <p class="lead mb-5">Silakan pilih jenis pengajuan yang ingin Anda buat.</p>

    <div class="row g-4 justify-content-center">
        <div class="col-md-5">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
                    <i class="bi bi-box-seam-fill text-primary" style="font-size: 4rem;"></i>
                    <h4 class="card-title mt-3">Minta Barang Habis Pakai</h4>
                    <p class="card-text text-muted">Untuk barang yang akan digunakan dan tidak dikembalikan, seperti ATK, komponen, dll.</p>
                    <a href="{{ route('pengajuan.barang.create', ['tipe' => 'permintaan']) }}" class="btn btn-primary mt-auto stretched-link">Buat Permintaan</a>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
                    <i class="bi bi-laptop-fill text-success" style="font-size: 4rem;"></i>
                    <h4 class="card-title mt-3">Pinjam Aset</h4>
                    <p class="card-text text-muted">Untuk aset yang akan dipinjam dan harus dikembalikan, seperti laptop, proyektor, dll.</p>
                    <a href="{{ route('pengajuan.barang.create', ['tipe' => 'peminjaman']) }}" class="btn btn-success mt-auto stretched-link">Buat Peminjaman</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection