@extends('layouts.app')

@section('title', 'Catat Barang Keluar')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Catat Barang Keluar</h1>
                <a href="{{ route('stok.pergerakan.index') }}" class="btn btn-secondary">Lihat Riwayat Stok</a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('stok.keluar.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="barang_id" class="form-label">Barang <span class="text-danger">*</span></label>
                            <select class="form-select @error('barang_id') is-invalid @enderror" id="barang_id" name="barang_id" required>
                                <option value="">-- Pilih Barang --</option>
                                @foreach ($barangs as $barang)
                                    <option value="{{ $barang->id }}" {{ old('barang_id') == $barang->id ? 'selected' : '' }} data-stok="{{ $barang->stok }}">
                                        {{ $barang->nama_barang }} (Stok Tersedia: {{ $barang->stok }})
                                    </option>
                                @endforeach
                            </select>
                            @error('barang_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="kuantitas" class="form-label">Kuantitas Keluar <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('kuantitas') is-invalid @enderror" id="kuantitas" name="kuantitas" value="{{ old('kuantitas', 1) }}" min="1" required>
                            <small id="stokHelp" class="form-text text-muted d-none">Stok barang yang dipilih: <span id="infoStokBarang">0</span>.</small>
                            @error('kuantitas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_pergerakan" class="form-label">Tanggal Barang Keluar <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('tanggal_pergerakan') is-invalid @enderror" id="tanggal_pergerakan" name="tanggal_pergerakan" value="{{ old('tanggal_pergerakan', now()->format('Y-m-d\TH:i')) }}" required>
                            @error('tanggal_pergerakan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="catatan" class="form-label">Catatan (Misal: No. SO, Keperluan, dll. Opsional)</label>
                            <textarea class="form-control @error('catatan') is-invalid @enderror" id="catatan" name="catatan" rows="3">{{ old('catatan') }}</textarea>
                            @error('catatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-danger">Simpan Barang Keluar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const barangSelect = document.getElementById('barang_id');
        const infoStokBarang = document.getElementById('infoStokBarang');
        const stokHelp = document.getElementById('stokHelp');
        const kuantitasInput = document.getElementById('kuantitas');

        function updateStokInfo() {
            const selectedOption = barangSelect.options[barangSelect.selectedIndex];
            const stokTersedia = selectedOption.getAttribute('data-stok');

            if (stokTersedia !== null && barangSelect.value !== "") {
                infoStokBarang.textContent = stokTersedia;
                stokHelp.classList.remove('d-none');
                kuantitasInput.max = stokTersedia; // Set max attribute for basic client-side validation
            } else {
                stokHelp.classList.add('d-none');
                kuantitasInput.removeAttribute('max');
            }
        }

        if (barangSelect) {
            barangSelect.addEventListener('change', updateStokInfo);
            // Panggil sekali saat load jika ada old value
            if (barangSelect.value !== "") {
                updateStokInfo();
            }
        }
    });
</script>
@endpush