<section>
    <header>
        <h4 class="mb-1">{{ __('Informasi Profil') }}</h4>
        <p class="text-muted small mb-3">
            {{ __("Perbarui informasi profil akun Anda dan alamat email.") }}
        </p>
    </header>

    {{-- Form untuk kirim ulang email verifikasi (jika ada dan dibutuhkan) --}}
    {{-- <form id="send-verification" method="post" action="{{ route('verification.send') }}">...</form> --}}

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">{{ __('Nama') }}</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                {{-- Logika untuk menampilkan pesan verifikasi email dan tombol kirim ulang --}}
            @endif
        </div>

        <div class="d-flex align-items-center gap-4">
            <button type="submit" class="btn btn-primary">{{ __('Simpan Perubahan') }}</button>

            @if (session('status') === 'profile-updated')
                <p class="text-success small m-0">{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>