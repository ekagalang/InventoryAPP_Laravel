<?php
/**
 * Production File Checker
 * Script to verify all required files exist in production
 */

echo "🔍 Checking Production Files...\n\n";

$requiredFiles = [
    // Controllers
    'app/Http/Controllers/Admin/RecurringPaymentController.php',
    'app/Http/Controllers/Admin/PaymentScheduleController.php',
    'app/Http/Controllers/Admin/MaintenanceScheduleController.php',
    
    // Models
    'app/Models/RecurringPayment.php',
    'app/Models/PaymentSchedule.php',
    'app/Models/MaintenanceSchedule.php',
    
    // Views
    'resources/views/admin/payments/index.blade.php',
    'resources/views/admin/payments/create.blade.php',
    'resources/views/admin/payments/edit.blade.php',
    'resources/views/admin/payments/show.blade.php',
    'resources/views/admin/payments/schedules.blade.php',
    'resources/views/components/advanced-search.blade.php',
    
    // Migrations
    'database/migrations/2025_08_28_020851_create_recurring_payments_table.php',
    'database/migrations/2025_08_28_020857_create_payment_schedules_table.php',
    'database/migrations/2025_08_28_015134_create_maintenance_schedules_table.php',
    'database/migrations/2025_08_28_015429_add_recurring_limit_to_maintenances_table.php',
];

$missing = [];
$found = [];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        $found[] = $file;
        echo "✅ $file\n";
    } else {
        $missing[] = $file;
        echo "❌ $file (MISSING)\n";
    }
}

echo "\n📊 SUMMARY:\n";
echo "✅ Found: " . count($found) . " files\n";
echo "❌ Missing: " . count($missing) . " files\n";

if (!empty($missing)) {
    echo "\n🚨 MISSING FILES:\n";
    foreach ($missing as $file) {
        echo "   - $file\n";
    }
    
    echo "\n💡 SOLUTIONS:\n";
    echo "1. Run: git pull origin main (or master)\n";
    echo "2. Run: composer dump-autoload\n";
    echo "3. Run: php artisan migrate\n";
    echo "4. Run: php artisan route:clear\n";
    echo "5. Run: php artisan config:clear\n";
    echo "6. Run: php artisan view:clear\n";
    
    exit(1);
} else {
    echo "\n🎉 All required files are present!\n";
    
    // Check if migrations have been run
    echo "\n🗃️ Checking database tables...\n";
    
    try {
        $pdo = new PDO("mysql:host=" . env('DB_HOST') . ";dbname=" . env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'));
        
        $tables = [
            'recurring_payments',
            'payment_schedules', 
            'maintenance_schedules'
        ];
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "✅ Table '$table' exists\n";
            } else {
                echo "❌ Table '$table' missing - Run: php artisan migrate\n";
            }
        }
        
    } catch (Exception $e) {
        echo "⚠️ Could not check database: " . $e->getMessage() . "\n";
    }
    
    exit(0);
}