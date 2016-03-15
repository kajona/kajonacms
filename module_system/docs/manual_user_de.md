# Usermanagement

Das Modul User dient zur Verwaltung der im System hinterlegten Gruppen und Benutzer. 
Es können neue Benutzer und Gruppen angelegt werden, die Mitgliedschaften verändert werden, und ein Login-Protokoll ausgegeben werden.

Hinweis: Bei der Installation wird ein Benutzer mit dem Benutzernamen und Passwort angelegt. Diese Werte werden im Laufe der Installation erfasst. Mit diesem kann man sich nach der Installation an der Administration anmelden.

Für die Version 3.4.1 wurde die Benutzerverwaltung grundlegend überarbeitet. So ist es nun möglich, verschiedene Benutzerquellen und Authentifizierungsmechanismen zu verwenden, oftmals auch als Usersources oder Login-Provider bezeichnet.
Per Standard wird Kajona immer mit dem Kajona-Loginprovider ausgeliefert. Er arbeitet analog zr Benutzerverwaltung früherer Kajona-Versionen und speichert alle benötigten Daten innerhalb der Kajona-Datenbank.

##Login-Provider

Innerhalb des Benutzer-Subsystems gibt es zwei generelle Bereiche der Datenhaltung. Zum Einen werden innerhalb von Kajona globale Werte von Benutzern und Gruppen gespeichert, hierzu gehört bei Benutzer beispielsweise der Benutzername oder der verwendete Admin-Skin. Zum Anderen kann jeder Login-Provider weitere, spezifische Daten wie eine Adresse oder das Geburtsdatum des Benutzers speichern.

Sobald ein Benutzer oder eine Gruppe innerhalb des System angefragt wird, werden die bestehenden, bekannten Daten durchsucht. Sofern dort ein passender Eintrag gefunden wird, wird dieser zurückgegeben. Der zurückgegebene Datensatz besteht in erster Instanz jedoch nur aus den globalen Daten, d.h. Die zugehörige Benutzer-Quelle wurde noch nicht nach weiteren Daten befragt.

Sobald weitere Details angefragt werden, erfolgt das Nachladen der Detail-Daten durch die entsprechende Benutzerquelle / den Login-Provider.

![](https://www.kajona.de/image.php?image=/files/images/upload/manual/moduleuser_1.png&maxWidth=550)

A: Ein Modul X fragt am System die Daten des Benutzers Z an
B: Das System lädt durchsucht die Datenbank und gibt
C: die Standard-Daten des Benutzers Z zurück
D: Das Modul X greift auf erweiterte Daten zu, die bisher nicht nachgeladen wurden. Um den Zugriff zu ermöglichen löst der Benutzer-Kern das Nachladen der Daten durch den zugehörigen Login-Provider aus
E: Der Login-Provider füllt die noch fehlenden Daten auf Basis interner Daten aus

Ähnlich wie oben beschrieben erfolgt der Login eines Users am System. In erster Instanz überprüft das System, ob sich der anzumeldende Benutzer bereits im System befindet. Ist dies der Fall, so wird direkt der zugehörige Login-Provider zur Authentifizierung geladen und ausgelöst. Kann der Benutzer nicht gefunden werden, dann wird der anzumeldende Benutzer sequentiell bei jedem Login-Provider angefragt. Sobald ein Login-Provider den Benutzer innerhalb seiner spezifischen Daten findet, kann er diesen als Kajona-Benutzer anlegen und ggf. zurückgeben sowie autorisieren. Die einzelnen Login-Provider werden dabei in der Reihenfolge der Nennung in der config.php (siehe unten) angefragt.

![](https://www.kajona.de/image.php?image=/files/images/upload/manual/moduleuser_2.png&maxWidth=550)

##Subsysteme

Jeder Login-Provider bzw. jedes Login-Subsystem ist im Ordner /system/usersources abgelegt. Ein Subsystem besteht dabei aus genau drei Dateien, wobei TBD als zu verwendender Name gilt:

* UsersourcesGroupTBD.php
* UsersourcesUserTBD.php
* UsersourcesSourceTBD.php

Die einzelnen Dateien werden durch die entsprechenden Interfaces definiert, die Schnittstellen und Strukturen sind also vorgegeben. Die Klassen group und user sollten selbsterklärend sein, sie repräsentieren jeweils eine Gruppe oder einen Benutzer. Jede source stellt eine allgemeine Zugriffsfassade dar, die für das Laden und Verwalten der einzelnen Benutzer und Gruppen verwendet wird. Darüber hinaus definiert die Klasse Eigenschaften des Login-Providers. Hierzu gehört die Information, ob einzelne Daten der Benutzer / Gruppen editierbar sind sowie ob das Anlegen neuer Datensätze seitens der Quelle überhaupt unterstützt wird.
Ggf. kann ein  Login-Provider durch eigene Konfigurationsdateien weiter konfiguriert und parametrisiert werden.

Der Kajona-Login Provider kann dabei als Referenz herangezogen werden, ein weiterer Login-Provider für LDAP-Systeme gibt es im Git-Repository des Kajona-Projektes.

##Settings

Der Wert des default-Skins sollte klar sein: Welcher Skin wird als Standard-Skin verwendet, bis der User einen Skin explizit auswählt.

Mit dem Wert „ID der Admin-Gruppe“ kann festgelegt werden, welche Gruppen-ID als globaler Admin behandelt wird. Analog definiert der Wert der Gäste-Gruppe, was als Gast behandelt werden soll.

Der Wert „Anzahl Zeilen“ legt fest, wie viele Zeilen im Login-Protokoll ausgegeben werden sollen.
Die Einstellung „Eigene Daten“ erlaubt Anwendern, auch dann ihr Profil zu verändern, wenn der User an sich keine Rechte am Usermanagement hat. Wird der Wert auf „Nein“ gesetzt, dann benötigt der Benutzer die entsprechenden Rechte an der Benutzerverwaltung.


##config.php
In der config.php werden die einzelnen Login-Provider aktiviert. Diese werden dabei als kommaseparierte Liste im Feld ``$config['loginproviders']`` gelistet.
Bei einer Standard-Installation sieht der Eintrag wie folgt aus:

	$config['loginproviders']       = "kajona";

Würde man nun exemplarisch einen weiteren Login-Provider „ldap“ aktivieren wollen, so würde der ergänzte Eintrag wie nachstehend aussehen:

	$config['loginproviders']       = "kajona,ldap";
	
Die Login-Provider werden dabei in der Reihenfolge der Nennung abgefragt. Der „kajona“-Eintrag sollte dabei nicht entfernt werden, da sonst auch die während der Installation angelegten Benutzer (u.A. der Admin-Accout) nicht mehr funktionsfähig sind.
