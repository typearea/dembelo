# Dembelo

## Installation
Dembelo nutzt die Container-Software Docker und git. Beide Werkzeuge können
über die Paketverwaltung deiner Linux-Distribution installiert werden. Die
Betriebssysteme MacOS und Windows werden nicht unterstützt, unter Zuhilfenahme der
Dokumentation von Docker sollte aber auch dort die Installation möglich sein.

```bash
git clone git@github.com:typearea/dembelo.git
cd dembelo
docker-compose up
```
Das erstmalige Starten kann etwas länger dauern, da Basiscontainer aus
dem Netz heruntergeladen werden müssen.

## Starten/Stoppen des Containers
```bash
docker-compose up
docker-compose down
```

## Datenbank einrichten
Wechsel in den Container:
```bash
docker exec -u 0 -ti dembelo_web_1 bash
```

Im Container kann man nun einen Admin- und einen "normalen" Benutzer anlegen, sowie ein paar Dummydaten erzeugen:
```
cd /var/www/dembelo/www/
php bin/console dembelo:install --purge-db --with-dummy-data
```

## Links
* [Dembelo-Git-Repository](http://github.com/typearea/dembelo)
* [lokale Dembelo-Installation](http://0.0.0.0/) (Docker-Container)

## Deployment
```
export SYMFONY_ENV=prod
composer install --no-dev --optimize-autoloader
php app/console cache:clear --env=prod
php app/console assets:install web --env=prod
```
Quelle: http://symfony.com/doc/current/deployment.html

## Problembehebung
### ausreichend Arbeitsspeicher
Falls composer abbricht, kann dies an mangelndem Arbeitsspeicher liegen.
Swap kann nachträglich mittels dieser Anleitung angelegt werden: https://getcomposer.org/doc/articles/troubleshooting.md#proc-open-fork-failed-errors
