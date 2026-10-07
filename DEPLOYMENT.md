# Deployment checklist

## cPanel / shared hosting

1. Use PHP 8.2 or newer with `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `curl`, `gd`, and `zip` enabled.
2. Point the domain Document Root to `public/`. If the host requires the project root, keep the root `.htaccess` in place.
3. Upload the application files without `storage/logs`, `storage/cache`, `bin/php`, or `bin/frankenphp` from local development.
4. Create a MySQL/MariaDB database and a dedicated user with privileges only on that database.
5. Set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, and `APP_URL` in the hosting environment. Do not commit credentials.
6. Import `database/schema.sql` from phpMyAdmin, or run `php database/migrate.php` once from a protected CLI environment.
7. Set `storage/`, `storage/cache/`, `storage/logs/`, and `public/uploads/` writable by PHP. Do not make the whole project writable.
8. Visit `/admin/login`, create or use the administrator configured during installation, and remove or lock `public/install.php` after setup.
9. Confirm the public home page, an admin create form, media upload, login throttling, mail settings, and HTTPS redirect on staging before production.

## Important

- Never run seed scripts on production unless their contents have been reviewed. Some seed scripts can replace existing content.
- The application requires a real MySQL/MariaDB PHP driver. SQLite is not supported by the schema or runtime.
- Keep backups outside the public directory and rotate database credentials after deployment.
