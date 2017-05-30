#!/usr/bin/env bash

# PHP7
echo 'deb http://packages.dotdeb.org jessie all' > /etc/apt/sources.list.d/dotdeb.list
curl http://www.dotdeb.org/dotdeb.gpg | apt-key add -

apt-get update
apt-get -y install git curl vim ruby php7.0-fpm php7.0-cli php7.0-dev php7.0-gd php7.0-curl php-apc php7.0-mcrypt php7.0-xdebug php7.0-memcache php7.0-intl php7.0-tidy php7.0-imap php7.0-imagick php7.0-fpm mongodb nginx imagemagick libsasl2-dev pkg-config unzip php7.0-xml php7.0-mbstring php7.0-mongodb

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
service php7.0-fpm restart

cd /vagrant/www
composer install

app/console assetic:dump
app/console cache:warmup

vendor/squizlabs/php_codesniffer/scripts/phpcs --config-set installed_paths www/vendor/escapestudios/symfony2-coding-standard
