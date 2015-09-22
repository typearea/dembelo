#!/usr/bin/env sh
echo "Dembelo Wiki Tocifier"

if [ ! -e "/tmp/dembelo_wiki" ]
    then
    mkdir /tmp/dembelo_wiki
fi

cd /tmp/dembelo_wiki

if [ ! -e "github_toc" ]
    then
        git clone git@gist.github.com:c56fa651974ae6d86eee.git github_toc
fi

git clone git@github.com:typearea/dembelo.wiki.git
cd dembelo.wiki

for f in *.md;
    do ruby ../github_toc/github_toc.rb 2 $f $f
done

git add .
git commit -m'Automatische Inhaltsverzeichniserstellung'
git push origin master
cd ..
rm -rf dembelo.wiki

echo "Finished"
