<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Aplikasi Inventaris'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom">
    <div class="container-fluid"> {{-- container-fluid agar lebih lebar --}}
        <a class="navbar-brand" href="{{ Auth::check() ? route('dashboard') : url('/') }}">{{ config('app.name', 'InventarisApp') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbarNav" aria-controls="mainNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @auth {{-- Semua menu navigasi utama hanya tampil jika user sudah login --}}
                    @can('view-dashboard') {{-- Pastikan permission 'view-dashboard' ada dan di-assign --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                    @endcan

                    @can('barang-list')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('barang.*') ? 'active' : '' }}" href="{{ route('barang.index') }}">Barang</a>
                        </li>
                    @endcan

                    {{-- Dropdown Data Master --}}
                    {{-- Tampilkan jika user punya salah satu permission untuk lihat item di dalamnya ATAU peran tertentu --}}
                    @hasanyrole('Admin|StafGudang')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->is('kategori*') || request()->is('unit*') || request()->is('lokasi*') ? 'active' : '' }}" href="#" id="navbarDropdownDataMaster" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                            </ul>
                        </li>
                    @endhasanyrole

                    {{-- Dropdown Manajemen Stok --}}
                    @hasanyrole('Admin|StafGudang')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->is('stok*') || request()->is('admin/pengajuan-barang*') ? 'active' : '' }}" href="#" id="navbarDropdownStok" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                                @if(Auth::user()->hasAnyPermission(['stok-masuk-create', 'stok-keluar-create', 'stok-koreksi']) && Auth::user()->hasPermissionTo('stok-pergerakan-list'))
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                @can('stok-pergerakan-list')
                                    <li><a class="dropdown-item {{ request()->routeIs('stok.pergerakan.index') ? 'active' : '' }}" href="{{ route('stok.pergerakan.index') }}">Riwayat Pergerakan</a></li>
                                @endcan
                                @can('pengajuan-barang-list-all') {{-- Pindahkan Kelola Pengajuan ke sini jika lebih logis --}}
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('admin.pengajuan.barang.index') || request()->routeIs('admin.pengajuan.barang.show') ? 'active' : '' }}" href="{{ route('admin.pengajuan.barang.index') }}">Kelola Pengajuan Barang</a></li>
                                @endcan
                                @can('maintenance-manage')
                                    <li><a class="dropdown-item {{ request()->routeIs('admin.maintenances.*') ? 'active' : '' }}" href="{{ route('admin.maintenances.index') }}">Jadwal Maintenance</a></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" href="{{ route('admin.payments.index') }}">Pembayaran Rutin</a></li>
                                @endcan
                            </ul>
                        </li>
                    @endhasanyrole
                    
                    {{-- Menu Pengajuan Barang untuk User Biasa (jika tidak di bawah Stok) --}}
                    @canany(['pengajuan-barang-create', 'pengajuan-barang-list-own',])
                        @unless (Auth::user()->hasPermissionTo('pengajuan-barang-list-all')) {{-- Hindari duplikasi jika sudah ada di 'Kelola Semua Pengajuan' --}}
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->is('pengajuan-barang*') ? 'active' : '' }}" href="#" id="navbarDropdownUserPengajuan" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Pengajuan Saya
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdownUserPengajuan">
                                    @can('pengajuan-barang-create')
                                        <li><a class="dropdown-item {{ request()->routeIs('pengajuan.barang.create') ? 'active' : '' }}" href="{{ route('pengajuan.barang.pilihTipe') }}">Buat Pengajuan Baru</a></li>
                                    @endcan
                                    @can('pengajuan-barang-list-own')
                                        <li><a class="dropdown-item {{ request()->routeIs('pengajuan.barang.index') ? 'active' : '' }}" href="{{ route('pengajuan.barang.index') }}">Daftar Pengajuan Saya</a></li>
                                    @endcan
                                </ul>
                            </li>
                        @endunless
                    @endcanany

                    {{-- Dropdown Laporan --}}
                    @canany(['view-laporan-stok', 'view-laporan-barang-masuk', 'view-laporan-barang-keluar'])
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
                            @can('view-laporan-maintenance')
                            <li><a class="dropdown-item {{ request()->routeIs('laporan.maintenance') ? 'active' : '' }}" href="{{ route('laporan.maintenance') }}">Laporan Maintenance</a></li>
                            @endcan
                        </ul>
                    </li>
                    @endcanany

                    {{-- Dropdown Pengaturan Admin --}}
                    @canany(['user-list', 'role-permission-manage'])
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('admin/users*') || request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'active' : '' }}" href="#" id="navbarDropdownAdminSettings" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Pengaturan Admin
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownAdminSettings">
                            @can('user-list')
                            <li><a class="dropdown-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Manajemen User</a></li>
                            @endcan
                            @can('role-permission-manage')
                            <li><a class="dropdown-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">Manajemen Peran</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}">Manajemen Hak Akses</a></li>
                            @endcan
                            @can('view-audit-trail')
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item {{ request()->routeIs('admin.activity-logs.index') ? 'active' : '' }}" href="{{ route('admin.activity-logs.index') }}">Log Aktivitas</a></li>
                            @endcan
                        </ul>
                    </li>
                    @endcanany
                @endauth
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @auth
                    {{-- Item Notifikasi --}}
                    {{-- $unreadNotificationsCount dari ViewComposer sudah dicek Auth::check() di AppServiceProvider --}}
                    {{-- Pastikan user punya permission untuk melihat notifikasi (misalnya 'view-dashboard' atau permission lain) --}}
                    @if(Auth::user()->hasPermissionTo('view-dashboard') && isset($unreadNotificationsCount))
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" id="navbarDropdownNotification" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi">
                            <i class="bi bi-bell-fill position-relative fs-5">
                                @if($unreadNotificationsCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.6em; padding: 0.25em 0.4em;">
                                    {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                                    <span class="visually-hidden">notifikasi belum dibaca</span>
                                </span>
                                @endif
                            </i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="navbarDropdownNotification" style="min-width: 350px; max-height: 400px; overflow-y: auto;">
                            <li class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">Notifikasi</h6>
                                @if($unreadNotificationsCount > 0)
                                    <form action="{{ route('notifikasi.markAllAsRead') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm p-0 text-primary fw-normal">Tandai semua dibaca</button>
                                    </form>
                                @endif
                            </li>
                            @forelse($unreadNotifications as $notification)
                                <li>
                                    <a class="dropdown-item py-2 small d-flex align-items-start {{ $notification->read_at ? 'text-muted' : 'fw-bold' }}" 
                                       href="{{ route('notifikasi.markAsReadAndRedirect', ['id' => $notification->id, 'url' => $notification->data['url'] ?? route('dashboard')]) }}">
                                        <i class="bi {{ $notification->data['kode_barang'] ?? false ? 'bi-box-seam-fill' : 'bi-info-circle-fill' }} {{ $notification->read_at ? 'text-secondary' : 'text-warning' }} me-2 mt-1 fs-6"></i>
                                        <div>
                                            {!! $notification->data['pesan'] ?? 'Notifikasi baru.' !!}
                                            <div class="{{ $notification->read_at ? 'text-muted' : 'text-primary' }}" style="font-size: 0.8em;">
                                                <i class="bi bi-clock"></i> {{ $notification->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @empty
                                <li><p class="dropdown-item text-center text-muted small py-3 mb-0">Tidak ada notifikasi baru.</p></li>
                            @endforelse
                            @if(Auth::user()->notifications()->count() > 0)
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item text-center text-primary small py-2" href="{{ route('notifikasi.index') }}">Lihat Semua Notifikasi</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    {{-- Dropdown Nama Pengguna --}}
                    <li class="nav-item dropdown">
                        <a id="navbarDropdownUser" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="navbarDropdownUser">
                            <a class="dropdown-item" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                            @if (Route::has('profile.edit'))
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person-circle me-2"></i>Profil Saya
                            </a>
                            @endif
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right me-2"></i>{{ __('Logout') }}
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @else  {{-- Ini untuk @guest (jika user belum login) --}}
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
                @endguest
            </ul>
        </div>
    </div>
</nav>

<main class="flex-shrink-0 py-4">
    <div class="container-fluid px-md-4">        
        @include('layouts.partials.alerts')
        @yield('content')
    </div>
</main>

<footer class="footer mt-auto py-3 bg-light border-top">
    <div class="container text-center">
        <span class="text-muted small">&copy; {{ date('Y') }} {{ config('app.name', 'Aplikasi Inventaris') }}.</span>
    </div>
</footer>

@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>