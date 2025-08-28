@extends('layouts.app')

@section('title', 'Jadwal Pembayaran - ' . $payment->nama_pembayaran)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Jadwal Pembayaran</h1>
            <h5 class="text-muted">{{ $payment->nama_pembayaran }}</h5>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Info Pembayaran --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 fw-bold text-primary">Detail Pembayaran</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%"><strong>Nama:</strong></td>
                            <td>{{ $payment->nama_pembayaran }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kategori:</strong></td>
                            <td>
                                <span class="badge 
                                    @if($payment->kategori == 'platform') bg-info
                                    @elseif($payment->kategori == 'utilitas') bg-warning
                                    @elseif($payment->kategori == 'asuransi') bg-success
                                    @elseif($payment->kategori == 'sewa') bg-primary
                                    @elseif($payment->kategori == 'berlangganan') bg-secondary
                                    @else bg-dark @endif">
                                    {{ $payment->kategori_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Penerima:</strong></td>
                            <td>{{ $payment->penerima ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Mulai:</strong></td>
                            <td>{{ $payment->tanggal_mulai->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Berulang:</strong></td>
                            <td>
                                @if($payment->is_recurring)
                                    Setiap {{ $payment->recurrence_interval }} {{ $payment->recurrence_unit }}
                                    @if($payment->max_occurrences)
                                        (Max {{ $payment->max_occurrences }} kali)
                                    @endif
                                    @if($payment->recurring_end_date)
                                        (Sampai {{ $payment->recurring_end_date->format('d M Y') }})
                                    @endif
                                @else
                                    Tidak berulang
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 fw-bold text-primary">Ringkasan Pembayaran & Status</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">Rp {{ number_format($totalExpected, 0, ',', '.') }}</h4>
                                <small class="text-muted">Total Diharapkan</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">Rp {{ number_format($totalPaid, 0, ',', '.') }}</h4>
                            <small class="text-muted">Total Dibayar</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-4">
                            <span class="badge bg-success fs-6">{{ $paidCount }}</span>
                            <br><small>Lunas</small>
                        </div>
                        <div class="col-4">
                            <span class="badge bg-warning fs-6">{{ $pendingCount }}</span>
                            <br><small>Menunggu</small>
                        </div>
                        <div class="col-4">
                            <span class="badge bg-danger fs-6">{{ $overdueCount }}</span>
                            <br><small>Terlambat</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Jadwal --}}
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Daftar Jadwal Pembayaran</h6>
        </div>
        <div class="card-body">
            @if($schedules->isEmpty())
                <div class="alert alert-info text-center">
                    Tidak ada jadwal pembayaran yang dibuat.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Jatuh Tempo</th>
                                <th>Diharapkan</th>
                                <th>Dibayar</th>
                                <th>Status</th>
                                <th>Tgl Bayar</th>
                                <th>Metode</th>
                                <th>Catatan</th>
                                <th>Oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $index => $schedule)
                                <tr class="{{ $schedule->is_overdue ? 'table-warning' : '' }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        {{ $schedule->due_date->format('d M Y') }}
                                        @if($schedule->is_overdue)
                                            <small class="text-danger d-block">Terlambat</small>
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format($schedule->expected_amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if($schedule->actual_amount)
                                            Rp {{ number_format($schedule->actual_amount, 0, ',', '.') }}
                                            @if($schedule->difference_amount != 0)
                                                <small class="d-block {{ $schedule->difference_amount > 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $schedule->difference_amount > 0 ? '+' : '' }}Rp {{ number_format(abs($schedule->difference_amount), 0, ',', '.') }}
                                                    ({{ $schedule->difference_amount > 0 ? '+' : '' }}{{ number_format($schedule->difference_percentage, 1) }}%)
                                                </small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($schedule->status === 'pending')
                                            <span class="badge bg-warning">Menunggu</span>
                                        @elseif($schedule->status === 'paid')
                                            <span class="badge bg-success">Lunas</span>
                                        @elseif($schedule->status === 'overdue')
                                            <span class="badge bg-danger">Terlambat</span>
                                        @elseif($schedule->status === 'cancelled')
                                            <span class="badge bg-secondary">Dibatalkan</span>
                                        @endif
                                    </td>
                                    <td>{{ $schedule->paid_date ? $schedule->paid_date->format('d M Y') : '-' }}</td>
                                    <td>{{ $schedule->payment_method ?? '-' }}</td>
                                    <td>{{ Str::limit($schedule->notes ?? '-', 30) }}</td>
                                    <td>
                                        @if($schedule->paidBy)
                                            {{ $schedule->paidBy->name }}
                                            <small class="text-muted d-block">{{ $schedule->paid_at->format('d M Y H:i') }}</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($schedule->status === 'pending')
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#paidModal{{ $schedule->id }}">
                                                    <i class="bi bi-check-circle"></i> Bayar
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $schedule->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('admin.payment-schedules.cancel', $schedule) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin ingin membatalkan jadwal ini?')">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            @if($schedule->attachment)
                                                <a href="{{ asset('storage/' . $schedule->attachment) }}" target="_blank" class="btn btn-outline-info btn-sm">
                                                    <i class="bi bi-paperclip"></i> Lampiran
                                                </a>
                                            @endif
                                        @endif
                                    </td>
                                </tr>

                                {{-- Modal Paid Schedule --}}
                                @if($schedule->status === 'pending')
                                <div class="modal fade" id="paidModal{{ $schedule->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.payment-schedules.paid', $schedule) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Catat Pembayaran - {{ $schedule->due_date->format('d M Y') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nominal Dibayar (Rp) <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control" name="actual_amount" value="{{ $schedule->expected_amount }}" min="0" step="0.01" required>
                                                        <small class="text-muted">Diharapkan: Rp {{ number_format($schedule->expected_amount, 0, ',', '.') }}</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Tanggal Pembayaran <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" name="paid_date" value="{{ now()->format('Y-m-d') }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Metode Pembayaran</label>
                                                        <select class="form-select" name="payment_method">
                                                            <option value="">-- Pilih Metode --</option>
                                                            <option value="Transfer Bank">Transfer Bank</option>
                                                            <option value="Cash">Cash</option>
                                                            <option value="E-Wallet">E-Wallet</option>
                                                            <option value="Kartu Kredit">Kartu Kredit</option>
                                                            <option value="Debit Online">Debit Online</option>
                                                            <option value="Lainnya">Lainnya</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Catatan</label>
                                                        <textarea class="form-control" name="notes" rows="3"></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Lampiran Bukti (Opsional)</label>
                                                        <input type="file" class="form-control" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success">Catat Pembayaran</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Modal Edit Schedule --}}
                                <div class="modal fade" id="editModal{{ $schedule->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.payment-schedules.update', $schedule) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Jadwal</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" name="due_date" value="{{ $schedule->due_date->format('Y-m-d') }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nominal Diharapkan (Rp) <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control" name="expected_amount" value="{{ $schedule->expected_amount }}" min="0" step="0.01" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Catatan</label>
                                                        <textarea class="form-control" name="notes" rows="2">{{ $schedule->notes }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection