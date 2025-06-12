@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Manajemen Pengguna</h1>
        @can('user-create')
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus-fill"></i> Tambah Pengguna Baru</a>
        @endcan
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($users->isEmpty())
                <div class="alert alert-info text-center">
                    Belum ada data pengguna.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Peran (Roles)</th>
                                <th>Email Terverifikasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $key => $user)
                                <tr>
                                    <td>{{ $users->firstItem() + $key }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @forelse ($user->getRoleNames() as $role)
                                            <span class="badge bg-info me-1">{{ $role }}</span>
                                        @empty
                                            <span class="badge bg-secondary">Belum ada peran</span>
                                        @endforelse
                                    </td>
                                    <td>
                                        @if ($user->email_verified_at)
                                            <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Ya</span>
                                        @else
                                            <span class="badge bg-warning"><i class="bi bi-exclamation-triangle-fill"></i> Belum</span>
                                        @endif
                                    </td>
                                    <td>
                                        @can('user-edit')
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm" title="Edit Pengguna & Peran"><i class="bi bi-pencil-square"></i> Edit</a>
                                        @endcan

                                        @can('user-delete')
                                            {{-- Jangan tampilkan tombol hapus untuk user yang sedang login --}}
                                            @if(Auth::user()->id != $user->id)
                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $user->name }}? Aksi ini tidak dapat dibatalkan.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus Pengguna"><i class="bi bi-trash"></i> Hapus</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($users->hasPages())
        <div class="card-footer">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection