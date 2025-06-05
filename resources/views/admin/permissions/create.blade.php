@extends('layouts.app')

@section('title', 'Tambah Hak Akses (Permission) Baru')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Tambah Permission Baru</h1>
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">Kembali ke Daftar Permissions</a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.permissions.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Permission <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: barang-lihat, user-kelola" required autofocus>
                            <small class="form-text text-muted">Gunakan format `modul-aksi` (huruf kecil, pisahkan dengan strip). Contoh: `barang-list`, `pengajuan-barang-approve`.</small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Guard name biasanya default 'web', jadi tidak perlu input --}}
                        {{-- Jika ingin guard name bisa dipilih, tambahkan inputnya di sini --}}

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Simpan Permission</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection