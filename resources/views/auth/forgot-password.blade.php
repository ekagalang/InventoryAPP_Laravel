@extends('layouts.app')

@section('title', 'Lupa Password')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-lg-5">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">{{ __('Lupa Password Anda?') }}</h4>
                </div>

                <div class="card-body p-4">
                    <div class="mb-4 text-muted small">
                        {{ __('Tidak masalah. Cukup beritahu kami alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang password yang memungkinkan Anda memilih yang baru.') }}
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success mb-3 small">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Alamat Email') }}</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Kirim Tautan Reset Password') }}
                            </button>
                        </div>

                        <hr class="my-4">
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="btn btn-link btn-sm">Kembali ke Halaman Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection