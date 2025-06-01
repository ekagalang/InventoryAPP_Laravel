<section class="space-y-6">
    <header>
        <h4 class="mb-1 text-danger">{{ __('Hapus Akun') }}</h4>
        <p class="text-muted small mb-3">
            {{ __('Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Sebelum menghapus akun Anda, harap unduh data atau informasi apa pun yang ingin Anda simpan.') }}
        </p>
    </header>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
        {{ __('Hapus Akun Saya') }}
    </button>

    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true"
        x-data="{ show: false, password: '' }" {{-- x-data dari Alpine.js untuk state modal jika diperlukan, atau bisa dihapus jika hanya Bootstrap modal --}}
        x-on:close-modal.window="show = false" {{-- Event listener Alpine.js --}}
    >
        <div class="modal-dialog">
            <form method="post" action="{{ route('profile.destroy') }}" class="modal-content">
                @csrf
                @method('delete')

                <div class="modal-header">
                    <h5 class="modal-title" id="confirmUserDeletionModalLabel">{{ __('Apakah Anda yakin ingin menghapus akun Anda?') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-muted small">
                        {{ __('Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Harap masukkan password Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun Anda secara permanen.') }}
                    </p>

                    <div class="mt-3">
                        <label for="password_delete_account" class="form-label visually-hidden">{{ __('Password') }}</label>
                        <input
                            id="password_delete_account"
                            name="password"
                            type="password"
                            class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                            placeholder="{{ __('Password Anda') }}"
                            x-model="password" {{-- Alpine.js model binding --}}
                        >
                        @error('password', 'userDeletion') {{-- Error bag 'userDeletion' --}}
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-danger" x-bind:disabled="!password">{{ __('Hapus Akun') }}</button> {{-- Tombol disable jika password kosong via Alpine.js --}}
                </div>
            </form>
        </div>
    </div>
</section>