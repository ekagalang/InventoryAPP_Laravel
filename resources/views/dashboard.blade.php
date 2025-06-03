@extends('layouts.app')

@section('title', 'Dashboard Aplikasi Inventaris')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Selamat Datang di Dashboard, {{ Auth::user()->name }}!</h1>
        {{-- Bisa tambahkan tombol aksi cepat di sini jika perlu --}}
    </div>

    @if (session('status')) {{-- Dari Breeze, untuk notifikasi seperti 'password updated' --}}
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Statistik Data Master --}}
        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam-fill fs-1 mb-2"></i>
                    <h5 class="card-title">{{ $totalBarang }}</h5>
                    <p class="card-text">Total Jenis Barang</p>
                    @can('barang-list')
                    <a href="{{ route('barang.index') }}" class="btn btn-outline-light btn-sm stretched-link">Lihat Detail</a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-info shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-tags-fill fs-1 mb-2"></i>
                    <h5 class="card-title">{{ $totalKategori }}</h5>
                    <p class="card-text">Total Kategori</p>
                    @can('kategori-list')
                    <a href="{{ route('kategori.index') }}" class="btn btn-outline-light btn-sm stretched-link">Lihat Detail</a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-secondary shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-rulers fs-1 mb-2"></i>
                    <h5 class="card-title">{{ $totalUnit }}</h5>
                    <p class="card-text">Total Unit</p>
                    @can('unit-list')
                    <a href="{{ route('unit.index') }}" class="btn btn-outline-light btn-sm stretched-link">Lihat Detail</a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-dark shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-geo-alt-fill fs-1 mb-2"></i>
                    <h5 class="card-title">{{ $totalLokasi }}</h5>
                    <p class="card-text">Total Lokasi</p>
                    @can('lokasi-list')
                    <a href="{{ route('lokasi.index') }}" class="btn btn-outline-light btn-sm stretched-link">Lihat Detail</a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Statistik Pengguna --}}
        @can('user-list') {{-- Hanya tampilkan jika user bisa lihat daftar pengguna --}}
        <div class="col-md-6 col-lg-3">
            <div class="card text-dark bg-light shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill fs-1 mb-2"></i>
                    <h5 class="card-title">{{ $totalPengguna }}</h5>
                    <p class="card-text">Total Pengguna Terdaftar</p>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm stretched-link">Kelola Pengguna</a>
                </div>
            </div>
        </div>
        @endcan

        {{-- Statistik Barang Aktif (Contoh) --}}
        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle-fill fs-1 mb-2"></i>
                    <h5 class="card-title">{{ $barangAktif }}</h5>
                    <p class="card-text">Barang Status Aktif</p>
                    {{-- Link ke daftar barang dengan filter status aktif jika ada --}}
                </div>
            </div>
        </div>


        {{-- Statistik Pengajuan Barang --}}
        @canany(['pengajuan-barang-list-all', 'pengajuan-barang-list-own'])
        <div class="col-md-6 col-lg-3">
            <div class="card text-dark bg-warning shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-journal-arrow-down fs-1 mb-2"></i>
                    <h5 class="card-title">{{ $pengajuanDiajukan }}</h5>
                    <p class="card-text">Pengajuan Baru (Diajukan)</p>
                    @can('pengajuan-barang-list-all')
                    <a href="{{ route('admin.pengajuan.barang.index', ['status_filter' => 'Diajukan']) }}" class="btn btn-outline-dark btn-sm stretched-link">Lihat Detail</a>
                    @elsecan('pengajuan-barang-list-own')
                     <a href="{{ route('pengajuan.barang.index', ['status_filter' => 'Diajukan']) }}" class="btn btn-outline-dark btn-sm stretched-link">Lihat Detail</a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-white bg-info shadow-sm h-100"> {{-- Warna disesuaikan --}}
                <div class="card-body text-center">
                    <i class="bi bi-journal-check fs-1 mb-2"></i>
                    <h5 class="card-title">{{ $pengajuanDisetujui }}</h5>
                    <p class="card-text">Pengajuan Disetujui (Menunggu Proses)</p>
                     @can('pengajuan-barang-list-all')
                    <a href="{{ route('admin.pengajuan.barang.index', ['status_filter' => 'Disetujui']) }}" class="btn btn-outline-light btn-sm stretched-link">Lihat Detail</a>
                    @elsecan('pengajuan-barang-list-own')
                     <a href="{{ route('pengajuan.barang.index', ['status_filter' => 'Disetujui']) }}" class="btn btn-outline-light btn-sm stretched-link">Lihat Detail</a>
                    @endcan
                </div>
            </div>
        </div>
        @endcanany
    </div>

    {{-- Nanti bisa tambahkan grafik atau daftar barang stok menipis di sini --}}
    <div class="row mt-4">
        <div class="col-md-12">
            {{-- Tempat untuk grafik atau tabel ringkasan lainnya --}}
        </div>
    </div>
</div>
@endsection