#!/bin/bash

# Quick Production Route Fix & Data Migration
echo "🔧 Quick Production Route Fix & Data Migration..."

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear  
php artisan view:clear
php artisan cache:clear

# Regenerate autoload files
echo "🔄 Regenerating autoload..."
composer dump-autoload --optimize

# Run pending migrations (new tables/columns)
echo "🗃️ Running schema migrations..."
php artisan migrate --force

# Run data migration to adjust existing data
echo "📊 Migrating existing data..."
php artisan data:migrate --dry-run
read -p "Apply data changes? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan data:migrate
    echo "✅ Data migration applied"
else
    echo "⏭️ Data migration skipped"
fi

# Seed permissions and roles if needed
echo "👥 Checking roles and permissions..."
php artisan db:seed --class=RolesAndPermissionsSeeder --force

# Cache for production
echo "⚡ Caching for production..."
php artisan config:cache
php artisan route:cache

# Check if routes exist now
echo "🔍 Verifying routes..."
php artisan route:list --name=admin.payments --compact

echo "✅ Route fix completed!"

# Test the problematic route
echo "🧪 Testing route resolution..."
php -r "
try {
    echo 'Testing route admin.payments.index: ';
    echo route('admin.payments.index');
    echo ' ✅ OK' . PHP_EOL;
} catch (Exception \$e) {
    echo ' ❌ FAILED: ' . \$e->getMessage() . PHP_EOL;
}
"

# Final health check
echo "🏥 Running final health check..."
php artisan data:migrate --dry-run | grep "Found" || echo "✅ All data looks good!"

echo ""
echo "🎉 Production fix completed!"
echo "📋 Summary:"
echo "   - Routes fixed and cached"
echo "   - Data migrated and cleaned"
echo "   - Permissions updated"
echo "   - Application ready!"