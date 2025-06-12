@extends('layouts.app')

@section('title', 'Form Koreksi Stok / Stok Opname')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Koreksi Stok / Stok Opname</h1>
                <a href="{{ route('stok.pergerakan.index') }}" class="btn btn-secondary">Lihat Riwayat Stok</a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('stok.koreksi.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="barang_id" class="form-label">Pilih Barang <span class="text-danger">*</span></label>
                            <select class="form-select @error('barang_id') is-invalid @enderror" id="barang_id" name="barang_id" required>
                                <option value="">-- Pilih Barang --</option>
                                @foreach ($barangs as $barang)
                                    <option value="{{ $barang->id }}" {{ old('barang_id') == $barang->id ? 'selected' : '' }} data-stok-sistem="{{ $barang->stok }}">
                                        {{ $barang->info_display }}
                                    </option>
                                @endforeach
                            </select>
                            @error('barang_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="stok_sistem_sekarang" class="form-label">Stok Sistem Saat Ini</label>
                            <input type="number" class="form-control" id="stok_sistem_sekarang" readonly disabled>
                        </div>

                        <div class="mb-3">
                            <label for="stok_fisik_baru" class="form-label">Stok Fisik Baru (Hasil Opname) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('stok_fisik_baru') is-invalid @enderror" id="stok_fisik_baru" name="stok_fisik_baru" value="{{ old('stok_fisik_baru') }}" min="0" required>
                            @error('stok_fisik_baru')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_koreksi" class="form-label">Tanggal Koreksi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal_koreksi') is-invalid @enderror" id="tanggal_koreksi" name="tanggal_koreksi" value="{{ old('tanggal_koreksi', now()->format('Y-m-d')) }}" required max="{{ now()->format('Y-m-d') }}">
                            @error('tanggal_koreksi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="alasan_koreksi" class="form-label">Alasan Koreksi / Catatan Opname <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('alasan_koreksi') is-invalid @enderror" id="alasan_koreksi" name="alasan_koreksi" rows="3" required>{{ old('alasan_koreksi') }}</textarea>
                            @error('alasan_koreksi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning">Simpan Koreksi Stok</button>
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
        const stokSistemInput = document.getElementById('stok_sistem_sekarang');

        function updateStokSistemDisplay() {
            const selectedOption = barangSelect.options[barangSelect.selectedIndex];
            const stokSistem = selectedOption.getAttribute('data-stok-sistem');

            if (stokSistem !== null && barangSelect.value !== "") {
                stokSistemInput.value = stokSistem;
            } else {
                stokSistemInput.value = '';
            }
        }

        if (barangSelect) {
            barangSelect.addEventListener('change', updateStokSistemDisplay);
            // Panggil sekali saat load jika ada old value atau pilihan awal
            if (barangSelect.value !== "") {
                updateStokSistemDisplay();
            }
        }
    });
</script>
@endpush