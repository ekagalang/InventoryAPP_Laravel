@extends('layouts.app')

@section('title', 'Log Aktivitas Sistem')

@section('content')
<div class="container">
    <h1 class="mb-3">Log Aktivitas Sistem</h1>
    <p class="text-muted">Semua riwayat perubahan data penting tercatat di sini.</p>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($activities->isEmpty())
                <div class="alert alert-info text-center">
                    Belum ada aktivitas yang tercatat.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped table-sm">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center align-middle">Waktu</th>
                                <th class="text-center align-middle">Deskripsi Aktivitas</th>
                                <th class="text-center align-middle">Subjek</th>
                                <th class="text-center align-middle">User Pelaku</th>
                                <th class="text-center align-middle">Detail Perubahan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activities as $activity)
                                <tr>
                                    <td class="text-nowrap text-center align-middle">{{ $activity->created_at->isoFormat('DD MMM YYYY, HH:mm:ss') }}</td>
                                    <td class="text-center align-middle">{{ $activity->description }}</td>
                                    <td class="text-center align-middle">
                                        @if($activity->subject)
                                            <span class="badge bg-secondary">{{ $activity->log_name }}</span>
                                            {{-- Coba tampilkan nama atau ID dari subjek --}}
                                            {{ $activity->subject->nama_barang ?? $activity->subject->name ?? ('ID: ' . $activity->subject->id) }}
                                        @else
                                            <span class="text-muted">Subjek Dihapus</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">{{ $activity->causer->name ?? 'Sistem' }}</td>
                                    <td>
                                        {{-- Tampilkan perubahan hanya jika ada data properties 'attributes' atau 'old' --}}
                                        @if($activity->properties->isNotEmpty() && ($activity->properties->has('attributes') || $activity->properties->has('old')))
                                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $activity->id }}" aria-expanded="false">
                                                Lihat Detail
                                            </button>
                                            <div class="collapse mt-2" id="collapse-{{ $activity->id }}">
                                                @if($activity->properties->has('old'))
                                                    <strong>Sebelum:</strong>
                                                    <pre class="mb-1 p-1 bg-light rounded-1 small"><code>{{ json_encode($activity->properties->get('old'), JSON_PRETTY_PRINT) }}</code></pre>
                                                @endif
                                                @if($activity->properties->has('attributes'))
                                                    <strong>Sesudah:</strong>
                                                    <pre class="p-1 bg-light rounded-1 small"><code>{{ json_encode($activity->properties->get('attributes'), JSON_PRETTY_PRINT) }}</code></pre>
                                                @endif
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
         @if ($activities->hasPages())
        <div class="card-footer bg-white d-flex justify-content-center">
            {{ $activities->links() }}
        </div>
        @endif
    </div>
</div>
@endsection