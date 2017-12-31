#!/usr/bin/env bash

apt-get -y install apt-transport-https lsb-release ca-certificates

# PHP7
echo "deb https://packages.sury.org/php/ jessie main" > /etc/apt/sources.list.d/php.list
wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg

apt-get update
apt-get -y install git curl vim ruby php7.1-fpm php7.1-cli php7.1-dev php7.1-gd php7.1-curl php7.1-mcrypt php7.1-xdebug php7.1-memcache php7.1-intl php7.1-tidy php7.1-imap php7.1-imagick php7.1-fpm mongodb nginx imagemagick libsasl2-dev pkg-config unzip php7.1-xml php7.1-mbstring php7.1-mongodb

#mkdir -p /etc/php7/cli/
#mkdir -p /etc/php7/fpm/
#cp /vagrant/files/php/cli/php.ini /etc/php/7.1/cli/php.ini
#cp /vagrant/files/php/fpm/php.ini /etc/php/7.1/fpm/php.ini
#cp /vagrant/files/php/fpm/php-fpm.conf /etc/php/7.1/fpm/php-fpm.conf
#cp /vagrant/files/php/fpm/pool.d/www.conf /etc/php/7.1/fpm/pool.d/www.conf

curl -s http://getcomposer.org/installer | php -- --install-dir=/usr/bin && mv /usr/bin/composer.phar /usr/bin/composer
composer self-update

gem install sass

cp /var/www/dembelo/files/nginx/default /etc/nginx/sites-available/default
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

service nginx restart
service php7.1-fpm restart

systemctl enable php7.1-fpm

cd /vagrant/www
composer install

bin/console assetic:dump
bin/console cache:warmup
