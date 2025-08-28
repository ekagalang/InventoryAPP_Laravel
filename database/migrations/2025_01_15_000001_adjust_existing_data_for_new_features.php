<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Adjust existing barangs table if columns missing
        if (Schema::hasTable('barangs')) {
            if (!Schema::hasColumn('barangs', 'tipe_item')) {
                Schema::table('barangs', function (Blueprint $table) {
                    $table->enum('tipe_item', ['habis_pakai', 'aset'])->default('habis_pakai')->after('nama_barang');
                });
                
                // Update existing data - default semua ke habis_pakai
                DB::table('barangs')->whereNull('tipe_item')->update(['tipe_item' => 'habis_pakai']);
            }
            
            if (!Schema::hasColumn('barangs', 'stok_minimum')) {
                Schema::table('barangs', function (Blueprint $table) {
                    $table->integer('stok_minimum')->default(0)->after('stok');
                });
                
                // Set stok minimum = 10% dari stok current atau minimum 1
                DB::statement('UPDATE barangs SET stok_minimum = GREATEST(FLOOR(stok * 0.1), 1) WHERE stok_minimum = 0');
            }
        }

        // 2. Adjust maintenances table if columns missing  
        if (Schema::hasTable('maintenances')) {
            if (!Schema::hasColumn('maintenances', 'is_recurring')) {
                Schema::table('maintenances', function (Blueprint $table) {
                    $table->boolean('is_recurring')->default(false)->after('lampiran');
                    $table->integer('recurrence_interval')->nullable()->after('is_recurring');
                    $table->enum('recurrence_unit', ['days', 'weeks', 'months', 'years'])->nullable()->after('recurrence_interval');
                    $table->date('recurrence_end_date')->nullable()->after('recurrence_unit');
                    $table->integer('max_occurrences')->nullable()->after('recurrence_end_date');
                    $table->integer('current_occurrence')->default(1)->after('max_occurrences');
                });
                
                // Update existing maintenances - set as non-recurring
                DB::table('maintenances')->update(['is_recurring' => false, 'current_occurrence' => 1]);
            }
        }

        // 3. Create user permissions if missing
        $this->ensurePermissionsExist();
        
        // 4. Update user roles for new features
        $this->updateUserRolesForNewFeatures();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Generally don't reverse data adjustments in production
        // Only reverse if absolutely necessary
    }

    /**
     * Ensure required permissions exist
     */
    private function ensurePermissionsExist(): void
    {
        $permissions = [
            // Maintenance permissions
            'maintenance-manage',
            
            // Payment permissions  
            'payment-manage',
            
            // Laporan permissions
            'view-laporan-maintenance',
            
            // Advanced features
            'bulk-operations',
            'advanced-search',
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Update user roles for new features
     */
    private function updateUserRolesForNewFeatures(): void
    {
        // Give admin role all new permissions
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        if ($adminRole) {
            $newPermissions = DB::table('permissions')
                ->whereIn('name', [
                    'maintenance-manage', 'payment-manage', 'view-laporan-maintenance',
                    'bulk-operations', 'advanced-search'
                ])
                ->pluck('id');

            foreach ($newPermissions as $permissionId) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $adminRole->id,
                ]);
            }
        }

        // Give staff role some permissions
        $staffRole = DB::table('roles')->where('name', 'staff')->first();
        if ($staffRole) {
            $staffPermissions = DB::table('permissions')
                ->whereIn('name', ['view-laporan-maintenance', 'advanced-search'])
                ->pluck('id');

            foreach ($staffPermissions as $permissionId) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $staffRole->id,
                ]);
            }
        }
    }
};