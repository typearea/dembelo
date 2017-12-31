#!/usr/bin/env bash

sudo -u www-data cd /var/www/dembelo/www \
&& composer install \
&& bin/console assetic:dump \
&& bin/console cache:warmup

service nginx start
service php7.1-fpm start

tail -f /var/www/dembelo/www/var/logs/dev.log