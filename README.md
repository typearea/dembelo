# Dembelo

## Installation

### Installation mit [Vagrant](https://www.vagrantup.com/) Also für Leute die nicht sowieso einen Webserver und vollständige Entwicklungsoberfläche am laufen haben.

1. Installiere Vagrant, siehe hierzu vagrantup.com
2. Installiere den NFS Server
    1. unter Windows: vagrant plugin install vagrant-winnfsd ([mehr Informationen](https://github.com/GM-Alex/vagrant-winnfsd))
    2. unter Linux: mit Hilfe der üblichen Paketverwaltung 
3. Installiere Git: 
4. Installiere VirtualBox oder ein Aquivalent: 
5. Klone das Git-Repository mit: git clone https://github.com/typearea/dembelo.git
6. wechsle in das neu angelegte Verzeichnis _dembelo_ : cd dembelo
7. installieren dort die Git-Submodule: git submodule update --init
8. starte Vagrantbox: vagrant up (kann ein Weilchen dauern)

7. per ssh in der Vagrantbox einloggen: vagrant ssh
8. ins Installationsverzeichnis wechseln: cd /vagrant/www/
9. externe PHP-Abhängigkeiten installieren: composer update
10. Bilder/JS/CSS kompilieren: php app/console assetic:dump --env=prod
11. Dembelo im Browser aufrufen: [http://33.33.33.100](33.33.33.100)
12. Vagrantbox schließen: vagrant halt

### Installation ohne Vagrant
Mit einem beliebigen Webserver und einem aktuellen PHP (>5.3.9) kann man Dembelo auch ohne Vagrant in Betrieb nehmen.
1. Git-Repository klonen: git clone git@github.com:typearea/dembelo.git
2. ins Installationsverzeichnis wechseln: cd dembelo/www/
3. externe PHP-Abhängigkeiten installieren: composer update
4. Document Root des Webservers auf das dembelo/www/web/-Verzeichnis verweisen lassen
5. Je nach Konfiguration Dembelo im Browser aufrufen.


## Links
* [Dembelo-Git-Repository](http://github.com:typearea)
* [lokale Dembelo-Installation](http://33.33.33.100/) (Vagrant)
* [PhpMyAdmin](http://33.33.33.100/phpmyadmin) (Vagrant)

## Dank an:
[github.com/irmantas/symfony2-vagrant](https://github.com/irmantas/symfony2-vagrant) für die Vagrant/Puppet/Composer-Basis.

## Deployment
* Cache leeren: php app/console cache:clear --env=prod
* Assets erstellen: php app/console assetic:dump --env=prod
