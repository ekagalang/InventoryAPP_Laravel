<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Barang;
use App\Models\Maintenance;
use App\Models\User;

class DataMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:migrate {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing data to match new features and schema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Data Migration for New Features...');
        
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        }

        // 1. Fix Barang Data
        $this->migrateBarangData($dryRun);
        
        // 2. Fix Maintenance Data
        $this->migrateMaintenanceData($dryRun);
        
        // 3. Fix User Permissions
        $this->migrateUserPermissions($dryRun);
        
        // 4. Clean up orphaned data
        $this->cleanupOrphanedData($dryRun);
        
        // 5. Update search indexes
        $this->updateSearchIndexes($dryRun);

        $this->info('✅ Data migration completed successfully!');
        
        if ($dryRun) {
            $this->info('💡 Run without --dry-run to apply changes');
        }
    }

    /**
     * Migrate Barang data for new features
     */
    private function migrateBarangData($dryRun = false)
    {
        $this->info('📦 Migrating Barang data...');
        
        // Check barangs with missing tipe_item
        $missingTipeItem = Barang::whereNull('tipe_item')->orWhere('tipe_item', '')->count();
        
        if ($missingTipeItem > 0) {
            $this->warn("Found {$missingTipeItem} barang(s) without tipe_item");
            
            if (!$dryRun) {
                Barang::whereNull('tipe_item')->orWhere('tipe_item', '')->update([
                    'tipe_item' => 'habis_pakai'
                ]);
                $this->info("✅ Updated {$missingTipeItem} barang(s) with default tipe_item");
            }
        }

        // Check barangs with stok_minimum = 0
        $zeroStokMinimum = Barang::where('stok_minimum', 0)->count();
        
        if ($zeroStokMinimum > 0) {
            $this->warn("Found {$zeroStokMinimum} barang(s) with zero stok_minimum");
            
            if (!$dryRun) {
                DB::statement('UPDATE barangs SET stok_minimum = GREATEST(FLOOR(stok * 0.1), 1) WHERE stok_minimum = 0');
                $this->info("✅ Updated stok_minimum for {$zeroStokMinimum} barang(s)");
            }
        }

        // Check for duplicate kode_barang
        $duplicates = DB::table('barangs')
            ->select('kode_barang', DB::raw('COUNT(*) as count'))
            ->groupBy('kode_barang')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->count() > 0) {
            $this->warn("Found {$duplicates->count()} duplicate kode_barang(s)");
            
            if (!$dryRun) {
                foreach ($duplicates as $duplicate) {
                    $barangs = Barang::where('kode_barang', $duplicate->kode_barang)->get();
                    foreach ($barangs->skip(1) as $index => $barang) {
                        $barang->update(['kode_barang' => $barang->kode_barang . '-' . ($index + 1)]);
                    }
                }
                $this->info("✅ Fixed duplicate kode_barang");
            }
        }
    }

    /**
     * Migrate Maintenance data
     */
    private function migrateMaintenanceData($dryRun = false)
    {
        $this->info('🔧 Migrating Maintenance data...');
        
        // Fix maintenances without user_id
        $missingUserId = Maintenance::whereNull('user_id')->count();
        
        if ($missingUserId > 0) {
            $this->warn("Found {$missingUserId} maintenance(s) without user_id");
            
            if (!$dryRun) {
                $adminUser = User::role('admin')->first();
                if ($adminUser) {
                    Maintenance::whereNull('user_id')->update(['user_id' => $adminUser->id]);
                    $this->info("✅ Assigned {$missingUserId} maintenance(s) to admin user");
                }
            }
        }

        // Fix maintenances with invalid status
        $invalidStatus = Maintenance::whereNotIn('status', ['Dijadwalkan', 'Selesai', 'Dibatalkan'])->count();
        
        if ($invalidStatus > 0) {
            $this->warn("Found {$invalidStatus} maintenance(s) with invalid status");
            
            if (!$dryRun) {
                Maintenance::whereNotIn('status', ['Dijadwalkan', 'Selesai', 'Dibatalkan'])
                    ->update(['status' => 'Dijadwalkan']);
                $this->info("✅ Fixed {$invalidStatus} maintenance(s) status");
            }
        }
    }

    /**
     * Migrate User Permissions
     */
    private function migrateUserPermissions($dryRun = false)
    {
        $this->info('👥 Migrating User permissions...');
        
        // Check users without roles
        $usersWithoutRoles = User::doesntHave('roles')->count();
        
        if ($usersWithoutRoles > 0) {
            $this->warn("Found {$usersWithoutRoles} user(s) without roles");
            
            if (!$dryRun) {
                User::doesntHave('roles')->each(function ($user) {
                    $user->assignRole('user'); // Default role
                });
                $this->info("✅ Assigned default role to {$usersWithoutRoles} user(s)");
            }
        }
    }

    /**
     * Cleanup orphaned data
     */
    private function cleanupOrphanedData($dryRun = false)
    {
        $this->info('🧹 Cleaning up orphaned data...');
        
        // Check orphaned stock_movements
        $orphanedMovements = DB::table('stock_movements')
            ->leftJoin('barangs', 'stock_movements.barang_id', '=', 'barangs.id')
            ->whereNull('barangs.id')
            ->count();
            
        if ($orphanedMovements > 0) {
            $this->warn("Found {$orphanedMovements} orphaned stock movement(s)");
            
            if (!$dryRun) {
                DB::table('stock_movements')
                    ->leftJoin('barangs', 'stock_movements.barang_id', '=', 'barangs.id')
                    ->whereNull('barangs.id')
                    ->delete();
                $this->info("✅ Cleaned up {$orphanedMovements} orphaned stock movement(s)");
            }
        }
    }

    /**
     * Update search indexes
     */
    private function updateSearchIndexes($dryRun = false)
    {
        $this->info('🔍 Updating search indexes...');
        
        if (!$dryRun) {
            // Clear view cache to refresh any cached queries
            \Artisan::call('view:clear');
            $this->info('✅ Search indexes updated');
        }
    }
}
