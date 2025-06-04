<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Aplikasi Inventaris')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Ini akan memuat app.css kita yang mengimpor Bootstrap --}}

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">InventarisApp</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav"> {{-- Navigasi utama di kiri --}}
                    @auth {{-- Tampil hanya jika sudah login --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        @can('barang-list') {{-- Hanya tampilkan menu jika punya izin lihat daftar barang --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('barang.*') ? 'active' : '' }}" href="{{ route('barang.index') }}">Barang</a>
                            </li>
                        @endcan
                @hasanyrole('Admin|StafGudang') {{-- Tampil hanya untuk Admin ATAU StafGudang --}}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ 
                                request()->routeIs('kategori.*') || 
                                request()->routeIs('unit.*') || 
                                request()->routeIs('lokasi.*') ? 'active' : '' 
                            }}" href="#" id="navbarDropdownDataMaster" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Data Master
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownDataMaster">
                                @can('kategori-list')
                                    <li><a class="dropdown-item {{ request()->routeIs('kategori.*') ? 'active' : '' }}" href="{{ route('kategori.index') }}">Kategori</a></li>
                                @endcan
                                @can('unit-list')
                                    <li><a class="dropdown-item {{ request()->routeIs('unit.*') ? 'active' : '' }}" href="{{ route('unit.index') }}">Unit</a></li>
                                @endcan
                                @can('lokasi-list')
                                    <li><a class="dropdown-item {{ request()->routeIs('lokasi.*') ? 'active' : '' }}" href="{{ route('lokasi.index') }}">Lokasi</a></li>
                                @endcan
                                {{-- Tambahkan item master data lain di sini jika ada --}}
                            </ul>
                        </li>
                    @endhasanyrole 
                    @canany(['stok-pergerakan-list', 'stok-masuk-create', 'stok-keluar-create'])
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('stok.*') ? 'active' : '' }}" href="#" id="navbarDropdownStok" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Manajemen Stok
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownStok">
                                @can('stok-masuk-create')
                                    <li><a class="dropdown-item {{ request()->routeIs('stok.masuk.create') ? 'active' : '' }}" href="{{ route('stok.masuk.create') }}">Catat Barang Masuk</a></li>
                                @endcan
                                @can('stok-keluar-create')
                                    <li><a class="dropdown-item {{ request()->routeIs('stok.keluar.create') ? 'active' : '' }}" href="{{ route('stok.keluar.create') }}">Catat Barang Keluar</a></li>
                                @endcan
                                @can('stok-koreksi')
                                    <li><a class="dropdown-item {{ request()->routeIs('stok.koreksi.create') ? 'active' : '' }}" href="{{ route('stok.koreksi.create') }}">Koreksi Stok</a></li>
                                @endcan
                                @if(Auth::user()->hasPermissionTo('stok-masuk-create') && Auth::user()->hasPermissionTo('stok-keluar-create') && Auth::user()->hasPermissionTo('stok-pergerakan-list'))
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                @can('stok-pergerakan-list')
                                    <li><a class="dropdown-item {{ request()->routeIs('stok.pergerakan.index') ? 'active' : '' }}" href="{{ route('stok.pergerakan.index') }}">Riwayat Pergerakan</a></li>
                                @endcan
                                @can('pengajuan-barang-list-all')
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('admin.pengajuan.barang.index') || request()->routeIs('admin.pengajuan.barang.show') ? 'active' : '' }}" href="{{ route('admin.pengajuan.barang.index') }}">Kelola Pengajuan Barang</a></li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                        @canany(['user-list', 'role-permission-manage'])
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="#" id="navbarDropdownAdminSettings" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Pengaturan Admin
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownAdminSettings">
                                @can('user-list')
                                <li><a class="dropdown-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Manajemen User</a></li>
                                @endcan
                                @can('role-permission-manage')
                                <li><a class="dropdown-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">Manajemen Peran</a></li>
                                @endcan
                            </ul>
                        </li>
                        @endcanany
                        @can('pengajuan-barang-create')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('pengajuan.barang.create') ? 'active' : '' }}" href="{{ route('pengajuan.barang.create') }}">Buat Pengajuan Barang</a>
                            </li>
                        @endcan
                        @can('pengajuan-barang-list-own') {{-- Nanti untuk daftar pengajuan sendiri --}}
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('pengajuan.barang.index') ? 'active' : '' }}" href="{{ route('pengajuan.barang.index') }}">Pengajuan Saya</a>
                            </li>
                        @endcan
                        @canany(['view-laporan-stok' /*, 'view-laporan-lainnya' */])
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->is('laporan/*') ? 'active' : '' }}" href="#" id="navbarDropdownLaporan" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Laporan
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownLaporan">
                                @can('view-laporan-stok')
                                <li><a class="dropdown-item {{ request()->routeIs('laporan.stok.barang') ? 'active' : '' }}" href="{{ route('laporan.stok.barang') }}">Laporan Stok Barang</a></li>
                                @endcan
                                @can('view-laporan-barang-masuk')
                                <li><a class="dropdown-item {{ request()->routeIs('laporan.barang.masuk') ? 'active' : '' }}" href="{{ route('laporan.barang.masuk') }}">Laporan Barang Masuk</a></li>
                                @endcan
                                @can('view-laporan-barang-keluar')
                                <li><a class="dropdown-item {{ request()->routeIs('laporan.barang.keluar') ? 'active' : '' }}" href="{{ route('laporan.barang.keluar') }}">Laporan Barang Keluar</a></li>
                                @endcan
                                {{-- Nanti tambahkan link laporan lain di sini --}}
                            </ul>
                        </li>
                        @endcanany
                    @endauth
                </ul>
                <ul class="navbar-nav ms-auto"> {{-- Navigasi otentikasi di kanan --}}
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                        @endif

                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @endif
                    @else   
                        <li class="nav-item dropdown">
                            <a id="navbarDropdownUser" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                                {{-- AKTIFKAN ATAU TAMBAHKAN LINK PROFIL DI SINI --}}
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    {{ __('Profil Saya') }}
                                </a>
                                <div class="dropdown-divider"></div> {{-- Pemisah opsional --}}
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                                document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-shrink-0 py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span class="text-muted">&copy; {{ date('Y') }} Aplikasi Inventaris Anda. Dibuat dengan Laravel & Bootstrap.</span>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>