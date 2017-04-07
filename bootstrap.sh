#!/usr/bin/env bash

apt-get update
apt-get -y install git curl vim ruby php5-fpm php5-cli php5-dev php5-gd php5-curl php-apc php5-mcrypt php5-xdebug php5-memcache php5-intl php5-tidy php5-imap php5-imagick php5-fpm mongodb nginx imagemagick libsasl2-dev pkg-config unzip

mkdir -p /etc/php5/cli/
mkdir -p /etc/php5/fpm/
cp /vagrant/files/php/cli/php.ini /etc/php5/cli/php.ini
cp /vagrant/files/php/fpm/php.ini /etc/php5/fpm/php.ini
cp /vagrant/files/php/fpm/php-fpm.conf /etc/php5/fpm/php-fpm.conf
cp /vagrant/files/php/fpm/pool.d/www.conf /etc/php5/fpm/pool.d/www.conf

curl -s http://getcomposer.org/installer | php -- --install-dir=/usr/bin && mv /usr/bin/composer.phar /usr/bin/composer
composer self-update

/usr/bin/pecl install --force mongodb

gem install sass

cp /vagrant/files/nginx/default /etc/nginx/sites-available/default
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

service nginx restart
service php5-fpm restart

cd /vagrant/www
composer install

app/console assetic:dump
app/console cache:warmup

vendor/squizlabs/php_codesniffer/scripts/phpcs --config-set installed_paths www/vendor/escapestudios/symfony2-coding-standard
