<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User; // Import model User jika Anda ingin membuat user admin di sini

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat Permissions Dasar untuk setiap modul
        $permissions = [
            // Barang
            'barang-list', 'barang-create', 'barang-edit', 'barang-delete', 'barang-show',
            // Kategori
            'kategori-list', 'kategori-create', 'kategori-edit', 'kategori-delete',
            // Unit
            'unit-list', 'unit-create', 'unit-edit', 'unit-delete',
            // Lokasi
            'lokasi-list', 'lokasi-create', 'lokasi-edit', 'lokasi-delete',
            // Stock Movement (Pergerakan Stok)
            'stok-pergerakan-list', 'stok-masuk-create', 'stok-keluar-create',
            // Manajemen Pengguna (Nanti)
            'user-list', 'user-create', 'user-edit', 'user-delete',
            // Manajemen Role & Permission (Nanti, hanya untuk super admin)
            'role-permission-manage',
            'view-audit-trail',

            // Pengajuan Barang
            'pengajuan-barang-list-own',    // Melihat daftar pengajuan milik sendiri
            'pengajuan-barang-create',      // Membuat pengajuan barang baru
            'pengajuan-barang-cancel-own',  // Membatalkan pengajuan milik sendiri (jika belum diproses)
            'pengajuan-barang-list-all',    // Melihat semua pengajuan barang (untuk Admin/Approver)
            'pengajuan-barang-approve',     // Menyetujui/Menolak pengajuan barang
            'pengajuan-barang-process',     // Memproses pengajuan yang sudah disetujui (mengeluarkan barang)
            'pengajuan-barang-return',

            // Dashboard & Laporan
            'view-dashboard',           // TAMBAHKAN INI
            // Nanti kita tambahkan untuk laporan spesifik
            // 'view-laporan-stok',
            // 'view-laporan-barang-masuk',
            // 'view-laporan-barang-keluar',

            // Laporan
            'view-laporan-stok',           // TAMBAHKAN INI
            // 'view-laporan-barang-masuk',
            // 'view-laporan-barang-keluar',

            'view-laporan-barang-masuk',   // TAMBAHKAN INI
            'view-laporan-barang-keluar',
            'stok-koreksi',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]); // Gunakan firstOrCreate untuk menghindari duplikat
        }
        $this->command->info('Permissions created successfully.');

        // Buat Role Admin dan berikan semua permission
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all()); // Admin dapat semua permission
        $this->command->info('Admin role created and given all permissions.');

        // Buat Role Staf Gudang
        $stafGudangRole = Role::firstOrCreate(['name' => 'StafGudang']); // Atau 'Staf Gudang' jika ingin spasi
        $stafGudangRole->givePermissionTo([
            'barang-list', 'barang-create', 'barang-edit', 'barang-show', // Staf gudang bisa kelola barang
            'kategori-list', 'kategori-create', // Staf gudang bisa kelola kategori
            'unit-list', 'unit-create',       // Staf gudang bisa kelola unit
            'lokasi-list', 'lokasi-create',     // Staf gudang bisa kelola lokasi
            'stok-pergerakan-list', 'stok-masuk-create', 'stok-keluar-create', // Staf gudang bisa kelola stok

            // ... permission staf gudang yang sudah ada ...
            'pengajuan-barang-list-own',
            'pengajuan-barang-create',
            'pengajuan-barang-cancel-own',
            'pengajuan-barang-list-all',    // Staf gudang juga bisa lihat semua pengajuan
            'pengajuan-barang-approve',     // Staf gudang bisa approve
            'pengajuan-barang-process',     // Staf gudang bisa proses
            'pengajuan-barang-return',
            // ... permission StafGudang yang sudah ada ...
            'view-dashboard', // TAMBAHKAN INI
            'view-laporan-stok', // TAMBAHKAN INI
            'view-laporan-barang-masuk', // TAMBAHKAN INI
            'view-laporan-barang-keluar', // TAMBAHKAN INI
            'stok-koreksi',
        ]);
        $this->command->info('StafGudang role created and given specific permissions.');
        
        // (Opsional) Buat Role Viewer (Hanya Lihat)
        $viewerRole = Role::firstOrCreate(['name' => 'Viewer']);
        $viewerRole->givePermissionTo([
            'barang-list', 'barang-show',
            'kategori-list',
            'unit-list',
            'lokasi-list',
            'stok-pergerakan-list',

            // ... permission viewer yang sudah ada ...
            'pengajuan-barang-list-own',
            'pengajuan-barang-create',
            'pengajuan-barang-cancel-own',

            // ... permission StafGudang yang sudah ada ...
            'view-dashboard', // TAMBAHKAN INI
        ]);
        $this->command->info('Viewer role created and given view permissions.');


        // (Opsional) Membuat User Admin Default jika belum ada
        // Anda bisa mengganti email dan passwordnya.
        // Pastikan user ini sudah ter-register atau buat baru.
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@basstrainingacademy.com'], // Kriteria pencarian
            [                             // Data jika user baru dibuat
                'name' => 'Admin User',
                'password' => bcrypt('Samphistik@7'), // Ganti dengan password yang aman
                'email_verified_at' => now(), // Langsung set terverifikasi
            ]
        );
        $adminUser->assignRole($adminRole); // Berikan peran Admin ke user ini
        $this->command->info('Admin user created/found and assigned Admin role.');

        // Anda bisa membuat user lain dan memberikan peran 'StafGudang' jika perlu
        $stafUser = User::firstOrCreate(
            ['email' => 'ekagalang@basstrainingacademy.com'],
            [
                'name' => 'Staf Gudang',
                'password' => bcrypt('Samphistik@7'),
                'email_verified_at' => now(),
            ]
        );
        $stafUser->assignRole($stafGudangRole);
        $this->command->info('StafGudang user created/found and assigned StafGudang role.');

    }
}