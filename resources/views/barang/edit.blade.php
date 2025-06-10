@extends('layouts.app')

@section('title', 'Edit Barang - ' . $barang->nama_barang)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Edit Barang: {{ $barang->nama_barang }}</h1>
            <a href="{{ route('barang.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('barang.update', $barang->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    {{-- ... Isi form tetap sama seperti sebelumnya (pastikan value diisi dengan $barang->field) ... --}}
                    {{-- Contoh satu field: --}}
                    <div class="mb-3">
                        <label for="nama_barang" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_barang') is-invalid @enderror" id="nama_barang" name="nama_barang" value="{{ old('nama_barang', $barang->nama_barang) }}" required>
                        @error('nama_barang')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipe Item <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipe_item" id="tipe_habis_pakai" value="habis_pakai" {{ old('tipe_item', $barang->tipe_item) == 'habis_pakai' ? 'checked' : '' }}>
                                <label class="form-check-label" for="tipe_habis_pakai">Barang Habis Pakai</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipe_item" id="tipe_aset" value="aset" {{ old('tipe_item', $barang->tipe_item) == 'aset' ? 'checked' : '' }}>
                                <label class="form-check-label" for="tipe_aset">Aset (Barang Pinjaman)</label>
                            </div>
                        </div>
                        @error('tipe_item')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="kode_barang" class="form-label">Kode Barang (Opsional)</label>
                        <input type="text" class="form-control @error('kode_barang') is-invalid @enderror" id="kode_barang" name="kode_barang" value="{{ old('kode_barang', $barang->kode_barang) }}">
                        @error('kode_barang')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $barang->deskripsi) }}</textarea>
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
                            @foreach ($kategoris as $itemKategori) {{-- Ganti nama variabel agar tidak konflik dengan $kategori dari loop lain jika ada --}}
                                <option value="{{ $itemKategori->id }}" {{ old('kategori_id', $barang->kategori_id) == $itemKategori->id ? 'selected' : '' }}>
                                    {{ $itemKategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="unit_id" class="form-label">Unit Barang (Opsional)</label>
                        <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id">
                            <option value="">-- Pilih Unit --</option>
                            @foreach ($units as $itemUnit) {{-- Ganti nama variabel agar tidak konflik --}}
                                <option value="{{ $itemUnit->id }}" {{ old('unit_id', $barang->unit_id) == $itemUnit->id ? 'selected' : '' }}>
                                    {{ $itemUnit->nama_unit }} ({{ $itemUnit->singkatan_unit ?? '' }})
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="lokasi_id" class="form-label">Lokasi Penempatan (Opsional)</label>
                        <select class="form-select @error('lokasi_id') is-invalid @enderror" id="lokasi_id" name="lokasi_id">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach ($lokasis as $itemLokasi) {{-- Ganti nama variabel agar tidak konflik --}}
                                <option value="{{ $itemLokasi->id }}" {{ old('lokasi_id', $barang->lokasi_id) == $itemLokasi->id ? 'selected' : '' }}>
                                    {{ $itemLokasi->nama_lokasi }} {{ $itemLokasi->kode_lokasi ? '(' . $itemLokasi->kode_lokasi . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('lokasi_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3"> {{-- Stok --}}
                            <label for="stok" class="form-label">Stok</label>
                            <input type="number" class="form-control @error('stok') is-invalid @enderror" id="stok" name="stok" value="{{ old('stok', $barang->stok) }}" min="0">
                            @error('stok')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3"> {{-- Stok Minimum --}}
                            <label for="stok_minimum" class="form-label">Stok Minimum (Untuk Notifikasi)</label>
                                <input type="number" class="form-control @error('stok_minimum') is-invalid @enderror" id="stok_minimum" name="stok_minimum" value="{{ old('stok_minimum', $barang->stok_minimum) }}" min="0">
                            @error('stok_minimum')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="harga_beli" class="form-label">Harga Beli (Opsional)</label>
                            <input type="number" step="0.01" class="form-control @error('harga_beli') is-invalid @enderror" id="harga_beli" name="harga_beli" value="{{ old('harga_beli', $barang->harga_beli) }}" min="0">
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
                            <option value="aktif" {{ old('status', $barang->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="rusak" {{ old('status', $barang->status) == 'rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="hilang" {{ old('status', $barang->status) == 'hilang' ? 'selected' : '' }}>Hilang</option>
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
                        @if ($barang->gambar)
                            <small class="form-text text-muted">Gambar saat ini: <a href="{{ asset('images/barangs/' . $barang->gambar) }}" target="_blank">{{ $barang->gambar }}</a></small>
                            <img src="{{ asset('images/barangs/' . $barang->gambar) }}" alt="{{ $barang->nama_barang }}" width="100" class="mt-2 img-thumbnail">
                        @endif
                         @error('gambar')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    {{-- Akhiri dengan tombol --}}
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('barang.index') }}" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">Update Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection