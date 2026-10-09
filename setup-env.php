<?php

$envFile = '/var/www/html/.env';
$envExample = '/var/www/html/.env.example';

if (!file_exists($envFile)) {
    copy($envExample, $envFile);
}

$env = file_get_contents($envFile);

function setEnvValue(&$content, $key, $value) {
    if (preg_match("/^{$key}=.*/m", $content)) {
        $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
    } else {
        $content .= "\n{$key}={$value}";
    }
}

// App basics
setEnvValue($env, 'APP_NAME', '"Maha E-Seva ERP"');
setEnvValue($env, 'APP_ENV', 'production');
setEnvValue($env, 'APP_DEBUG', 'true');

// Port & URL
$port = getenv('PORT') ?: '80';
$railwayDomain = getenv('RAILWAY_PUBLIC_DOMAIN') ?: getenv('RAILWAY_STATIC_URL');
if ($railwayDomain) {
    setEnvValue($env, 'APP_URL', 'https://' . $railwayDomain);
} else {
    setEnvValue($env, 'APP_URL', 'http://0.0.0.0:' . $port);
}

// Database configuration
$mysqlHost = getenv('MYSQLHOST') ?: getenv('DB_HOST');
if ($mysqlHost) {
    echo "Configuring MySQL database...\n";
    setEnvValue($env, 'DB_CONNECTION', 'mysql');
    setEnvValue($env, 'DB_HOST', $mysqlHost);
    setEnvValue($env, 'DB_PORT', getenv('MYSQLPORT') ?: (getenv('DB_PORT') ?: '3306'));
    setEnvValue($env, 'DB_DATABASE', getenv('MYSQLDATABASE') ?: (getenv('DB_DATABASE') ?: 'railway'));
    setEnvValue($env, 'DB_USERNAME', getenv('MYSQLUSER') ?: (getenv('DB_USERNAME') ?: 'root'));
    setEnvValue($env, 'DB_PASSWORD', getenv('MYSQLPASSWORD') ?: (getenv('DB_PASSWORD') ?: ''));
} else {
    echo "Configuring SQLite database...\n";
    setEnvValue($env, 'DB_CONNECTION', 'sqlite');
    setEnvValue($env, 'DB_DATABASE', '/var/www/html/database/database.sqlite');
    setEnvValue($env, 'SESSION_DRIVER', 'file');
    setEnvValue($env, 'CACHE_STORE', 'file');
    setEnvValue($env, 'QUEUE_CONNECTION', 'sync');
}

file_put_contents($envFile, $env);
echo "Environment setup completed successfully.\n";
