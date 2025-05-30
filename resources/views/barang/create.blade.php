@extends('layouts.app')

@section('title', 'Tambah Barang Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Tambah Barang Baru</h1>
            <a href="{{ route('barang.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('barang.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- ... Isi form tetap sama seperti sebelumnya ... --}}
                    {{-- Contoh satu field: --}}
                    <div class="mb-3">
                        <label for="nama_barang" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_barang') is-invalid @enderror" id="nama_barang" name="nama_barang" value="{{ old('nama_barang') }}" required>
                        @error('nama_barang')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="kode_barang" class="form-label">Kode Barang (Opsional)</label>
                        <input type="text" class="form-control @error('kode_barang') is-invalid @enderror" id="kode_barang" name="kode_barang" value="{{ old('kode_barang') }}">
                        @error('kode_barang')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="kategori_id" class="form-label">Kategori Barang (Opsional)</label>
                        <select class="form-select @error('kategori_id') is-invalid @enderror" id="kategori_id" name="kategori_id">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($kategoris as $kategori)
                                <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stok" class="form-label">Stok Awal</label>
                            <input type="number" class="form-control @error('stok') is-invalid @enderror" id="stok" name="stok" value="{{ old('stok', 0) }}" min="0">
                            @error('stok')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="harga_beli" class="form-label">Harga Beli (Opsional)</label>
                            <input type="number" step="0.01" class="form-control @error('harga_beli') is-invalid @enderror" id="harga_beli" name="harga_beli" value="{{ old('harga_beli') }}" min="0">
                            @error('harga_beli')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                            <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="rusak" {{ old('status') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="hilang" {{ old('status') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                        </select>
                         @error('status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="gambar" class="form-label">Gambar Barang (Opsional)</label>
                        <input class="form-control @error('gambar') is-invalid @enderror" type="file" id="gambar" name="gambar">
                         @error('gambar')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- Akhiri dengan tombol --}}
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('barang.index') }}" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection