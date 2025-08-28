@extends('layouts.app')

@section('title', 'Detail Pembayaran Rutin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Pembayaran Rutin</h1>
        <div>
            <a href="{{ route('admin.payments.schedules', $payment) }}" class="btn btn-info">
                <i class="bi bi-calendar-event"></i> Lihat Jadwal
            </a>
            <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="m-0 fw-bold text-primary">Informasi Pembayaran</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Nama Pembayaran:</th>
                                    <td>{{ $payment->nama_pembayaran }}</td>
                                </tr>
                                <tr>
                                    <th>Kategori:</th>
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
                                    <th>Penerima/Vendor:</th>
                                    <td>{{ $payment->penerima ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Nominal:</th>
                                    <td><strong>Rp {{ number_format($payment->nominal, 0, ',', '.') }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge 
                                            @if($payment->status == 'aktif') bg-success
                                            @elseif($payment->status == 'nonaktif') bg-secondary  
                                            @else bg-danger @endif">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Tanggal Mulai:</th>
                                    <td>{{ $payment->tanggal_mulai->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Berulang:</th>
                                    <td>
                                        @if($payment->is_recurring)
                                            <i class="bi bi-arrow-repeat text-success"></i>
                                            Setiap {{ $payment->recurrence_interval }} {{ $payment->recurrence_unit }}
                                        @else
                                            <i class="bi bi-dash text-muted"></i> Sekali saja
                                        @endif
                                    </td>
                                </tr>
                                @if($payment->max_occurrences)
                                    <tr>
                                        <th>Maksimal:</th>
                                        <td>{{ $payment->max_occurrences }} kali</td>
                                    </tr>
                                @endif
                                @if($payment->recurring_end_date)
                                    <tr>
                                        <th>Berakhir:</th>
                                        <td>{{ $payment->recurring_end_date->format('d M Y') }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Dibuat oleh:</th>
                                    <td>{{ $payment->user->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($payment->deskripsi)
                        <div class="mt-3">
                            <h6>Deskripsi:</h6>
                            <p class="text-muted">{{ $payment->deskripsi }}</p>
                        </div>
                    @endif

                    @if($payment->keterangan)
                        <div class="mt-3">
                            <h6>Keterangan Tambahan:</h6>
                            <p class="text-muted">{{ $payment->keterangan }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="m-0 fw-bold text-primary">Aksi Cepat</h6>
                </div>
                <div class="card-body text-center">
                    @if($payment->is_recurring)
                        <a href="{{ route('admin.payments.schedules', $payment) }}" class="btn btn-info btn-lg w-100 mb-2">
                            <i class="bi bi-calendar-event"></i><br>
                            <small>Lihat Jadwal Pembayaran</small>
                        </a>
                    @endif

                    <a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-warning w-100 mb-2">
                        <i class="bi bi-pencil"></i> Edit Pembayaran
                    </a>

                    @if($payment->lampiran)
                        <a href="{{ asset('storage/' . $payment->lampiran) }}" target="_blank" class="btn btn-outline-info w-100 mb-2">
                            <i class="bi bi-paperclip"></i> Lihat Lampiran
                        </a>
                    @endif

                    <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pembayaran ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Hapus Pembayaran
                        </button>
                    </form>
                </div>
            </div>

            @if($payment->created_at)
                <div class="card shadow-sm mt-3">
                    <div class="card-header">
                        <h6 class="m-0 fw-bold text-secondary">Riwayat</h6>
                    </div>
                    <div class="card-body">
                        <small>
                            <strong>Dibuat:</strong><br>
                            {{ $payment->created_at->format('d M Y H:i') }}<br><br>
                            <strong>Terakhir diubah:</strong><br>
                            {{ $payment->updated_at->format('d M Y H:i') }}
                        </small>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection