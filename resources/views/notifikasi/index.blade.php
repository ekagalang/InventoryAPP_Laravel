@extends('layouts.app')

@section('title', 'Semua Notifikasi Saya')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Semua Notifikasi Saya</h1>
        {{-- Tampilkan tombol hanya jika ada notifikasi yang belum dibaca --}}
        @if(Auth::check() && Auth::user()->unreadNotifications()->count() > 0)
            <form action="{{ route('notifikasi.markAllAsRead') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-check2-all"></i> Tandai semua sudah dibaca
                </button>
            </form>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error')) {{-- Untuk error jika notifikasi tidak ditemukan saat redirect --}}
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        @if($notifications->isEmpty())
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-bell-slash fs-1"></i>
                <p class="mt-3 mb-0">Tidak ada notifikasi untuk ditampilkan.</p>
            </div>
        @else
            <div class="list-group list-group-flush">
                @foreach ($notifications as $notification)
                    <a href="{{ route('notifikasi.markAsReadAndRedirect', ['id' => $notification->id, 'url' => $notification->data['url'] ?? route('dashboard')]) }}" 
                       class="list-group-item list-group-item-action d-flex align-items-start {{ $notification->read_at ? 'list-group-item-light text-muted' : 'list-group-item-warning fw-bold' }}">
                        
                        {{-- Ikon bisa disesuaikan berdasarkan tipe notifikasi jika ada pembeda di $notification->data --}}
                        <i class="bi {{ $notification->data['kode_barang'] ?? false ? 'bi-box-seam-fill text-primary' : 'bi-info-circle-fill' }} {{ $notification->read_at ? 'text-secondary' : ($notification->data['kode_barang'] ?? false ? 'text-danger' : 'text-warning') }} me-3 fs-4"></i>
                        
                        <div class="flex-grow-1">
                            <div class="d-flex w-100 justify-content-between">
                                <p class="mb-1">{!! $notification->data['pesan'] ?? 'Notifikasi.' !!}</p>
                                <small class="ms-3 text-nowrap" title="{{ $notification->created_at->format('d M Y, H:i:s') }}">
                                    {{ $notification->created_at->diffForHumans() }}
                                </small>
                            </div>
                            @if (!$notification->read_at)
                                <small><span class="badge bg-warning text-dark">Belum Dibaca</span></small>
                            @else
                                <small class="text-muted" style="font-size: 0.8em;">Dibaca: {{ $notification->read_at->isoFormat('DD MMM YY, HH:mm') }}</small>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($notifications->hasPages())
        <div class="card-footer bg-white d-flex justify-content-center">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>
@endsection