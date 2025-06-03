@extends('layouts.app')

@section('title', 'Edit Pengguna - ' . $user->name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Edit Pengguna: <span class="fw-normal">{{ $user->name }}</span></h1>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Kembali ke Daftar Pengguna</a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT') {{-- Method spoofing untuk request PUT --}}

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>
                        <p class="text-muted small">Biarkan kosong jika tidak ingin mengubah password.</p>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru (Opsional)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                        </div>
                        <hr>

                        <div class="mb-3">
                            <label for="roles" class="form-label">Peran (Roles) <span class="text-danger">*</span></label>
                            <select multiple class="form-select @error('roles') is-invalid @enderror @error('roles.*') is-invalid @enderror" id="roles" name="roles[]" required size="3"> {{-- Atribut size ditambahkan untuk tampilan awal yang lebih baik untuk multi-select standar --}}
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" 
                                        {{ (is_array(old('roles')) && in_array($role->name, old('roles'))) || (empty(old('roles')) && in_array($role->name, $userRoles)) ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Pilih satu atau lebih peran. Tahan Ctrl (atau Cmd di Mac) untuk memilih lebih dari satu.</small>
                            @error('roles')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('roles.*') {{-- Untuk error pada setiap item di array roles --}}
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Update Pengguna</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Jika Anda menggunakan Select2 atau plugin JS lain untuk multiselect, tambahkan scriptnya di sini --}}
{{-- Contoh untuk Select2 (pastikan jQuery sudah ada jika menggunakan Select2 versi lama): --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> {{-- Contoh jQuery --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() { // Jika pakai jQuery
    document.addEventListener('DOMContentLoaded', function() { // Vanilla JS
        Inisialisasi Select2 jika Anda mau
        const rolesSelect = document.getElementById('roles');
        if (rolesSelect) {
            $(rolesSelect).select2({ // Jika pakai jQuery
                placeholder: "-- Pilih Peran --",
                allowClear: true
            });
        }
    });
</script>
@endpush