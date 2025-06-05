@extends('layouts.app')

@section('title', 'Semua Notifikasi Saya')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Semua Notifikasi Saya</h1>
        @if(Auth::user()->unreadNotifications()->count() > 0)
            <form action="{{ route('notifikasi.markAllAsRead') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary">
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

    <div class="card shadow-sm">
        <div class="list-group list-group-flush">
            @forelse ($notifications as $notification)
                <a href="{{ route('notifikasi.markAsReadAndRedirect', ['id' => $notification->id, 'url' => $notification->data['url'] ?? route('dashboard')]) }}" 
                   class="list-group-item list-group-item-action {{ $notification->read_at ? 'list-group-item-light text-muted' : 'list-group-item-warning fw-bold' }}">
                    <div class="d-flex w-100 justify-content-between">
                        <p class="mb-1">
                            <i class="bi bi-exclamation-triangle-fill me-2 {{ $notification->read_at ? 'text-secondary' : 'text-warning' }}"></i>
                            {!! $notification->data['pesan'] ?? 'Notifikasi.' !!}
                        </p>
                        <small title="{{ $notification->created_at->format('d M Y, H:i:s') }}">
                            {{ $notification->created_at->diffForHumans() }}
                        </small>
                    </div>
                    @if (!$notification->read_at)
                        <small class="d-block text-end"><span class="badge bg-warning text-dark">Belum Dibaca</span></small>
                    @else
                        <small class="d-block text-end text-muted">Dibaca pada: {{ $notification->read_at->isoFormat('DD MMM YY, HH:mm') }}</small>
                    @endif
                </a>
            @empty
                <div class="list-group-item">
                    <p class="text-center text-muted my-3">Tidak ada notifikasi untuk ditampilkan.</p>
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
        <div class="card-footer bg-white">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>
@endsection