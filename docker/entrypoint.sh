#!/bin/sh
set -e

# Fix permissions for storage and bootstrap/cache
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public/students/photos \
         /var/www/html/storage/app/public/parents/photos \
         /var/www/html/storage/app/public/student-card \
         /var/www/html/storage/app/public/uploads/logos/large \
         /var/www/html/storage/app/public/uploads/logos/small \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symlink if missing
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link --force || true
fi

# Execute passed command (e.g. supervisord or queue:work)
exec "$@"

