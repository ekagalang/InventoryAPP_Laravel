# 📋 Production Migration Summary

## What Will Be Changed in Your Existing Data

### 🔄 Data Adjustments

#### 1. **Barang Table Updates**
- **Missing `tipe_item`**: All barang without tipe_item → Set to `habis_pakai`
- **Zero `stok_minimum`**: Calculate as 10% of current stock (minimum 1)
- **Duplicate `kode_barang`**: Add suffix `-1`, `-2`, etc to duplicates

#### 2. **Maintenance Table Updates**  
- **Missing `user_id`**: Assign to first admin user
- **Invalid status**: Fix any status not in [Dijadwalkan, Selesai, Dibatalkan]
- **New columns**: Add recurring maintenance fields (default: non-recurring)

#### 3. **User Permissions**
- **Users without roles**: Assign default 'user' role
- **New permissions**: Add maintenance-manage, payment-manage, advanced-search, bulk-operations
- **Role updates**: Give admin all new permissions, staff gets view permissions

#### 4. **Data Cleanup**
- **Orphaned stock movements**: Remove movements for deleted barangs
- **Search indexes**: Refresh cached queries

### 🆕 New Database Tables

The migration will create these new tables:
- `recurring_payments` - For tracking recurring payments (IPL, platform fees, etc)
- `payment_schedules` - Individual payment schedule entries
- `maintenance_schedules` - Recurring maintenance schedules

### ⚠️ **SAFETY FIRST**

**ALWAYS backup before migration:**
```bash
./backup-before-migration.sh
```

### 🚀 **Migration Commands**

**Option 1: Full Automated (Recommended)**
```bash
./fix-production-routes.sh
```

**Option 2: Step by Step**
```bash
# 1. Backup
./backup-before-migration.sh

# 2. Check what will change (safe)
php artisan data:migrate --dry-run

# 3. Run migrations
php artisan migrate --force
php artisan data:migrate

# 4. Update permissions
php artisan db:seed --class=RolesAndPermissionsSeeder --force

# 5. Clear caches
php artisan config:clear && php artisan route:clear
php artisan config:cache && php artisan route:cache
```

### 📊 **Before vs After**

| Feature | Before | After |
|---------|--------|-------|
| Search | Basic filter only | Advanced multi-field search with save/load |
| Bulk Operations | None | Select multiple, bulk delete/update/export |
| Stock Management | Manual only | Advanced filtering, low stock alerts |
| Maintenance | Basic tracking | Recurring schedules, cost tracking |
| Payments | None | Full recurring payment system |
| User Experience | Desktop focused | Mobile responsive design |
| Security | Basic | Rate limiting, permission-based UI |

### 🔍 **Verification Steps**

After migration, verify:

1. **Homepage loads** ✓
2. **Login works** ✓  
3. **Barang list shows advanced search** ✓
4. **Bulk operations work** ✓
5. **Maintenance scheduling works** ✓
6. **New payment menu appears** ✓
7. **Reports show combined data** ✓

### 🆘 **Rollback Plan**

If something goes wrong:

```bash
# Navigate to backup directory
cd backups/[timestamp]

# Restore database
./restore.sh

# Clear caches
php artisan config:clear
php artisan route:clear
```

### 📞 **Need Help?**

Common issues and solutions are in `PRODUCTION-DEPLOYMENT.md`

**Migration Status Check:**
```bash
php artisan data:migrate --dry-run
```

**Route Verification:**
```bash
php artisan route:list --name=admin.payments
```

---

**Remember**: Your existing data will be preserved and enhanced, not lost! ✨