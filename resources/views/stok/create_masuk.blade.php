@extends('layouts.app')

@section('title', 'Catat Barang Masuk')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Catat Barang Masuk</h1>
                <a href="{{ route('stok.pergerakan.index') }}" class="btn btn-secondary">Lihat Riwayat Stok</a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('stok.masuk.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="barang_id" class="form-label">Barang <span class="text-danger">*</span></label>
                            <select class="form-select @error('barang_id') is-invalid @enderror" id="barang_id" name="barang_id" required>
                                <option value="">-- Pilih Barang --</option>
                                @foreach ($barangs as $barang)
                                    <option value="{{ $barang->id }}" {{ old('barang_id') == $barang->id ? 'selected' : '' }}>
                                        {{ $barang->nama_barang }} (Stok Saat Ini: {{ $barang->stok }})
                                    </option>
                                @endforeach
                            </select>
                            @error('barang_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="kuantitas" class="form-label">Kuantitas Masuk <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('kuantitas') is-invalid @enderror" id="kuantitas" name="kuantitas" value="{{ old('kuantitas', 1) }}" min="1" required>
                            @error('kuantitas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_pergerakan" class="form-label">Tanggal Barang Masuk <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('tanggal_pergerakan') is-invalid @enderror" id="tanggal_pergerakan" name="tanggal_pergerakan" value="{{ old('tanggal_pergerakan', now()->format('Y-m-d\TH:i')) }}" required>
                            @error('tanggal_pergerakan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="catatan" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control @error('catatan') is-invalid @enderror" id="catatan" name="catatan" rows="3">{{ old('catatan') }}</textarea>
                            @error('catatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Simpan Barang Masuk</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection