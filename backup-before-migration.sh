#!/bin/bash

# Backup Database Before Migration
echo "💾 Creating database backup before migration..."

# Get database credentials from .env
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)
DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)

# Create backup directory
BACKUP_DIR="backups/$(date +%Y-%m-%d_%H-%M-%S)"
mkdir -p $BACKUP_DIR

echo "📁 Backup directory: $BACKUP_DIR"

# 1. Backup entire database
echo "🗃️ Backing up database structure and data..."
mysqldump -h$DB_HOST -u$DB_USERNAME -p$DB_PASSWORD --single-transaction --routines --triggers $DB_DATABASE > $BACKUP_DIR/full_backup.sql

if [ $? -eq 0 ]; then
    echo "✅ Database backup completed: $BACKUP_DIR/full_backup.sql"
else
    echo "❌ Database backup failed!"
    exit 1
fi

# 2. Backup critical tables separately
echo "📊 Backing up critical tables..."

# Backup barangs table
mysqldump -h$DB_HOST -u$DB_USERNAME -p$DB_PASSWORD --single-transaction $DB_DATABASE barangs > $BACKUP_DIR/barangs_backup.sql

# Backup maintenances table
mysqldump -h$DB_HOST -u$DB_USERNAME -p$DB_PASSWORD --single-transaction $DB_DATABASE maintenances > $BACKUP_DIR/maintenances_backup.sql

# Backup stock_movements table
mysqldump -h$DB_HOST -u$DB_USERNAME -p$DB_PASSWORD --single-transaction $DB_DATABASE stock_movements > $BACKUP_DIR/stock_movements_backup.sql

# Backup users and permissions
mysqldump -h$DB_HOST -u$DB_USERNAME -p$DB_PASSWORD --single-transaction $DB_DATABASE users roles permissions role_has_permissions model_has_roles model_has_permissions > $BACKUP_DIR/users_permissions_backup.sql

echo "✅ Critical tables backed up"

# 3. Export data statistics before migration
echo "📈 Creating data statistics report..."

mysql -h$DB_HOST -u$DB_USERNAME -p$DB_PASSWORD $DB_DATABASE << EOF > $BACKUP_DIR/data_statistics.txt
SELECT 'BARANG STATISTICS' as category;
SELECT COUNT(*) as total_barangs FROM barangs;
SELECT tipe_item, COUNT(*) as count FROM barangs GROUP BY tipe_item;
SELECT COUNT(*) as barangs_without_stok_minimum FROM barangs WHERE stok_minimum = 0 OR stok_minimum IS NULL;

SELECT 'MAINTENANCE STATISTICS' as category;
SELECT COUNT(*) as total_maintenances FROM maintenances;
SELECT status, COUNT(*) as count FROM maintenances GROUP BY status;
SELECT COUNT(*) as maintenances_without_user FROM maintenances WHERE user_id IS NULL;

SELECT 'USER STATISTICS' as category;
SELECT COUNT(*) as total_users FROM users;

SELECT 'STOCK MOVEMENT STATISTICS' as category;
SELECT COUNT(*) as total_movements FROM stock_movements;
SELECT tipe_pergerakan, COUNT(*) as count FROM stock_movements GROUP BY tipe_pergerakan;
EOF

echo "✅ Data statistics saved to: $BACKUP_DIR/data_statistics.txt"

# 4. Create restore script
cat > $BACKUP_DIR/restore.sh << 'EOL'
#!/bin/bash

echo "🔄 Restoring database from backup..."

# Get database credentials from .env
DB_HOST=$(grep DB_HOST ../../.env | cut -d '=' -f2)
DB_DATABASE=$(grep DB_DATABASE ../../.env | cut -d '=' -f2)
DB_USERNAME=$(grep DB_USERNAME ../../.env | cut -d '=' -f2)
DB_PASSWORD=$(grep DB_PASSWORD ../../.env | cut -d '=' -f2)

# Restore full database
read -p "⚠️  This will OVERWRITE the current database. Continue? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    mysql -h$DB_HOST -u$DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < full_backup.sql
    
    if [ $? -eq 0 ]; then
        echo "✅ Database restored successfully!"
        echo "🔄 Don't forget to run: php artisan migrate"
    else
        echo "❌ Database restore failed!"
        exit 1
    fi
else
    echo "❌ Restore cancelled"
fi
EOL

chmod +x $BACKUP_DIR/restore.sh

echo "✅ Restore script created: $BACKUP_DIR/restore.sh"

# 5. Compress backup
echo "📦 Compressing backup..."
tar -czf $BACKUP_DIR.tar.gz $BACKUP_DIR/
BACKUP_SIZE=$(du -sh $BACKUP_DIR.tar.gz | cut -f1)

echo ""
echo "🎉 Backup completed successfully!"
echo "📁 Location: $BACKUP_DIR.tar.gz"
echo "📏 Size: $BACKUP_SIZE"
echo ""
echo "💡 To restore:"
echo "   1. Extract: tar -xzf $BACKUP_DIR.tar.gz"
echo "   2. Run: cd $BACKUP_DIR && ./restore.sh"
echo ""
echo "🚀 Now you can safely run migrations!"