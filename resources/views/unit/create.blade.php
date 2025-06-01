@extends('layouts.app')

@section('title', 'Tambah Unit Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Tambah Unit Baru</h1>
            <a href="{{ route('unit.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('unit.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="nama_unit" class="form-label">Nama Unit <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_unit') is-invalid @enderror" id="nama_unit" name="nama_unit" value="{{ old('nama_unit') }}" required>
                        @error('nama_unit')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="singkatan_unit" class="form-label">Singkatan Unit (Opsional)</label>
                        <input type="text" class="form-control @error('singkatan_unit') is-invalid @enderror" id="singkatan_unit" name="singkatan_unit" value="{{ old('singkatan_unit') }}">
                        @error('singkatan_unit')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi_unit" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control @error('deskripsi_unit') is-invalid @enderror" id="deskripsi_unit" name="deskripsi_unit" rows="3">{{ old('deskripsi_unit') }}</textarea>
                        @error('deskripsi_unit')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Simpan Unit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection