@extends('layouts.app')

@php
    // Definisikan judul dinamis di sini
    $judul = 'Tambah Barang Baru';
@endphp

@section('title', $judul)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">{{ $judul }}</h1>
        <div>
            <a href="{{ route('barang.index') }}" class="btn btn-secondary btn-sm">Kembali ke Daftar Barang</a>
        </div>
    </div>

    <form action="{{ route('barang.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            {{-- KOLOM KIRI: Informasi Utama & Detail --}}
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Dasar</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="nama_barang" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_barang') is-invalid @enderror" id="nama_barang" name="nama_barang" value="{{ old('nama_barang') }}" required>
                            @error('nama_barang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="kode_barang" class="form-label">Kode Barang (Opsional)</label>
                            <input type="text" class="form-control @error('kode_barang') is-invalid @enderror" id="kode_barang" name="kode_barang" value="{{ old('kode_barang') }}">
                            @error('kode_barang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Detail Stok & Harga</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="stok" class="form-label">Stok Awal</label>
                                <input type="number" class="form-control @error('stok') is-invalid @enderror" id="stok" name="stok" value="{{ old('stok', 0) }}" min="0">
                                @error('stok')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stok_minimum" class="form-label">Stok Minimum</label>
                                <input type="number" class="form-control @error('stok_minimum') is-invalid @enderror" id="stok_minimum" name="stok_minimum" value="{{ old('stok_minimum', 0) }}" min="0">
                                @error('stok_minimum')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="harga_beli" class="form-label">Harga Beli (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="0.01" class="form-control @error('harga_beli') is-invalid @enderror" id="harga_beli" name="harga_beli" value="{{ old('harga_beli') }}" min="0">
                                @error('harga_beli')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: Pengaturan & Gambar --}}
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Pengaturan & Klasifikasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Tipe Item <span class="text-danger">*</span></label>
                            <div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_item" id="tipe_habis_pakai" value="habis_pakai" {{ old('tipe_item', 'habis_pakai') == 'habis_pakai' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tipe_habis_pakai">Barang Habis Pakai</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipe_item" id="tipe_aset" value="aset" {{ old('tipe_item') == 'aset' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tipe_aset">Aset (Barang Pinjaman)</label>
                                </div>
                            </div>
                            @error('tipe_item') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="kategori_id" class="form-label">Kategori</label>
                            <select class="form-select @error('kategori_id') is-invalid @enderror" id="kategori_id" name="kategori_id">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>{{ $kategori->nama_kategori }}</option>
                                @endforeach
                            </select>
                            @error('kategori_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id">
                                <option value="">-- Pilih Unit --</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->nama_unit }} ({{ $unit->singkatan_unit }})</option>
                                @endforeach
                            </select>
                            @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="lokasi_id" class="form-label">Lokasi</label>
                            <select class="form-select @error('lokasi_id') is-invalid @enderror" id="lokasi_id" name="lokasi_id">
                                <option value="">-- Pilih Lokasi --</option>
                                @foreach ($lokasis as $lokasi)
                                    <option value="{{ $lokasi->id }}" {{ old('lokasi_id') == $lokasi->id ? 'selected' : '' }}>{{ $lokasi->nama_lokasi }}</option>
                                @endforeach
                            </select>
                            @error('lokasi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                         <div class="mb-3">
                            <label for="status" class="form-label">Status Awal</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="rusak" {{ old('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                <option value="hilang" {{ old('status') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                            </select>
                             @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Gambar Barang</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="gambar" class="form-label">Upload Gambar (Opsional)</label>
                            <input class="form-control @error('gambar') is-invalid @enderror" type="file" id="gambar" name="gambar">
                            @error('gambar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Simpan Barang</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
