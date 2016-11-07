#!/usr/bin/env bash
mkdir release
echo -n $DEMBELO_VERSION > files/version
git config user.name "Travis CI"
git config user.email "travisci@waszulesen.de"
git add files/version
git commit -m "Set build version number" files/version
git tag $DEMBELO_VERSION -a -m "Tag from weekly TravisCI build $TRAVIS_BUILD_NUMBER"
git push --quiet git@github.com:typearea/dembelo.git $DEMBELO_VERSION
git checkout-index -a -f --prefix release/
cd release
export SYMFONY_ENV=prod
composer --working-dir="www" remove alcaeus/mongo-php-adapter mongodb/mongodb
composer --working-dir="www" install --no-dev --optimize-autoloader
rm www/app/config/parameters.yml
echo "zip release: $RELEASE_FILENAME"
zip -rq $RELEASE_FILENAME ./
ls -l $RELEASE_FILENAME