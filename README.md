# Dembelo

## Installation

### Installation mit [Vagrant](https://www.vagrantup.com/)

1. Installation Vagrant, siehe vagrantup.com
2. Installation NFS
    1. unter Windows: vagrant plugin install vagrant-winnfsd ([mehr Informationen](https://github.com/GM-Alex/vagrant-winnfsd))
    2. unter Linux: die übliche Paketverwaltung
3. Git-Repository klonen: git clone git@github.com:typearea/dembelo.git
4. in das neu angelegte Verzeichnis _dembelo_ wechseln
5. Git-Submodule installieren: git submodule update --init
6. Vagrantbox starten: vagrant up (kann ein weilchen dauern)
7. per ssh in der Vagrantbox einloggen: vagrant ssh
8. ins Installationsverzeichnis wechslen: cd /vagrant/www/
9. externe PHP-Abhängigkeiten installieren: composer update
10. Dembelo im Browser aufrufen: [http://33.33.33.100]
11. Vagrantbox schließen: vagrant halt

## Links
* [Dembelo-Git-Repository](http://github.com:typearea)
* [lokale Dembelo-Installation](http://33.33.33.100/)
* [PhpMyAdmin](http://33.33.33.100/phpmyadmin)

## Dank an:
[https://github.com/irmantas/symfony2-vagrant] für die Vagrant/Puppet/Composer-Basis.