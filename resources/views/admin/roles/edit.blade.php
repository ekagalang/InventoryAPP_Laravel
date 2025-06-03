@extends('layouts.app')

@section('title', 'Edit Peran - ' . $role->name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Edit Peran: <span class="fw-normal">{{ $role->name }}</span></h1>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Kembali ke Daftar Peran</a>
            </div>

            @if ($role->name === 'Admin' && false) {{-- Nonaktifkan warning ini untuk sementara, bisa diatur sesuai kebutuhan --}}
                <div class="alert alert-warning">
                    <strong>Perhatian!</strong> Mengubah hak akses untuk peran "Admin" dapat mempengaruhi kemampuan administrasi sistem secara keseluruhan. Lakukan dengan hati-hati.
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT') {{-- Method spoofing untuk request PUT --}}

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Peran <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $role->name) }}" 
                                   {{ $role->name === 'Admin' || $role->name === 'StafGudang' || $role->name === 'Viewer' ? 'readonly' : '' }} required>
                            <small class="form-text text-muted">
                                @if($role->name === 'Admin' || $role->name === 'StafGudang' || $role->name === 'Viewer')
                                    Nama peran default (Admin, StafGudang, Viewer) tidak dapat diubah.
                                @else
                                    Gunakan huruf kecil tanpa spasi jika memungkinkan.
                                @endif
                            </small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Hak Akses (Permissions) untuk Peran Ini <span class="text-danger">*</span></label>
                            @error('permissions')
                                <div class="alert alert-danger p-2 small">{{ $message }}</div>
                            @enderror
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                                @foreach ($permissions->groupBy(function($item) {
                                    return explode('-', $item->name)[0]; // Kelompokkan berdasarkan prefix
                                }) as $groupName => $permissionGroup)
                                    <div class="col">
                                        <div class="card h-100">
                                            <div class="card-header bg-light py-2">
                                                <strong class="text-capitalize">{{ str_replace('_', ' ', $groupName) }}</strong>
                                            </div>
                                            <div class="card-body pt-2 pb-2">
                                                @foreach ($permissionGroup as $permission)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="permission_{{ $permission->id }}"
                                                            {{ (is_array(old('permissions')) && in_array($permission->name, old('permissions'))) || (empty(old('permissions')) && in_array($permission->name, $rolePermissions)) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                            {{ $permission->name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('permissions.*')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Update Peran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection