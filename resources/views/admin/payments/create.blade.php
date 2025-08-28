@extends('layouts.app')

@section('title', 'Tambah Pembayaran Rutin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Pembayaran Rutin</h1>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <form action="{{ route('admin.payments.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.payments._form', ['tombol_text' => 'Simpan'])
    </form>
</div>
@endsection