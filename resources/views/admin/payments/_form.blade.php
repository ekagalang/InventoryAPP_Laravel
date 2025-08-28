<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="mb-3">
                    <label for="nama_pembayaran" class="form-label">Nama Pembayaran <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama_pembayaran') is-invalid @enderror" id="nama_pembayaran" name="nama_pembayaran" value="{{ old('nama_pembayaran', $payment->nama_pembayaran ?? '') }}" required>
                    @error('nama_pembayaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi', $payment->deskripsi ?? '') }}</textarea>
                    @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select @error('kategori') is-invalid @enderror" id="kategori" name="kategori" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="platform" {{ old('kategori', $payment->kategori ?? '') == 'platform' ? 'selected' : '' }}>Platform Digital</option>
                                <option value="utilitas" {{ old('kategori', $payment->kategori ?? '') == 'utilitas' ? 'selected' : '' }}>Utilitas (IPL)</option>
                                <option value="asuransi" {{ old('kategori', $payment->kategori ?? '') == 'asuransi' ? 'selected' : '' }}>Asuransi</option>
                                <option value="sewa" {{ old('kategori', $payment->kategori ?? '') == 'sewa' ? 'selected' : '' }}>Sewa</option>
                                <option value="berlangganan" {{ old('kategori', $payment->kategori ?? '') == 'berlangganan' ? 'selected' : '' }}>Berlangganan</option>
                                <option value="lainnya" {{ old('kategori', $payment->kategori ?? '') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="penerima" class="form-label">Penerima/Vendor</label>
                            <input type="text" class="form-control @error('penerima') is-invalid @enderror" id="penerima" name="penerima" value="{{ old('penerima', $payment->penerima ?? '') }}" placeholder="Nama penerima pembayaran">
                            @error('penerima') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan Tambahan</label>
                    <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="2">{{ old('keterangan', $payment->keterangan ?? '') }}</textarea>
                    @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="mb-3">
                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', isset($payment) && $payment->tanggal_mulai ? $payment->tanggal_mulai->format('Y-m-d') : '') }}" required>
                    @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="nominal" class="form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control format-rupiah @error('nominal') is-invalid @enderror" id="nominal" name="nominal" value="{{ old('nominal', isset($payment->nominal) ? (int)$payment->nominal : '') }}" min="0" required>
                    @error('nominal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                        <option value="aktif" {{ old('status', $payment->status ?? 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status', $payment->status ?? '') == 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
                        <option value="selesai" {{ old('status', $payment->status ?? '') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Jadwal Berulang --}}
                <div class="border rounded p-3 mb-3">
                    <h6 class="text-primary">Pengaturan Jadwal Berulang</h6>
                    
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text">Ulangi setiap</span>
                        <input type="number" class="form-control @error('recurrence_interval') is-invalid @enderror" name="recurrence_interval" value="{{ old('recurrence_interval', $payment->recurrence_interval ?? 1) }}" min="1" max="24" required>
                        <select class="form-select @error('recurrence_unit') is-invalid @enderror" name="recurrence_unit" required>
                            <option value="hari" {{ old('recurrence_unit', $payment->recurrence_unit ?? '') == 'hari' ? 'selected' : '' }}>Hari</option>
                            <option value="minggu" {{ old('recurrence_unit', $payment->recurrence_unit ?? '') == 'minggu' ? 'selected' : '' }}>Minggu</option>
                            <option value="bulan" {{ old('recurrence_unit', $payment->recurrence_unit ?? 'bulan') == 'bulan' ? 'selected' : '' }}>Bulan</option>
                            <option value="tahun" {{ old('recurrence_unit', $payment->recurrence_unit ?? '') == 'tahun' ? 'selected' : '' }}>Tahun</option>
                        </select>
                    </div>
                    @error('recurrence_interval') <div class="text-danger small">{{ $message }}</div> @enderror
                    @error('recurrence_unit') <div class="text-danger small">{{ $message }}</div> @enderror

                    <div class="row">
                        <div class="col-6">
                            <label class="form-label small">Maksimal berapa kali:</label>
                            <input type="number" class="form-control form-control-sm @error('max_occurrences') is-invalid @enderror" name="max_occurrences" value="{{ old('max_occurrences', $payment->max_occurrences ?? 24) }}" min="1" max="120" placeholder="24">
                            <small class="text-muted">Max 120 kali</small>
                            @error('max_occurrences') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Atau sampai tanggal:</label>
                            <input type="date" class="form-control form-control-sm @error('recurring_end_date') is-invalid @enderror" name="recurring_end_date" value="{{ old('recurring_end_date', isset($payment) && $payment->recurring_end_date ? $payment->recurring_end_date->format('Y-m-d') : '') }}">
                            @error('recurring_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <small class="text-info d-block mt-1">Jadwal akan berhenti jika salah satu batasan tercapai</small>
                </div>

                <div class="mb-3">
                    <label for="lampiran" class="form-label">Lampiran (Opsional)</label>
                    <input class="form-control @error('lampiran') is-invalid @enderror" type="file" id="lampiran" name="lampiran" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    @error('lampiran') <div class="invalid-feedback">{{ $message }}</div> @enderror

                    {{-- Tampilkan link ke lampiran lama jika sedang edit --}}
                    @if(isset($payment) && $payment->lampiran)
                        <div class="mt-2">
                            <a href="{{ asset('storage/' . $payment->lampiran) }}" target="_blank" class="text-decoration-none">
                                <i class="bi bi-paperclip"></i> Lihat Lampiran Saat Ini
                            </a>
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