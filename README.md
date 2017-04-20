[![Stories in Ready](https://badge.waffle.io/typearea/dembelo.png?label=ready&title=Ready)](https://waffle.io/typearea/dembelo)
# Dembelo


[![Build Status](https://travis-ci.org/typearea/dembelo.svg?branch=master)](https://travis-ci.org/typearea/dembelo) [![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/dembelo/Lobby?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge) 

## Über Dembelo

Dembelo ist Software für das Eintauchen in neue Unterhaltungsliteratur, welche keine herkömmliche lineare
Abfolge kennt, sondern einen netztypischen verzweigten Aufbau zugrundelegt. Die Leser entscheiden, welchen
Erzählungsstrang sie jeweils weiterverfolgen möchten, und wählen Themen ab, für die sie sich nicht interessieren.
Die Oberfläche der Software ist so einfach wie möglich gehalten, um potentielle Reibungsverluste beim Einstieg
und bei der Nutzung zu minimieren und den Lesefluss nicht zu unterbrechen. Außerdem wird eine Anwendung zur
Verfügung gestellt, um Autoren dabei zu unterstützen, Texte für dieses neue Format zu verfassen und in das
System einzupflegen.

Dembelo ist freie Software lizenziert unter der GNU Affero General Public License 3 und jeder späteren
Version der Lizenz, so wie diese von der Free Software Foundation herausgegeben werden.

## Installation
* [INSTALL.md](https://github.com/typearea/dembelo/blob/master/INSTALL.md)

## Links
* [Webseite des Projekts](http://dembelo.de)
* [Dembelo-Git-Repository](http://github.com:typearea)

## Dank an:
[github.com/irmantas/symfony2-vagrant](https://github.com/irmantas/symfony2-vagrant) für die Vagrant/Puppet/Composer-Basis.

## Helfen/Mitarbeit
* Wir laden dazu ein, dembelo zu [forken](https://help.github.com/articles/fork-a-repo/) und [Pull-Requests](https://help.github.com/articles/using-pull-requests/) zu erstellen, damit wir eure Arbeit nach Dembelo-Master übernehmen können.
* Wir nutzen die ["Issues"](https://github.com/typearea/dembelo/issues) in unserem github-Repository zur Planung der Weiterentwicklung, nutzt bitte die Meilenstein-Ansicht.
* Im [Wiki](https://github.com/typearea/dembelo/wiki) findet sich eine Spezifikation und Konzeptionsthemen.
* Tretet mit uns per Mail in Kontakt: tina.giesler@typearea.de

## Entwicklung

### PHPUnit
* Aufruf: /vagrant/www/vendor/phpunit/phpunit/phpunit -c /vagrant/www/app/

### CodeSniffer

    cd /vagrant/www
    ./bin/phpcs --config-set installed_paths vendor/escapestudios/symfony2-coding-standard
    ./bin/phpcs --standard=symfony2 src


