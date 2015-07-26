# Dembelo

## Installation
Vagrant ist ein Tool zur Verwaltung von virtuellen Maschinen. Dembelo nutzt es, um für VirtualBox eine virtuelle
Maschine aus dem Internet herunterzuladen und für Dembelo korrekt zu konfigurieren.

Falls man selber bereits einen Webserver mit PHP betreibt, bietet sich die Installation ohne Vagrant an.

### Installation mit [Vagrant](https://www.vagrantup.com/)
Voraussetzungen:
* VirtualBox ([Webseite](https://www.virtualbox.org/)|[Installation](https://www.virtualbox.org/manual/ch02.html))
* Vagrant([Webseite](https://www.vagrantup.com/)|[Download](https://www.vagrantup.com/downloads.html))
* git ([Webseite](https://git-scm.com/)|[Download](https://git-scm.com/downloads))
* NFS-Server
** unter Windows: vagrant plugin install vagrant-winnfsd ([mehr Informationen](https://github.com/GM-Alex/vagrant-winnfsd))
** unter Linux: mit Hilfe der üblichen Paketverwaltung 

1. Klone das Git-Repository mit: git clone https://github.com/typearea/dembelo.git
2. wechsle in das neu angelegte Verzeichnis _dembelo_ : cd dembelo
3. installieren dort die Git-Submodule: git submodule update --init
4. starte Vagrantbox: vagrant up (kann beim ersten Mal ein Weilchen dauern)
5. per ssh in der Vagrantbox einloggen: vagrant ssh
6. ins Installationsverzeichnis wechseln: cd /vagrant/www/
7. externe PHP-Abhängigkeiten installieren: composer update
8. Bilder/JS/CSS kompilieren: php app/console assetic:dump --env=prod
9. Dembelo im Browser aufrufen: [http://33.33.33.100](33.33.33.100)
10. Vagrantbox schließen: vagrant halt

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

## Helfen/Mitarbeit
* Wir laden dazu ein, dembelo zu [forken](https://help.github.com/articles/fork-a-repo/) und [Pull-Requests](https://help.github.com/articles/using-pull-requests/) zu erstellen, damit wir eure Arbeit nach Dembelo-Master übernehmen können.
* Wir nutzen die ["Issues"](https://github.com/typearea/dembelo/issues) in unserem github-Repository zur Planung der Weiterentwicklung.
* Tretet mit uns per Mail in Kontakt: tina.giesler@typearea.de-