#!/bin/bash

# Port configuration (Railway / Render / Cloud platforms pass $PORT)
PORT="${PORT:-80}"
echo "Configuring Apache to listen on port: $PORT"

echo "Listen $PORT" > /etc/apache2/ports.conf
cat <<EOF > /etc/apache2/sites-available/000-default.conf
<VirtualHost *:$PORT>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Ensure .env exists
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Ensure storage and bootstrap/cache directories exist with write permissions
mkdir -p /var/www/html/storage/app/private/documents \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/www/html/database

chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Database auto-configuration:
# If Railway MySQL environment variables are present, configure MySQL;
# Otherwise, default to SQLite for zero-config instant testing.
if [ -n "$MYSQLHOST" ] || [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Configuring MySQL database connection..."
    sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=mysql/g" /var/www/html/.env
    if [ -n "$MYSQLHOST" ]; then
        sed -i "s/DB_HOST=.*/DB_HOST=$MYSQLHOST/g" /var/www/html/.env
        sed -i "s/DB_PORT=.*/DB_PORT=$MYSQLPORT/g" /var/www/html/.env
        sed -i "s/DB_DATABASE=.*/DB_DATABASE=$MYSQLDATABASE/g" /var/www/html/.env
        sed -i "s/DB_USERNAME=.*/DB_USERNAME=$MYSQLUSER/g" /var/www/html/.env
        sed -i "s/DB_PASSWORD=.*/DB_PASSWORD='$MYSQLPASSWORD'/g" /var/www/html/.env
    fi
else
    echo "Configuring standalone SQLite database for instant testing..."
    touch /var/www/html/database/database.sqlite
    chmod 666 /var/www/html/database/database.sqlite
    sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=sqlite/g" /var/www/html/.env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=\/var\/www\/html\/database\/database.sqlite/g" /var/www/html/.env
    sed -i "s/SESSION_DRIVER=.*/SESSION_DRIVER=file/g" /var/www/html/.env
    sed -i "s/CACHE_STORE=.*/CACHE_STORE=file/g" /var/www/html/.env
    sed -i "s/QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/g" /var/www/html/.env
fi

# Ensure APP_KEY exists
if ! grep -q "^APP_KEY=base64" /var/www/html/.env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Set production env settings
sed -i "s/APP_ENV=.*/APP_ENV=production/g" /var/www/html/.env
sed -i "s/APP_DEBUG=.*/APP_DEBUG=true/g" /var/www/html/.env

# Run database migrations and seed default data
echo "Running database migrations..."
php artisan migrate --force

echo "Seeding default services, roles, and admin accounts..."
php artisan db:seed --force

# Symlink public storage
php artisan storage:link || true

# Clear cache to avoid stale configs
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "Starting Apache web server on port $PORT..."
exec apache2-foreground
