#!/bin/bash
# Golden Bird Charcoal CMS Startup Script for Cloud Run & Local Dev

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Ensure runtime directories exist
mkdir -p "$SCRIPT_DIR/bin" "$SCRIPT_DIR/database" "$SCRIPT_DIR/public/uploads" "$SCRIPT_DIR/public/assets/images" /tmp

if [ ! -s "$SCRIPT_DIR/bin/frankenphp" ]; then
    echo "Downloading FrankenPHP binary..."
    curl -fsSL https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-linux-x86_64 -o "$SCRIPT_DIR/bin/frankenphp" || true
fi

chmod +x "$SCRIPT_DIR/bin/frankenphp" 2>/dev/null || true
chmod +x "$SCRIPT_DIR/bin/php" 2>/dev/null || true

export PATH="$SCRIPT_DIR/bin:$PATH"

# 1. Start MariaDB / MySQL daemon if available
if command -v mariadbd >/dev/null 2>&1 || command -v mysqld >/dev/null 2>&1; then
    mkdir -p /run/mysqld /var/lib/mysql /var/log/mysql
    chown -R mysql:mysql /run/mysqld /var/lib/mysql 2>/dev/null || true
    if [ ! -d "/var/lib/mysql/mysql" ]; then
        mariadb-install-db --user=mysql --datadir=/var/lib/mysql > /dev/null 2>&1 || mysql_install_db --user=mysql --datadir=/var/lib/mysql > /dev/null 2>&1 || true
    fi
    if ! mysqladmin ping --silent > /dev/null 2>&1; then
        /usr/sbin/mariadbd --user=mysql --datadir=/var/lib/mysql > /dev/null 2>&1 &
        sleep 2
    fi
    mysql -e "CREATE DATABASE IF NOT EXISTS goldenbird_export_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'goldenbird_user'@'127.0.0.1' IDENTIFIED BY 'goldenbird_pass_2026!';
CREATE USER IF NOT EXISTS 'goldenbird_user'@'localhost' IDENTIFIED BY 'goldenbird_pass_2026!';
GRANT ALL PRIVILEGES ON goldenbird_export_db.* TO 'goldenbird_user'@'127.0.0.1';
GRANT ALL PRIVILEGES ON goldenbird_export_db.* TO 'goldenbird_user'@'localhost';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost';
FLUSH PRIVILEGES;" > /dev/null 2>&1 || true
fi

# 2. Determine PHP Runner
PHP_CLI=""
if [ -x "$SCRIPT_DIR/bin/frankenphp" ]; then
    PHP_CLI="$SCRIPT_DIR/bin/frankenphp php-cli"
elif [ -x "$SCRIPT_DIR/bin/php" ]; then
    PHP_CLI="$SCRIPT_DIR/bin/php"
elif command -v php >/dev/null 2>&1; then
    PHP_CLI="$(command -v php)"
fi

if [ -n "$PHP_CLI" ]; then
    echo "Running MySQL database initialization..."
    $PHP_CLI "$SCRIPT_DIR/database/migrate.php" || true
    $PHP_CLI "$SCRIPT_DIR/database/seed_golden_bird.php" || true
    $PHP_CLI "$SCRIPT_DIR/database/generate_charcoal_articles.php" || true
fi

# 3. Start Web Server on Port 3000
echo "Starting Golden Bird Charcoal Portal on port 3000..."
if [ -x "$SCRIPT_DIR/bin/frankenphp" ]; then
    exec "$SCRIPT_DIR/bin/frankenphp" php-server --listen :3000 --root "$SCRIPT_DIR/public"
elif [ -x "$SCRIPT_DIR/bin/php" ]; then
    exec "$SCRIPT_DIR/bin/php" -S 0.0.0.0:3000 -t "$SCRIPT_DIR/public" "$SCRIPT_DIR/public/index.php"
elif command -v php >/dev/null 2>&1; then
    exec php -S 0.0.0.0:3000 -t "$SCRIPT_DIR/public" "$SCRIPT_DIR/public/index.php"
else
    echo "FATAL: No PHP executable available."
    exit 1
fi
