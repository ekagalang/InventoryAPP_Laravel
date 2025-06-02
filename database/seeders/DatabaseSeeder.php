<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create(); // Anda bisa komentari ini jika tidak butuh user factory bawaan

        // Panggil RolesAndPermissionsSeeder kita
        $this->call(RolesAndPermissionsSeeder::class); // TAMBAHKAN BARIS INI

        // Anda bisa memanggil seeder lain di sini jika ada
        // $this->call(BarangSeeder::class);
        // $this->call(KategoriSeeder::class);
    }
}
