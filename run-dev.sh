#!/bin/bash
# Ensure MariaDB is running
if command -v mariadbd >/dev/null 2>&1 || command -v mysqld >/dev/null 2>&1; then
    mkdir -p /run/mysqld /var/lib/mysql
    if ! mysqladmin ping --silent > /dev/null 2>&1; then
        /usr/sbin/mariadbd --user=mysql --datadir=/var/lib/mysql > /dev/null 2>&1 &
        sleep 1
    fi
fi
exec php -S 0.0.0.0:3000 -t public public/index.php
