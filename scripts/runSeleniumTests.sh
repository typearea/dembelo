#!/usr/bin/env bash

cd ./www/

php bin/console server:start -v
bin/phantomjs --webdriver=8910 --webdriver-loglevel=ERROR &
bin/console assetic:dump --env=selenium
bin/console cache:warmup --env=selenium -q

bin/console dembelo:install --purge-db -e selenium

bin/phpunit -c app/phpunitselenium.xml
RETURNVALUEPHPUNIT=$?

pkill -f 'bin/phantomjs --webdriver=8910'
php bin/console server:stop -q

exit $RETURNVALUEPHPUNIT