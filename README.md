# Dembelo


[![Build Status](https://travis-ci.org/typearea/dembelo.svg?branch=master)](https://travis-ci.org/typearea/dembelo) [![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/dembelo/Lobby?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge) 

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/typearea/dembelo/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/typearea/dembelo/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/typearea/dembelo/badges/build.png?b=master)](https://scrutinizer-ci.com/g/typearea/dembelo/build-status/master) [![Code Coverage](https://scrutinizer-ci.com/g/typearea/dembelo/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/typearea/dembelo/?branch=master)

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

## Links
* [Blog des Projektes](http://blog.waszulesen.de)
* [Dembelo-Git-Repository](http://github.com/typearea)
* [Gattland](http://www.waszulesen.de)
* [Facebook](https://www.facebook.com/gattland)
* [Twitter](https://twitter.com/waszulesen)

## Installation
* [INSTALL.md](https://github.com/typearea/dembelo/blob/master/INSTALL.md)

## Helfen/Mitarbeit
* Wir laden dazu ein, dembelo zu [forken](https://help.github.com/articles/fork-a-repo/) und [Pull-Requests](https://help.github.com/articles/using-pull-requests/) zu erstellen, damit wir eure Arbeit nach Dembelo-Master übernehmen können.
* Wir nutzen die ["Issues"](https://github.com/typearea/dembelo/issues) in unserem github-Repository zur Planung der Weiterentwicklung, nutzt bitte die Meilenstein-Ansicht.
* Im [Wiki](https://github.com/typearea/dembelo/wiki) findet sich eine Spezifikation und Konzeptionsthemen.
* Tretet mit uns per Mail in Kontakt: tina.giesler@typearea.de
* Wer uns finanziell unterstützen will, kann dies gerne über [paypal.me/waszulesen](https://www.paypal.me/waszulesen) tun.

## Entwicklung

### PHPUnit
* Aufruf: /vagrant/www/vendor/phpunit/phpunit/phpunit -c /vagrant/www/app/

### CodeSniffer

    cd /vagrant/www
    ./bin/phpcs --config-set installed_paths vendor/escapestudios/symfony2-coding-standard
    ./bin/phpcs


