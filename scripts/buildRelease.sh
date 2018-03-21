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
cd www
composer install --no-dev --optimize-autoloader
php bin/console assets:install web --env=prod
yarn install
yarn run encore production
rm app/config/parameters.yml
cd ..
echo "zip release: $RELEASE_FILENAME"
zip -rq $RELEASE_FILENAME ./
ls -l $RELEASE_FILENAME