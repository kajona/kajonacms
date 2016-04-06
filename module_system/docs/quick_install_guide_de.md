#Quick Install Guide

Dieses Dokument beschreibt die Installation eines neuen Kajona-Systems in wenigen Schritten. 

##Download
Wenn noch nicht bereits geschehen, so beginnt die Installation von Kajona mit dem Download des Systems von www.kajona.de.


##Dateien entpacken
Nach dem Download sollte das ZIP-Paket entpackt werden. Dies kann bereits auf dem Ziel-Server, oder aber lokal erfolgen.
Nach dem Entpacken sollte das Dateisystem wie folgt aussehen (schematisch, je nach Paket-Wahl kann die Liste variieren):

	/admin
	/core
	/files
	/project
	.htaccess
	download.php
	index.php
	installer.php
	image.php
	xml.php
	
Nun kann es mit dem Schritt „Upload“ weitergehen.

##Upload
Das System kann nun auf den Webserver kopiert werden. Dies erfolgt in der Regel per FTP oder SSH, bei lokalen Testumgebungen meistens ganz einfach per Dateimanager. Befindet sich das System auf dem Webserver, so kann nun mit dem Schritt „Verzeichnisrechte anpassen“ fortgefahren werden.

##Verzeichnisrechte anpassen

Damit das System erfolgreich installiert und betrieben werden kann, müssen ein paar Datei- und Verzeichnisrechte vorgenommen werden. Konkret benötigt der Webserver Schreibrechte auf die folgenden Dateien/Verzeichnisse:

	/core
	/project/module_system/system/config
	/project/dbdumps
	/project/log
	/project/temp
	/files/cache
	/files/images
	/files/public
	/files/downloads
	
Die Rechte können mit allen gängigen FTP-Programmen abgeändert werden (Stichwort: chmod 0777). Sind alle Rechte angepasst, geht es mit dem Punkt „Datenbank erstellen“ weiter.

##Datenbank erstellen

Die Datenbank, in welcher Kajona später installiert werden soll, muss im Vorfeld angelegt werden. Kajona benötigt hierfür nicht zwingend eine eigene Datenbank sondern kann auch in bestehende Datenbanken parallel zu anderen Systemen installiert werden. Notiert werden sollten der Name der Datenbank, der Name des Datenbankbenutzers sowie dessen Passwort.

Das Anlegen einer Datenbank erfolgt in der Regel über die Administrationsoberfläche des Webhosters oder per z.B. per phpMyAdmin.

Nun kann der „Installer aufgerufen“ werden.

##Installer aufrufen
Wurde die Datenbank angelegt, so erfolgt nun das Aufrufen des Installers. Dieser wird beim ersten Aufruf des Systems automatisch ausgeführt – das System erkennt, dass noch keine Installation vorliegt.
Es sollte nun eine URL nach dem Schema ``www.meine-domain.xy/`` aufgerufen werden. Wurde das System in einem Unterordner angelegt, so muss der Pfad natürlich um diesen erweitert werden: ``www.meine-domain.xy/meine_unterordner/``.

Als erster Schritt überprüft der Installer ob alle Rechte zuvor korrekt konfiguriert wurden. Zusätzlich werden ein paar benötigte PHP-Module auf Vorhandensein überprüft.
Wurde dieser Schritt abgeschlossen, erfolgt das Erfassen der Datenbank-Zugangsdaten. Hier sollten nun die zuvor notierten Daten eingetragen werden.

Nach dem Klick auf „in config.php Speichern“ erscheint ein weiteres Formular. In diesem können die gewünschten Zugangsdaten zur Administration erfasst werden. Konkret sind das der Benutzername und das Passwort samt E-Mail Adresse für den späteren Zugang zur Administration.

Ist auch dieser Schritt erledigt, geht es an die Installation des Systems an sich.

Wurden alle Elemente und Module installiert ist die Installation abgeschlossen. 
Und dann kann es weiter gehen: „Das Portal lebt“.

##Das Portal lebt
Nach der Installation erfolgt der erste Test: Das Portal kann aufgerufen werden. Hierfür wird einfach der oberste Ordner des Systems aufgerufen, also, je nach dem,
``www.meine-domain.xy/``
oder 
``www.meine-domain.xy/meine_installation/``.

Es erscheint nun eine vorgefertigte Beispielseite. Herzlichen Glückwunsch – Kajona wurde installiert. Um nun Inhalte abzuändern, erfolgt das „Anmelden an der Administration“.

##Anmelden an der Administration
Um sich an der Administration anzumelden, wird der Pfad des Portals einfach um den Ordner admin/ ergänzt, also zu
``www.meine-domain.xy/admin/``
oder 
``www.meine-domain.xy/meine_installation/admin/``.
Nun erscheint die Login-Oberfläche der Administration, an der man sich mit dem während der Installation angegebenen Benutzernamen samt Passwort anmelden kann.


Viel Spaß mit Kajona!