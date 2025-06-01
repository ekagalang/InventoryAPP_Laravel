@extends('layouts.app') {{-- Layout Bootstrap kita --}}

@section('title', 'Edit Profil')

@section('content')
    {{-- Konten dari edit.blade.php Breeze akan diadaptasi di sini --}}
    {{-- Biasanya ini akan meng-include beberapa partial --}}

    <div class="container py-4">
        <h2 class="mb-4">Pengaturan Profil</h2>

        <div class="p-4 sm:p-8 bg-white shadow card mb-4"> {{-- Menggunakan card Bootstrap --}}
            <div class="card-body">
                 @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white shadow card mb-4">
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white shadow card">
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection