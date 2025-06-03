@extends('layouts.app')

@section('title', 'Buat Pengajuan Barang Baru')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Buat Pengajuan Barang</h1>
                {{-- Nanti bisa ada link ke daftar pengajuan saya --}}
                {{-- <a href="{{ route('pengajuan.barang.index') }}" class="btn btn-secondary">Lihat Pengajuan Saya</a> --}}
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('pengajuan.barang.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="barang_id" class="form-label">Barang yang Diajukan <span class="text-danger">*</span></label>
                            <select class="form-select @error('barang_id') is-invalid @enderror" id="barang_id" name="barang_id" required>
                                <option value="">-- Pilih Barang --</option>
                                @foreach ($barangs as $barang)
                                    <option value="{{ $barang->id }}" {{ old('barang_id') == $barang->id ? 'selected' : '' }} data-stok="{{ $barang->stok }}">
                                        {{ $barang->nama_barang }} (Stok: {{ $barang->stok }} {{ $barang->unit->singkatan_unit ?? $barang->unit->nama_unit ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('barang_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="kuantitas_diminta" class="form-label">Kuantitas Diminta <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('kuantitas_diminta') is-invalid @enderror" id="kuantitas_diminta" name="kuantitas_diminta" value="{{ old('kuantitas_diminta', 1) }}" min="1" required>
                            @error('kuantitas_diminta')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_dibutuhkan" class="form-label">Tanggal Dibutuhkan (Opsional)</label>
                            <input type="date" class="form-control @error('tanggal_dibutuhkan') is-invalid @enderror" id="tanggal_dibutuhkan" name="tanggal_dibutuhkan" value="{{ old('tanggal_dibutuhkan') }}" min="{{ now()->format('Y-m-d') }}">
                            @error('tanggal_dibutuhkan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="keperluan" class="form-label">Keperluan / Alasan Pengajuan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('keperluan') is-invalid @enderror" id="keperluan" name="keperluan" rows="4" required>{{ old('keperluan') }}</textarea>
                            @error('keperluan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection