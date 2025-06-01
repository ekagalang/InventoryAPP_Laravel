@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">{{ __('Dashboard') }}</h4>
                </div>
                <div class="card-body">
                    {{ __("Anda berhasil login!") }}
                    <hr>
                    <p>Selamat datang di Aplikasi Inventaris. Anda bisa mulai mengelola data melalui menu navigasi di atas.</p>
                    <p>Halaman dashboard ini nantinya akan berisi ringkasan dan statistik penting.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection