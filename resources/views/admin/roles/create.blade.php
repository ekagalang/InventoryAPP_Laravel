@extends('layouts.app')

@section('title', 'Tambah Peran Baru')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10"> {{-- Lebarkan sedikit kolomnya untuk daftar permission --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Tambah Peran Baru</h1>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Kembali ke Daftar Peran</a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.roles.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Peran <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Manajer Keuangan, Staf IT" required autofocus>
                            <small class="form-text text-muted">Gunakan huruf kecil tanpa spasi jika memungkinkan (misal: 'manajer_keuangan').</small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Hak Akses (Permissions) untuk Peran Ini <span class="text-danger">*</span></label>
                            @error('permissions') {{-- Error umum untuk array permissions --}}
                                <div class="alert alert-danger p-2 small">{{ $message }}</div>
                            @enderror
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                                @foreach ($permissions->groupBy(function($item) {
                                    return explode('-', $item->name)[0]; // Kelompokkan berdasarkan prefix (misal: barang, kategori)
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
                                                            {{ (is_array(old('permissions')) && in_array($permission->name, old('permissions'))) ? 'checked' : '' }}>
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
                            @error('permissions.*') {{-- Error spesifik per item permission --}}
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Simpan Peran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection