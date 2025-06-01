@extends('layouts.app')

@section('title', 'Tambah Lokasi Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Tambah Lokasi Baru</h1>
            <a href="{{ route('lokasi.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('lokasi.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="nama_lokasi" class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_lokasi') is-invalid @enderror" id="nama_lokasi" name="nama_lokasi" value="{{ old('nama_lokasi') }}" required>
                        @error('nama_lokasi')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="kode_lokasi" class="form-label">Kode Lokasi (Opsional)</label>
                        <input type="text" class="form-control @error('kode_lokasi') is-invalid @enderror" id="kode_lokasi" name="kode_lokasi" value="{{ old('kode_lokasi') }}">
                        @error('kode_lokasi')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi_lokasi" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control @error('deskripsi_lokasi') is-invalid @enderror" id="deskripsi_lokasi" name="deskripsi_lokasi" rows="3">{{ old('deskripsi_lokasi') }}</textarea>
                        @error('deskripsi_lokasi')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Simpan Lokasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection