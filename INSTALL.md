# Dembelo

## Installation
Vagrant ist ein Tool zur Verwaltung von virtuellen Maschinen. Dembelo nutzt es, um für VirtualBox eine virtuelle
Maschine aus dem Internet herunterzuladen und für Dembelo korrekt zu konfigurieren.

Falls man selber bereits einen Webserver mit PHP betreibt, bietet sich die Installation ohne Vagrant an.

### Installation mit [Vagrant](https://www.vagrantup.com/)
Voraussetzungen:

* VirtualBox ([Webseite](https://www.virtualbox.org/)|[Installation](https://www.virtualbox.org/manual/ch02.html))
* Vagrant ([Webseite](https://www.vagrantup.com/)|[Download](https://www.vagrantup.com/downloads.html))
* git ([Webseite](https://git-scm.com/)|[Download](https://git-scm.com/downloads))
* NFS-Server
  * unter Windows: `vagrant plugin install vagrant-winnfsd` ([mehr Informationen](https://github.com/GM-Alex/vagrant-winnfsd))
  * unter Linux: mit Hilfe der üblichen Paketverwaltung 

1. Klone das Git-Repository mit: `git clone https://github.com/typearea/dembelo.git`
2. Wechsle in das neu angelegte Verzeichnis _dembelo_ : `cd dembelo`
3. Installieren dort die Git-Submodule: `git submodule update --init`
4. Starte Vagrantbox: `vagrant up` (kann beim ersten Mal ein Weilchen dauern)
5. Logge dich per ssh in der Vagrantbox ein: `vagrant ssh`
6. Wechsle ins Installationsverzeichnis: `cd /vagrant/www/`
7. Installiere externe PHP-Abhängigkeiten: `composer update`
8. Kompiliere Bilder/JS/CSS: `php app/console assetic:dump --env=prod`
9. Webserver neu starten: `/etc/init.d/nginx restart `
10. Rufe Dembelo im Browser auf: [http://33.33.33.102](33.33.33.102)
11. Schließe bei Bedarf die Vagrantbox wieder: `vagrant halt`

#### Starten des Servers
1. Starte im Installationsverzeichnis die Vargrantbox: `vagrant up` (geht nach der initialen Installation sehr flott)
2. Rufe Dembelo im Browser auf: [http://33.33.33.100](33.33.33.100)
3. Schließe bei Bedarf die Vagrantbox wieder: `vagrant halt`

#### Löschen der Installation
1. Lösche im Installationsverzeichnis mit `vagrant destroy` die virtuelle Maschine.
2. Lösche dann das Installationsverzeichnis.

### Installation ohne Vagrant
Mit einem beliebigen Webserver und einem aktuellen PHP (>5.3.9) kann man Dembelo auch ohne Vagrant in Betrieb nehmen.

1. Klone das Git-Repository: `git clone git@github.com:typearea/dembelo.git`
2. Wechsle ins Installationsverzeichnis: `cd dembelo/www/`
3. Installiere externe PHP-Abhängigkeiten: `composer update`
4. Kompiliere Bilder/JS/CSS: `php app/console assetic:dump --env=prod`
5. Lasse das Document Root des Webservers auf das dembelo/www/web/-Verzeichnis verweisen
6. Rufe je nach Konfiguration Dembelo im Browser auf

## Datenbank einrichten
Über die Konsole kann man nun einen Admin- und einen "normalen" Benutzer anlegen, sowie ein paar Dummydaten erzeugen:

```
php app/console dembelo:install --purge-db --with-dummy-data
```

## Links
* [Dembelo-Git-Repository](http://github.com:typearea)
* [lokale Dembelo-Installation](http://33.33.33.100/) (Vagrant)
* [PhpMyAdmin](http://33.33.33.100/phpmyadmin) (Vagrant)

## Dank an:
[github.com/irmantas/symfony2-vagrant](https://github.com/irmantas/symfony2-vagrant) für die Vagrant/Puppet/Composer-Basis.

## Deployment
```
export SYMFONY_ENV=prod
composer install --no-dev --optimize-autoloader
php app/console cache:clear --env=prod
php app/console assetic:dump --env=prod
```
Quelle: http://symfony.com/doc/current/deployment.html

## Problembehebung
### ausreichend Arbeitsspeicher
Falls composer abbricht, kann dies an mangelndem Arbeitsspeicher liegen.
Swap kann nachträglich mittels dieser Anleitung angelegt werden: https://getcomposer.org/doc/articles/troubleshooting.md#proc-open-fork-failed-errors
