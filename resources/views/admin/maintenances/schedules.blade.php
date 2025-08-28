@extends('layouts.app')

@section('title', 'Jadwal Maintenance - ' . $maintenance->nama_perbaikan)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Jadwal Maintenance</h1>
            <h5 class="text-muted">{{ $maintenance->nama_perbaikan }}</h5>
        </div>
        <a href="{{ route('admin.maintenances.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Info Maintenance --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 fw-bold text-primary">Detail Maintenance</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%"><strong>Nama:</strong></td>
                            <td>{{ $maintenance->nama_perbaikan }}</td>
                        </tr>
                        <tr>
                            <td><strong>Barang:</strong></td>
                            <td>{{ $maintenance->barang->nama_barang ?? 'Umum' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Deskripsi:</strong></td>
                            <td>{{ $maintenance->deskripsi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Mulai:</strong></td>
                            <td>{{ $maintenance->tanggal_maintenance->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Berulang:</strong></td>
                            <td>
                                @if($maintenance->is_recurring)
                                    Setiap {{ $maintenance->recurrence_interval }} {{ $maintenance->recurrence_unit }}
                                    @if($maintenance->max_occurrences)
                                        (Max {{ $maintenance->max_occurrences }} kali)
                                    @endif
                                    @if($maintenance->recurring_end_date)
                                        (Sampai {{ $maintenance->recurring_end_date->format('d M Y') }})
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
                    <h6 class="m-0 fw-bold text-primary">Ringkasan Biaya & Status</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">Rp {{ number_format($totalEstimated, 0, ',', '.') }}</h4>
                                <small class="text-muted">Total Estimasi</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">Rp {{ number_format($totalActual, 0, ',', '.') }}</h4>
                            <small class="text-muted">Total Aktual</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-4">
                            <span class="badge bg-success fs-6">{{ $completedCount }}</span>
                            <br><small>Selesai</small>
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
            <h6 class="m-0 fw-bold text-primary">Daftar Jadwal Maintenance</h6>
        </div>
        <div class="card-body">
            @if($schedules->isEmpty())
                <div class="alert alert-info text-center">
                    Tidak ada jadwal maintenance yang dibuat.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Tanggal Dijadwalkan</th>
                                <th>Estimasi</th>
                                <th>Aktual</th>
                                <th>Status</th>
                                <th>Tgl Selesai</th>
                                <th>Metode</th>
                                <th>Catatan</th>
                                <th>Oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $index => $schedule)
                                <tr class="{{ $schedule->status === 'pending' && $schedule->scheduled_date->isPast() ? 'table-warning' : '' }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        {{ $schedule->scheduled_date->format('d M Y') }}
                                        @if($schedule->status === 'pending' && $schedule->scheduled_date->isPast())
                                            <small class="text-danger d-block">Terlambat</small>
                                        @endif
                                    </td>
                                    <td>Rp {{ number_format($schedule->estimated_cost, 0, ',', '.') }}</td>
                                    <td>
                                        @if($schedule->actual_cost)
                                            Rp {{ number_format($schedule->actual_cost, 0, ',', '.') }}
                                            @if($schedule->actual_cost != $schedule->estimated_cost)
                                                @php
                                                    $diff = $schedule->actual_cost - $schedule->estimated_cost;
                                                    $diffPercent = $schedule->estimated_cost > 0 ? ($diff / $schedule->estimated_cost) * 100 : 0;
                                                @endphp
                                                <small class="d-block {{ $diff > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ $diff > 0 ? '+' : '' }}Rp {{ number_format(abs($diff), 0, ',', '.') }}
                                                    ({{ $diff > 0 ? '+' : '' }}{{ number_format($diffPercent, 1) }}%)
                                                </small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($schedule->status === 'pending')
                                            <span class="badge bg-warning">Menunggu</span>
                                        @elseif($schedule->status === 'completed')
                                            <span class="badge bg-success">Selesai</span>
                                        @elseif($schedule->status === 'cancelled')
                                            <span class="badge bg-danger">Dibatalkan</span>
                                        @endif
                                    </td>
                                    <td>{{ $schedule->completed_date ? $schedule->completed_date->format('d M Y') : '-' }}</td>
                                    <td>{{ $schedule->work_method ?? '-' }}</td>
                                    <td>{{ Str::limit($schedule->notes ?? '-', 30) }}</td>
                                    <td>
                                        @if($schedule->completedBy)
                                            {{ $schedule->completedBy->name }}
                                            <small class="text-muted d-block">{{ $schedule->completed_at->format('d M Y H:i') }}</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($schedule->status === 'pending')
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#completeModal{{ $schedule->id }}">
                                                    <i class="bi bi-check-circle"></i> Selesai
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $schedule->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('admin.maintenance-schedules.cancel', $schedule) }}" method="POST" class="d-inline">
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

                                {{-- Modal Complete Schedule --}}
                                @if($schedule->status === 'pending')
                                <div class="modal fade" id="completeModal{{ $schedule->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.maintenance-schedules.complete', $schedule) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Tandai Selesai - {{ $schedule->scheduled_date->format('d M Y') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Biaya Aktual (Rp) <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control" name="actual_cost" value="{{ $schedule->estimated_cost }}" min="0" step="0.01" required>
                                                        <small class="text-muted">Estimasi: Rp {{ number_format($schedule->estimated_cost, 0, ',', '.') }}</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Tanggal Penyelesaian <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" name="completed_date" value="{{ now()->format('Y-m-d') }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Metode Pengerjaan</label>
                                                        <select class="form-select" name="work_method">
                                                            <option value="">-- Pilih Metode --</option>
                                                            <option value="Internal">Internal</option>
                                                            <option value="Vendor Eksternal">Vendor Eksternal</option>
                                                            <option value="Service Center">Service Center</option>
                                                            <option value="Teknisi Khusus">Teknisi Khusus</option>
                                                            <option value="DIY">DIY (Do It Yourself)</option>
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
                                                    <button type="submit" class="btn btn-success">Tandai Selesai</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Modal Edit Schedule --}}
                                <div class="modal fade" id="editModal{{ $schedule->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.maintenance-schedules.update', $schedule) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Jadwal</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Tanggal Dijadwalkan</label>
                                                        <input type="date" class="form-control" name="scheduled_date" value="{{ $schedule->scheduled_date->format('Y-m-d') }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Estimasi Biaya (Rp)</label>
                                                        <input type="number" class="form-control" name="estimated_cost" value="{{ $schedule->estimated_cost }}" min="0" required>
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