@extends('layouts.app')

@section('title', 'Manajemen Hak Akses (Permissions)')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Manajemen Hak Akses (Permissions)</h1>
        @can('role-permission-manage') {{-- Atau permission lebih spesifik 'permission-create' jika ada --}}
            <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary"><i class="bi bi-shield-plus"></i> Tambah Permission Baru</a>
        @endcan
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($permissions->isEmpty())
                <div class="alert alert-info text-center">
                    Belum ada data hak akses.
                    @can('role-permission-manage')
                        <a href="{{ route('admin.permissions.create') }}">Buat permission baru sekarang.</a>
                    @endcan
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Permission (Slug)</th>
                                <th>Guard</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissions as $key => $permission)
                                <tr>
                                    <td>{{ $permissions->firstItem() + $key }}</td>
                                    <td><code>{{ $permission->name }}</code></td>
                                    <td><span class="badge bg-secondary">{{ $permission->guard_name }}</span></td>
                                    <td>{{ $permission->created_at->isoFormat('DD MMM YYYY, HH:mm') }}</td>
                                    <td>
                                        @can('role-permission-manage') {{-- Atau 'permission-edit' --}}
                                            <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="btn btn-warning btn-sm" title="Edit Permission"><i class="bi bi-pencil-square"></i> Edit</a>
                                        @endcan
                                        
                                        @can('role-permission-manage') {{-- Atau 'permission-delete' --}}
                                            {{-- Untuk permission default/krusial, tombol hapus bisa di-disable di sini juga --}}
                                            @php
                                                $criticalPermissions = ['role-permission-manage', 'user-list', 'barang-list']; // Sesuaikan
                                            @endphp
                                            @if(!in_array($permission->name, $criticalPermissions))
                                                <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" class="d-inline" onsubmit="return confirm('PERHATIAN! Apakah Anda yakin ingin menghapus permission \'{{ $permission->name }}\'? Menghapus permission akan mencabutnya dari semua peran yang memilikinya dan bisa mempengaruhi fungsionalitas sistem. Aksi ini tidak dapat dibatalkan dengan mudah.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus Permission"><i class="bi bi-trash"></i> Hapus</button>
                                                </form>
                                            @else
                                                <button type="button" class="btn btn-secondary btn-sm" disabled title="Permission default/krusial tidak dapat dihapus"><i class="bi bi-trash"></i> Hapus</button>
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
        @if ($permissions->hasPages())
        <div class="card-footer">
            {{ $permissions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection