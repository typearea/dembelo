#!/usr/bin/env bash

umask 0000

cd /var/www/dembelo/www
composer install
yarn run encore dev
bin/console assets:install web --symlink
bin/console cache:warmup

service nginx start
service php7.1-fpm start

tail -f /var/www/dembelo/www/var/logs/dev.log