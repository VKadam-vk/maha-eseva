#!/bin/bash

echo "=== STARTING MAHA E-SEVA ERP IN CONTAINER ==="

PORT="${PORT:-80}"
echo "Configuring application for Port: $PORT"

# Ensure essential directories exist with full permissions
mkdir -p /var/www/html/storage/app/private/documents \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/www/html/database

touch /var/www/html/database/database.sqlite
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Run safe environment setup
php /var/www/html/setup-env.php

# Generate APP_KEY if missing
if ! grep -q "^APP_KEY=base64" /var/www/html/.env; then
    echo "Generating application encryption key..."
    php artisan key:generate --force
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force || echo "Migration notice: continuing..."

# Seed initial roles, admin, and services
echo "Seeding default data (services, admin accounts)..."
php artisan db:seed --force || echo "Seed notice: continuing..."

# Symlink public storage
php artisan storage:link || true

# Clear cached configs to avoid stale data
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Configure Apache
grep -q "ServerName localhost" /etc/apache2/apache2.conf || echo "ServerName localhost" >> /etc/apache2/apache2.conf
echo "Listen 0.0.0.0:$PORT" > /etc/apache2/ports.conf

cat <<EOF > /etc/apache2/sites-available/000-default.conf
<VirtualHost *:$PORT>
    ServerName localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

echo "Starting Apache web server on 0.0.0.0:$PORT..."
exec apache2-foreground
