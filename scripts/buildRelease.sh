#!/usr/bin/env bash
export SYMFONY_ENV=prod
mkdir release
cd release
git clone git@github.com:typearea/dembelo.git
cd dembelo
composer remove alcaeus/mongo-php-adapter mongodb/mongodb
composer install --no-dev --optimize-autoloader
rm -r .git
echo "zip release: ", $RELEASE_FILENAME
zip -r $RELEASE_FILENAME ./
ls -l $RELEASE_FILENAME