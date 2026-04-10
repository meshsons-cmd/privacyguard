#!/bin/bash
echo "Starting Laravel setup..."

# Ensure database directory and file exist with proper permissions
mkdir -p database
touch database/database.sqlite
chown -R www-data:www-data /var/www/html/database
chmod -R 775 /var/www/html/database

# Ensure storage and cache have proper permissions
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/bootstrap/cache

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Cache config, routes, and views for production performance
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Apache..."
exec apache2-foreground