#!/bin/bash
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "=== Building Golden Bird Charcoal CMS ==="

mkdir -p "$SCRIPT_DIR/bin" "$SCRIPT_DIR/database" "$SCRIPT_DIR/public/uploads" "$SCRIPT_DIR/public/assets/images" /tmp

if [ ! -s "$SCRIPT_DIR/bin/frankenphp" ]; then
    echo "Downloading FrankenPHP binary..."
    curl -fsSL https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-linux-x86_64 -o "$SCRIPT_DIR/bin/frankenphp" || true
fi

chmod +x "$SCRIPT_DIR/bin/frankenphp" 2>/dev/null || true
chmod +x "$SCRIPT_DIR/bin/php" 2>/dev/null || true

PHP_CMD=""
if [ -x "$SCRIPT_DIR/bin/frankenphp" ]; then
    PHP_CMD="$SCRIPT_DIR/bin/frankenphp php-cli"
elif [ -x "$SCRIPT_DIR/bin/php" ]; then
    PHP_CMD="$SCRIPT_DIR/bin/php"
elif command -v php >/dev/null 2>&1; then
    PHP_CMD="$(command -v php)"
fi

if [ -n "$PHP_CMD" ]; then
    echo "Running database setup with: $PHP_CMD"
    $PHP_CMD "$SCRIPT_DIR/database/migrate.php" || true
    $PHP_CMD "$SCRIPT_DIR/database/seed_images.php" || true
fi

echo "=== Build Complete ==="
