#!/bin/sh
set -e

# Pastikan storage & cache selalu writable, apapun permission asal dari host
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Lanjut jalankan command aslinya (start Apache)
exec "$@"
