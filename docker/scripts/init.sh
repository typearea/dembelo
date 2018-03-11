#!/usr/bin/env bash

umask 0000

cd /var/www/dembelo/www
composer install
bin/console assets:install web --symlink
bin/console cache:warmup

service nginx start
service php7.1-fpm start

yarn run encore dev --watch