#!/usr/bin/env bash

cd ./www/

php app/console server:start -q
bin/phantomjs --webdriver=8910 --webdriver-loglevel=ERROR &
app/console cache:warmup --env=selenium -q

bin/phpunit -c app/phpunitselenium.xml
RETURNVALUEPHPUNIT=$?

pkill -f 'bin/phantomjs --webdriver=8910'
php app/console server:stop -q

exit $RETURNVALUEPHPUNIT