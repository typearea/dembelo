FROM debian
ENV DEBIAN_FRONTEND noninteractive
RUN apt-get -qq update \
&& apt-get install -qq -y apt-transport-https apt-utils wget

RUN echo "deb https://packages.sury.org/php/ jessie main" > /etc/apt/sources.list.d/php.list \
&& wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg

RUN apt-get update -qq -y \
&& apt-get install -qq -y \
# git \
curl \
vim \
ruby \
ruby-dev \
gem \
php7.1-fpm \
php7.1-cli \
php7.1-dev \
# php7.1-gd \ # broken packages
php7.1-curl \
php7.1-mcrypt \
php7.1-xdebug \
#php7.1-memcache \
#php7.1-intl \
#php7.1-tidy \
#php7.1-imap \
# php7.1-imagick \
php7.1-fpm \
php7.1-xml \
php7.1-mbstring \
php7.1-mongodb \
nginx \
# imagemagick \
libsasl2-dev \
#pkg-config \
unzip \
&& apt-get autoremove -y

RUN curl -s http://getcomposer.org/installer | php -- --install-dir=/usr/bin \
&& mv /usr/bin/composer.phar /usr/bin/composer \
&& composer self-update

RUN gem install sass

COPY ./files/nginx/default /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
RUN /etc/init.d/nginx start \
&& /etc/init.d/nginx status
RUN service php7.1-fpm start

RUN useradd -ms /bin/bash dembelo

USER dembelo

WORKDIR /var/www
EXPOSE 80

CMD sh /var/www/dembelo/docker/init.sh