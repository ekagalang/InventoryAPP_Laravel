@extends('layouts.app')

@section('title', 'Edit Hak Akses (Permission) - ' . $permission->name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Edit Permission: <span class="fw-normal">{{ $permission->name }}</span></h1>
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">Kembali ke Daftar Permissions</a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.permissions.update', $permission->id) }}" method="POST">
                        @csrf
                        @method('PUT') {{-- Method spoofing untuk request PUT --}}

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Permission <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $permission->name) }}" required>
                            <small class="form-text text-muted">Mengubah nama permission dapat mempengaruhi pengecekan hak akses yang sudah ada. Lakukan dengan hati-hati. Format disarankan: `modul-aksi`.</small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Guard name biasanya tidak diubah setelah dibuat, biarkan default --}}
                        <div class="mb-3">
                            <label for="guard_name" class="form-label">Guard Name</label>
                            <input type="text" class="form-control" id="guard_name" name="guard_name_display" value="{{ $permission->guard_name }}" readonly disabled>
                            <small class="form-text text-muted">Guard name biasanya tidak diubah.</small>
                        </div>


                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Update Permission</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection