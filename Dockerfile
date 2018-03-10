FROM debian
ENV DEBIAN_FRONTEND noninteractive
RUN apt-get -qq update \
&& apt-get install -qq -y apt-transport-https apt-utils wget

RUN echo "deb https://packages.sury.org/php/ jessie main" > /etc/apt/sources.list.d/php.list \
&& wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add - \
&& echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list

RUN curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -

RUN apt-get update -qq -y \
&& apt-get install -qq -y \
curl \
vim \
ruby \
ruby-dev \
gem \
php7.1-fpm \
php7.1-cli \
php7.1-dev \
php7.1-curl \
php7.1-mcrypt \
php7.1-xdebug \
php7.1-fpm \
php7.1-xml \
php7.1-mbstring \
php7.1-mongodb \
nginx \
libsasl2-dev \
unzip \
sudo \
nodejs \
yarn \
&& apt-get autoremove -y

RUN curl -s http://getcomposer.org/installer | php -- --install-dir=/usr/bin \
&& mv /usr/bin/composer.phar /usr/bin/composer \
&& composer self-update

RUN yarn add sass-loader node-sass webpack-notifier @symfony/webpack-encore --dev

COPY ./files/php/mods-available/xdebug.ini /etc/php/7.1/mods-available/xdebug.ini

COPY ./files/nginx/default /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

WORKDIR /var/www
EXPOSE 80

CMD sh /var/www/dembelo/docker/scripts/init.sh