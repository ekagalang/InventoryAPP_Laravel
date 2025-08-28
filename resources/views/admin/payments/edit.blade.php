@extends('layouts.app')

@section('title', 'Edit Pembayaran Rutin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Pembayaran Rutin</h1>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <form action="{{ route('admin.payments.update', $payment) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.payments._form', ['tombol_text' => 'Update'])
    </form>
</div>
@endsection