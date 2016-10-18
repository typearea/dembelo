#!/usr/bin/env bash
export SYMFONY_ENV=prod
mkdir release
git checkout-index -a -f --prefix release/
cd release
composer --working-dir="www" remove alcaeus/mongo-php-adapter mongodb/mongodb
composer --working-dir="www" install --no-dev --optimize-autoloader
echo "zip release: ", $RELEASE_FILENAME
zip -r $RELEASE_FILENAME ./
ls -l $RELEASE_FILENAME