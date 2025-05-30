<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- CSRF Token untuk AJAX nanti --}}

    <title>@yield('title', 'Aplikasi Inventaris')</title> {{-- Judul halaman dinamis --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"> 

    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100"> {{-- Agar footer bisa menempel di bawah --}}

    {{-- Navbar Sederhana --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">InventarisApp</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('barang.*') ? 'active' : '' }}" href="{{ route('barang.index') }}">Manajemen Barang</a>
                    </li>
                    {{-- Tambahkan link navigasi lain di sini nanti (Kategori, Lokasi, Pengguna, dll.) --}}
                    {{-- Contoh:
                    <li class="nav-item">
                        <a class="nav-link" href="#">Kategori</a>
                    </li>
                    --}}

                    {{-- Link Login/Logout (akan kita implementasikan nanti) --}}
                    {{-- @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">Register</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                    Logout
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest --}}
                </ul>
            </div>
        </div>
    </nav>

    {{-- Konten Utama Halaman --}}
    <main class="flex-shrink-0 py-4">
        <div class="container">
            @yield('content') {{-- Ini adalah tempat konten spesifik halaman akan dimasukkan --}}
        </div>
    </main>

    {{-- Footer Sederhana --}}
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">&copy; {{ date('Y') }} Aplikasi Inventaris Anda. Dibuat dengan Laravel & Bootstrap.</span>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>