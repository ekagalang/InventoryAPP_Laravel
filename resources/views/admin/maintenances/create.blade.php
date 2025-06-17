@extends('layouts.app')
@section('title', 'Tambah Jadwal Maintenance')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Tambah Jadwal Maintenance Baru</h1>
        {{-- TOMBOL KEMBALI --}}
        <a href="{{ route('admin.maintenances.index') }}" class="btn btn-secondary btn-sm">Kembali ke Daftar</a>
    </div>

    <form action="{{ route('admin.maintenances.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.maintenances._form', ['tombol_text' => 'Tambah Jadwal'])
    </form>
</div>
@endsection