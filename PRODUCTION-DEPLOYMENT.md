# 🚀 Production Deployment Guide

## ⚠️ IMPORTANT: Data Migration for Existing Production

**For existing production databases**, you need to migrate old data to match new features.

### 1. Backup First (CRITICAL!)

```bash
# Make script executable and backup
chmod +x backup-before-migration.sh

# Create backup (ALWAYS DO THIS FIRST!)
./backup-before-migration.sh
```

### 2. Migrate Data & Fix Routes

```bash
# Make script executable
chmod +x fix-production-routes.sh

# Run complete fix (includes data migration)
./fix-production-routes.sh
```

### 3. Manual Data Migration Steps

If you prefer manual control:

```bash
# 1. Check what will be changed (safe to run)
php artisan data:migrate --dry-run

# 2. Apply the changes
php artisan data:migrate

# 3. Update permissions
php artisan db:seed --class=RolesAndPermissionsSeeder --force
```

### 2. Manual Steps

```bash
# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Update autoload
composer dump-autoload --optimize

# Run migrations
php artisan migrate --force

# Cache for production
php artisan config:cache
php artisan route:cache
```

### 3. Verify Files Exist

```bash
# Check all required files
php check-production-files.php
```

### 4. Full Deployment

```bash
# For complete deployment
chmod +x deploy-production.sh
./deploy-production.sh
```

## Required Files Checklist

### Controllers
- [ ] `app/Http/Controllers/Admin/RecurringPaymentController.php`
- [ ] `app/Http/Controllers/Admin/PaymentScheduleController.php`
- [ ] `app/Http/Controllers/Admin/MaintenanceScheduleController.php`

### Models
- [ ] `app/Models/RecurringPayment.php`
- [ ] `app/Models/PaymentSchedule.php`
- [ ] `app/Models/MaintenanceSchedule.php`

### Views
- [ ] `resources/views/admin/payments/index.blade.php`
- [ ] `resources/views/admin/payments/create.blade.php`
- [ ] `resources/views/admin/payments/edit.blade.php`
- [ ] `resources/views/admin/payments/show.blade.php`
- [ ] `resources/views/admin/payments/schedules.blade.php`
- [ ] `resources/views/components/advanced-search.blade.php`

### Database
- [ ] Migration: `create_recurring_payments_table.php`
- [ ] Migration: `create_payment_schedules_table.php` 
- [ ] Migration: `create_maintenance_schedules_table.php`
- [ ] Migration: `add_recurring_limit_to_maintenances_table.php`

## Common Issues & Solutions

### Route Not Found
**Error**: `Route [admin.payments.index] not defined`
**Solution**: Run the fix script or clear caches manually

### Class Not Found 
**Error**: `Class 'App\Http\Controllers\Admin\RecurringPaymentController' not found`
**Solution**: 
```bash
composer dump-autoload --optimize
```

### Migration Issues
**Error**: `Table 'recurring_payments' doesn't exist`
**Solution**:
```bash
php artisan migrate --force
```

### View Not Found
**Error**: `View [admin.payments.index] not found`
**Solution**: Ensure all view files are uploaded and clear view cache:
```bash
php artisan view:clear
```

## Safety Features

The application now includes:

1. **Route Existence Checks**: Navigation only shows links if routes exist
2. **Fallback Routes**: Graceful handling of missing admin routes
3. **Custom Blade Directives**: `@route()` and `@safeRoute()` for safer templating

## Environment Variables

Ensure these are set in production:

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

## Post-Deployment Verification

1. Check homepage loads
2. Test login functionality
3. Verify admin navigation works
4. Test key features:
   - Barang management
   - Stock movements
   - Maintenance scheduling
   - Payment tracking (if deployed)

## Rollback Plan

If deployment fails:

```bash
# Put in maintenance mode
php artisan down

# Rollback git changes
git reset --hard HEAD~1

# Clear caches
php artisan config:clear
php artisan route:clear

# Bring back online  
php artisan up
```

## Support

If issues persist:
1. Check logs: `tail -f storage/logs/laravel.log`
2. Verify all files are uploaded
3. Ensure database is up to date
4. Check file permissions (755 for directories, 644 for files)

---

**Last Updated**: 2025-01-15
**Laravel Version**: 11.x