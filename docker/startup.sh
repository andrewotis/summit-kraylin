#!/bin/bash

git config --global --add safe.directory /var/www/html

mkdir -p /var/www/html/storage/framework/{cache,sessions,testing,views}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

[ ! -d /var/www/html/vendor ] && composer install --no-interaction --ignore-platform-req=ext-calendar 2>/dev/null

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
