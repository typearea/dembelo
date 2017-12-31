#!/usr/bin/env bash

cd /var/www/dembelo/www
composer install
bin/console assetic:dump
bin/console cache:warmup

tail -f ./var/logs/dev.log