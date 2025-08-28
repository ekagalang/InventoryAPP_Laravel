#!/bin/bash

# Laravel Production Deployment Script
echo "🚀 Starting Laravel Production Deployment..."

# 1. Put application in maintenance mode
echo "⏳ Putting application in maintenance mode..."
php artisan down

# 2. Pull latest changes
echo "📥 Pulling latest changes from repository..."
git pull origin main 2>/dev/null || git pull origin master

# 3. Update composer dependencies 
echo "📦 Updating composer dependencies..."
composer install --optimize-autoloader --no-dev --quiet

# 4. Update NPM dependencies and build assets (if applicable)
if [ -f "package.json" ]; then
    echo "🎨 Building frontend assets..."
    npm ci --silent
    npm run build --silent
fi

# 5. Run database migrations
echo "🗃️ Running database migrations..."
php artisan migrate --force

# 6. Clear all caches
echo "🧹 Clearing application caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan queue:restart

# 7. Cache configuration and routes for production
echo "⚡ Caching configuration for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Set proper permissions
echo "🔐 Setting proper file permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 9. Bring application back online
echo "🟢 Bringing application back online..."
php artisan up

echo "✅ Production deployment completed successfully!"
echo "🌐 Application is now live!"

# Optional: Run a health check
if command -v curl &> /dev/null; then
    echo "🏥 Running health check..."
    curl -f -s -o /dev/null "$APP_URL" && echo "✅ Health check passed" || echo "❌ Health check failed"
fi