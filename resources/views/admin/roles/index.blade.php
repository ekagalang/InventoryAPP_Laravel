@extends('layouts.app')

@section('title', 'Manajemen Peran (Roles)')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Manajemen Peran (Roles)</h1>
        @can('role-permission-manage') {{-- Atau permission lebih spesifik 'role-create' --}}
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle-fill"></i> Tambah Peran Baru</a>
        @endcan
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($roles->isEmpty())
                <div class="alert alert-info text-center">
                    Belum ada data peran.
                    @can('role-permission-manage')
                        <a href="{{ route('admin.roles.create') }}">Buat peran baru sekarang.</a>
                    @endcan
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Peran</th>
                                <th>Guard</th>
                                <th class="text-center">Jumlah Permissions</th>
                                <th class="text-center">Jumlah Pengguna</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $key => $role)
                                <tr>
                                    <td>{{ $roles->firstItem() + $key }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td><span class="badge bg-secondary">{{ $role->guard_name }}</span></td>
                                    <td class="text-center">{{ $role->permissions_count }}</td>
                                    <td class="text-center">{{ $role->users_count }}</td>
                                    <td>
                                        @can('role-permission-manage') {{-- Atau permission 'role-edit' --}}
                                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-warning btn-sm" title="Edit Peran & Permissions"><i class="bi bi-pencil-square"></i> Edit</a>
                                        @endcan
                                        
                                        @can('role-permission-manage') {{-- Atau permission 'role-delete' --}}
                                            @if(!in_array($role->name, ['Admin', 'StafGudang', 'Viewer'])) {{-- Sesuaikan dengan peran default Anda --}}
                                                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus peran \'{{ $role->name }}\'? Pengguna dengan peran ini mungkin kehilangan akses tertentu.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus Peran"><i class="bi bi-trash"></i> Hapus</button>
                                                </form>
                                            @else
                                                <button type="button" class="btn btn-secondary btn-sm" disabled title="Peran default tidak dapat dihapus"><i class="bi bi-trash"></i> Hapus</button>
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
        @if ($roles->hasPages())
        <div class="card-footer">
            {{ $roles->links() }}
        </div>
        @endif
    </div>
</div>
@endsection