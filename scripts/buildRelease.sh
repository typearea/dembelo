#!/usr/bin/env bash
mkdir release
#echo -n $DEMBELO_VERSION > files/version
#git add files/version
#git commit -m "Set build version number" public/version
#git tag $DEMBELO_VERSION -a -m "Generated tag from TravisCI build $TRAVIS_BUILD_NUMBER"
#git push --quiet https://$GITHUBKEY@github.com/typearea/dembelo $DEMBELO_VERSION > /dev/null 2>&1
git checkout-index -a -f --prefix release/
cd release
export SYMFONY_ENV=prod
composer --working-dir="www" remove alcaeus/mongo-php-adapter mongodb/mongodb
composer --working-dir="www" install --no-dev --optimize-autoloader
echo "zip release: $RELEASE_FILENAME"
zip -rq $RELEASE_FILENAME ./
ls -l $RELEASE_FILENAME