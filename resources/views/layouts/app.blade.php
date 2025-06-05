<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Aplikasi Inventaris')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">InventarisApp</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>

                    @can('barang-list')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('barang.*') ? 'active' : '' }}" href="{{ route('barang.index') }}">Barang</a>
                        </li>
                    @endcan

                    @canany(['kategori-list', 'unit-list', 'lokasi-list'])
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->is('kategori*') || request()->is('unit*') || request()->is('lokasi*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Data Master</a>
                            <ul class="dropdown-menu">
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
                    @endcanany

                    @canany(['stok-masuk-create', 'stok-keluar-create', 'stok-pergerakan-list'])
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('stok.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Manajemen Stok</a>
                            <ul class="dropdown-menu">
                                @can('stok-masuk-create')
                                    <li><a class="dropdown-item" href="{{ route('stok.masuk.create') }}">Catat Barang Masuk</a></li>
                                @endcan
                                @can('stok-keluar-create')
                                    <li><a class="dropdown-item" href="{{ route('stok.keluar.create') }}">Catat Barang Keluar</a></li>
                                @endcan
                                @can('stok-koreksi')
                                    <li><a class="dropdown-item" href="{{ route('stok.koreksi.create') }}">Koreksi Stok</a></li>
                                @endcan
                                @can('stok-pergerakan-list')
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('stok.pergerakan.index') }}">Riwayat Pergerakan</a></li>
                                @endcan
                                @can('pengajuan-barang-list-all')
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pengajuan.barang.index') }}">Kelola Pengajuan Barang</a></li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @canany(['user-list', 'role-permission-manage'])
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Pengaturan Admin</a>
                            <ul class="dropdown-menu">
                                @can('user-list')
                                    <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Manajemen User</a></li>
                                @endcan
                                @can('role-permission-manage')
                                    <li><a class="dropdown-item" href="{{ route('admin.roles.index') }}">Manajemen Peran</a></li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany

                    @can('pengajuan-barang-create')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('pengajuan.barang.create') }}">Buat Pengajuan Barang</a>
                        </li>
                    @endcan

                    @can('pengajuan-barang-list-own')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('pengajuan.barang.index') }}">Pengajuan Saya</a>
                        </li>
                    @endcan

                    @canany(['view-laporan-stok', 'view-laporan-barang-masuk', 'view-laporan-barang-keluar'])
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->is('laporan/*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Laporan</a>
                            <ul class="dropdown-menu">
                                @can('view-laporan-stok')
                                    <li><a class="dropdown-item" href="{{ route('laporan.stok.barang') }}">Laporan Stok Barang</a></li>
                                @endcan
                                @can('view-laporan-barang-masuk')
                                    <li><a class="dropdown-item" href="{{ route('laporan.barang.masuk') }}">Laporan Barang Masuk</a></li>
                                @endcan
                                @can('view-laporan-barang-keluar')
                                    <li><a class="dropdown-item" href="{{ route('laporan.barang.keluar') }}">Laporan Barang Keluar</a></li>
                                @endcan
                            </ul>
                        </li>
                    @endcanany
                @endauth
            </ul>

            <ul class="navbar-nav ms-auto">
                @auth
                    @if(Auth::user()->hasPermissionTo('view-dashboard') && isset($unreadNotificationsCount))
                        <li class="nav-item dropdown">
                            <a class="nav-link" href="#" data-bs-toggle="dropdown" title="Notifikasi">
                                <i class="bi bi-bell-fill position-relative fs-5">
                                    @if($unreadNotificationsCount > 0)
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.6em;">{{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}</span>
                                    @endif
                                </i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow mt-2" style="min-width: 350px; max-height: 400px; overflow-y: auto;">
                                <li class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Notifikasi</h6>
                                    @if($unreadNotificationsCount > 0)
                                        <form method="POST" action="{{ route('notifikasi.markAllAsRead') }}">
                                            @csrf
                                            <button class="btn btn-link btn-sm p-0 text-primary">Tandai semua dibaca</button>
                                        </form>
                                    @endif
                                </li>
                                @forelse($unreadNotifications as $notif)
                                    <li>
                                        <a class="dropdown-item small {{ $notif->read_at ? 'text-muted' : 'fw-bold' }}" href="{{ route('notifikasi.markAsReadAndRedirect', ['id' => $notif->id, 'url' => $notif->data['url'] ?? route('dashboard')]) }}">
                                            <i class="bi bi-exclamation-triangle-fill me-2 {{ $notif->read_at ? 'text-secondary' : 'text-warning' }}"></i>
                                            {!! $notif->data['pesan'] ?? 'Notifikasi baru' !!}
                                            <div class="text-muted small"><i class="bi bi-clock"></i> {{ $notif->created_at->diffForHumans() }}</div>
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

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">{{ Auth::user()->name }}</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profil Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST">@csrf
                                    <button class="dropdown-item text-danger" type="submit">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    @if (Route::has('register'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                    @endif
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="flex-fill container py-4">
    @yield('content')
</main>

<footer class="bg-light text-center text-muted py-3 mt-auto border-top small">
    &copy; {{ date('Y') }} InventarisApp | Dibuat oleh Tim IT
</footer>

@stack('scripts')
</body>
</html>
