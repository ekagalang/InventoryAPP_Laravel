<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="mb-3">
                    <label for="nama_perbaikan" class="form-label">Nama Perbaikan/Maintenance <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama_perbaikan') is-invalid @enderror" id="nama_perbaikan" name="nama_perbaikan" value="{{ old('nama_perbaikan', $maintenance->nama_perbaikan ?? '') }}" required>
                    @error('nama_perbaikan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi', $maintenance->deskripsi ?? '') }}</textarea>
                    @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="mb-3">
                    <label for="tanggal_maintenance" class="form-label">Tanggal Maintenance <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('tanggal_maintenance') is-invalid @enderror" id="tanggal_maintenance" name="tanggal_maintenance" value="{{ old('tanggal_maintenance', isset($maintenance) && $maintenance->tanggal_maintenance ? \Carbon\Carbon::parse($maintenance->tanggal_maintenance)->format('Y-m-d') : '') }}" required>
                    @error('tanggal_maintenance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="barang_id" class="form-label">Barang Terkait (Opsional)</label>
                    <select class="form-select @error('barang_id') is-invalid @enderror" id="barang_id" name="barang_id">
                        <option value="">-- Umum / Non-Barang --</option>
                        @foreach ($barangs as $barang)
                            <option value="{{ $barang->id }}" {{ old('barang_id', $maintenance->barang_id ?? '') == $barang->id ? 'selected' : '' }}>{{ $barang->nama_barang }}</option>
                        @endforeach
                    </select>
                    @error('barang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="border rounded p-3 mb-3" id="recurring-options-wrapper">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring', $maintenance->is_recurring ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_recurring">Jadwal Berulang?</label>
                    </div>

                    <div id="recurring-details" class="{{ old('is_recurring', $maintenance->is_recurring ?? false) ? '' : 'd-none' }}">
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text">Ulangi setiap</span>
                            <input type="number" class="form-control" name="recurrence_interval" value="{{ old('recurrence_interval', $maintenance->recurrence_interval ?? 1) }}" min="1" max="24">
                            <select class="form-select" name="recurrence_unit">
                                <option value="bulan" {{ old('recurrence_unit', $maintenance->recurrence_unit ?? 'bulan') == 'bulan' ? 'selected' : '' }}>Bulan</option>
                                <option value="tahun" {{ old('recurrence_unit', $maintenance->recurrence_unit ?? '') == 'tahun' ? 'selected' : '' }}>Tahun</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label small">Maksimal berapa kali:</label>
                                <input type="number" class="form-control form-control-sm" name="max_occurrences" value="{{ old('max_occurrences', $maintenance->max_occurrences ?? 12) }}" min="1" max="60" placeholder="12">
                                <small class="text-muted">Max 60 kali</small>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Atau sampai tanggal:</label>
                                <input type="date" class="form-control form-control-sm" name="recurring_end_date" value="{{ old('recurring_end_date', isset($maintenance) && $maintenance->recurring_end_date ? $maintenance->recurring_end_date->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                        <small class="text-info d-block mt-1">Jadwal akan berhenti jika salah satu batasan tercapai</small>
                    </div>
                </div>
                 <div class="mb-3">
                    <label for="biaya" class="form-label">Biaya (Rp)</label>
                    <input type="text" class="form-control format-rupiah @error('biaya') is-invalid @enderror" id="biaya" name="biaya" value="{{ old('biaya', isset($maintenance->biaya) ? (int)$maintenance->biaya : 0) }}" min="0">
                    @error('biaya') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                        <option value="Dijadwalkan" {{ old('status', $maintenance->status ?? 'Dijadwalkan') == 'Dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                        <option value="Selesai" {{ old('status', $maintenance->status ?? '') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Dibatalkan" {{ old('status', $maintenance->status ?? '') == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                     @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="lampiran" class="form-label">Lampiran (Opsional)</label>
                    <input class="form-control @error('lampiran') is-invalid @enderror" type="file" id="lampiran" name="lampiran">
                    @error('lampiran') <div class="invalid-feedback">{{ $message }}</div> @enderror

                    {{-- Tampilkan link ke lampiran lama jika sedang edit --}}
                    @if(isset($maintenance) && $maintenance->lampiran)
                        <div class="mt-2">
                            <a href="{{ asset('storage/' . $maintenance->lampiran) }}" target="_blank">Lihat Lampiran Saat Ini</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-grid mt-3">
            <button type="submit" class="btn btn-primary">{{ $tombol_text ?? 'Simpan' }}</button>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const recurringCheckbox = document.getElementById('is_recurring');
        const recurringDetails = document.getElementById('recurring-details');

        recurringCheckbox.addEventListener('change', function() {
            if (this.checked) {
                recurringDetails.classList.remove('d-none');
            } else {
                recurringDetails.classList.add('d-none');
            }
        });
    });
</script>
@endpush