# Framework


Klar ist, dass Kajona eine strenge Trennung der Logik von der Präsentation verfolgt. Ebenso ist die Datenhaltung / die Persistenz an sich von den beiden erwähnten Schichten zu trennen. Daraus ergibt sich ein dreistufiger Aufbau des Systems:

![](https://www.kajona.de/image.php?image=/files/images/upload/manual/002_architektur.png&maxWidth=500)

Deutlich werden soll hier, dass es keine direkte Verbindung der Präsentationsschicht und der Datenbank gibt. Nur so können Sicherheitsmechanismen effektiv implementiert werden. Ein weiterer Vorteil ist, dass ein Entwickler der Präsentationsschicht nicht den Aufbau der Datenbank kennen muss. Er fordert die benötigten Daten einfach beim Model an, und freut sich dann des Lebens. Wie die Daten vom Model beschaffen werden, ist der Präsentation prinzipiell egal.

Analog verhält es sich mit der Relation des Models zur Datenbank. Dem Model ist es egal, ob die Datenbankschicht die Werte in eine Datenbank, oder in eine XML-Datei schreibt. So lange das Model die Daten erhält, ist alles wunderbar.

Diese Gegebenheiten prägen des Framework-Charakter des Systems. Alle Schnittstellen sind sauber definiert, so dass ein Erweitern des Systems einfach und schnell vorgenommen werden kann.

Des Weiteren wird in Kajona strikt zwischen Portal und Administration unterschieden. Das Portal wird zur Darstellung der Inhalte verwendet, die Administration zur Erfassung und Bearbeitung der Inhalte sowie zur Verwaltung des Systems.

Bei einer Anfrage an das System wird also, je nach Art der Anfrage, in das Portal oder die Administration verzweigt.

![](https://www.kajona.de/image.php?image=/files/images/upload/manual/003_aufteilung.png&maxWidth=500)

##Module

Kajona ist modular aufgebaut. Das bedeutet, dass zusammengehörige Funktionen in Modulen gebündelt werden, und somit nur bei Bedarf in das System installiert werden. Das Erweitern eines bestehenden Systems kann auf Grund dieser Modularität jederzeit vorgenommen werden. Besteht Bedarf für eine FAQ-Verwaltung, so kann das Modul „FAQs“ nachträglich in das System installiert werden, um dieses um die nun benötigten Funktionen zu erweitern.

##Datenbank

Kajona benötigt eine Datenbank, um die Inhalte, die über die Administration erfasst werden, speichern zu können. Aktuell existieren Treiber für MySQL, PostgreSQL, Oracle und SQLite. Kajona kann aber bei Bedarf durch einen neuen Datenbanktreiber mit jeder anderen SQL-fähigen Datenbank betrieben werden. 
Das Datenbankschema von Kajona ist dabei von den nachfolgenden Eigenschaften gekennzeichnet:

###Global Unique Ids (GUIDs)
GUIDs sind über die Systemgrenze hinweg eindeutig. Dies hat dann Vorteile, wenn man Datensätze des aktuellen Systems in ein Fremdsystem importieren möchte. Da die Erzeugung der IDs nicht die Datenbank, sondern Kajona übernimmt, sind die Ids bereits vor dem Einfügen in die Datenbank bekannt. Ein Rückfragen der verwendeten ID nach einem Insert entfällt also.
Ebenso werden keine zusätzlichen Referenzschlüssel mehr benötigt, da alle Primärschlüssel auf Grund der GUIDs ja bereits eindeutig sind und somit direkt referenziert werden können.

###Systemtabelle
Die Systemtabelle dient als zentrale Verwaltungsinstanz und bildet die Strukturen des Systems ab. Verwendet wird hierfür eine Baumstruktur. 
Benötigt man mehr Details zu einem Datensatz, als in der Systemtabelle vorhanden, so können diese über JOINs aus weiteren Tabellen hinzu verknüpft werden. Dazu gehören Tabellen wie die Rechte-Tabelle, Datum-Tabelle, Element-Tabelle oder die Modul-Tabelle. 
Benötigt man einen Ausschnitt aus der Baumstruktur, so wird einfach die Systemtabelle mit sich selbst verknüpft.

![](https://www.kajona.de/image.php?image=/files/images/upload/manual/005_verknseiteelement.png&maxWidth=500)

##Rechtesystem

Das Rechtesystem in Kajona orientiert sich an der bereits beschriebenen Baumstruktur der Systemtabelle. Auf Grund dieses Aufbaus ist eine rekursive Rechtevergabe innerhalb des Systems möglich, Datensätze können also von übergeordneten Datensätzen ihre Rechte erben.

Diese Kette der Vererbung kann an jeder Stelle der Hierarchie unterbrochen werden. Angenommen, es gibt ein paar Seiten, die nur registrierten Benutzern zur Verfügung stehen sollen, so benötigen diese spezielle Rechte. Der eine Weg wäre nun, die Rechte jeder Seite anzupassen. Der schönere Weg aber ist, alle Seiten in einem Ordner abzulegen. Diesem Ordner gibt man dann die angepassten Rechte – die darin enthaltenen Seiten und Unterordner erben dann automatisch die angepassten Rechte. Diesen Sachverhalt soll auch die nächste Grafik erläutern.

![](https://www.kajona.de/image.php?image=/files/images/upload/manual/006_rechtevergabe.png&maxWidth=500)

Vom Konzept der Rechtevergabe her ist das Rechtesystem an dem von UNIX angelehnt. Die Vergabe von Rechten erfolgt also nicht per User, sondern per Gruppe. Hierbei gilt, das beliebig viele Benutzer in beliebig vielen Gruppen Mitglied sein dürfen.
Da die Rechte aber Gruppen abhängig vergeben werden gilt ebenso: Ein User ohne Gruppe ist ein User ohne Rechte. Dieser hat dann gegebenenfalls sogar weniger Rechte als ein Gast!
Es gibt in Kajona zwei vordefinierte Gruppen: Gäste und Administratoren. Gäste sind all die Benutzer, die nicht angemeldet sind. Mitglieder der Gruppe Administratoren hingegen haben superglobale Admin-Rechte, dürfen also alles.

##Settings

Mit der Einstellung „Portal deaktiviert“ kann das gesamte Portal ausgeschaltet werden. Ist diese Einstellung aktiviert, dann wird stattdessen die unter „Zwischenseite“ angegebene Seite geladen.
Anzahl DB-Dumps legt fest, wie viele Sicherungen der Datenbank im System vorgehalten werden sollen. Der Wert sollte nicht all zu hoch sein, da sonst schnell viel Speicherplatz verbraucht wird.

Die im Feld „Admin E-Mail“ hinterlegte E-Mail-Adresse wird dafür verwendet, um im Fehlerfall eine E-Mail zu verschicken. Wird keine Adresse angegeben, wird auch keine E-Mail verschickt.

Der Wert, der unter „maximale Sperrdauer“ definiert wird, gibt an, nach wie vielen Sekunden gesperrte Datensätze automatisch wieder entsperrt werden.

Der in der Option „Anzahl Datensätze pro Seite“ hinterlegte Wert gibt an, wie viele Datensätze in einer Admin-Liste pro Seite ausgegeben werden. Dies muss vom jeweiligen Modul unterstützt werden und kann von einem Modul gegebenenfalls redefiniert werden.

„Admin nur per https“ stellt sicher, dass die Administrationsoberfläche nur per https geladen wird und nicht per http. Dies setzt jedoch voraus, dass der Webserver mit https-Unterstützung konfiguriert wurde. Dies Einstellung beeinflusst unter Umständen den Portal-Editor: Wird das Portal per http geladen, die Administration (in Form des Portal-Editors) jedoch per https, verweigert der Browser unter Umständen das Schließen des Portal-Editor Popups sowie das anschließende Neuladen des Portals. Dies stellt eine Sicherheitsfunktion des Browser dar und kann somit nicht verhindert werden. Verwendet der Application-Server vom Standard abweichende Header zu Kenntlichmachung einer HTTPS-Verbindung, dann können diese in der config.php hinterlegt werden. In der Regel ist dies aber nicht nötig.

Da externe Anfragen, z.B. RSS-Feeds von Kajona zwischengespeichert werden, definiert die Einstellung „Cachedauer externer Quellen“ die maximale Zeit, die Anfragen gespeichert werden. Ist diese Zeitspanne vergangen, werden die Anfragen erneut ausgeführt. Eine höhere Cachedauer verringert die Anzahl an externen Anfragen und erhöht damit die Geschwindigkeit des Systems, bei sich oft ändernden externen Inhalten sollte hier jedoch ein sinnvolles Maß gefunden werden.

Die Dauer einer Session gibt an, wann eine Session nach der letzten Aktion des Benutzers automatisch beendet wird. Vergisst also beispielsweise ein Benutzer sich von der Administration abzumelden, so wird die Sitzung automatisch nach der angegebenen Dauer beendet.

Welche Bibliothek zum Erstellen von Diagrammen verwendet wird, definiert die Einstellung „Verwendete Chart-Bibliothek“. Hier kann zwischen ez Components sowie pChart umgschaltet werden. pChart kann aus lizenztechnischen Gründen nicht mit Kajona ausgeliefert werden und muss von daher getrennt heruntergeladen und installiert werden.

##ModRewrite
Mit der Einstellung „Mod Rewrite“ wird festgelegt, ob das System die Rewrite-Extension verwenden soll. Mod Rewrite ist ein Modul des Apache-Webservers und wird in der Regel dazu verwendet, dynamische URLs in scheinbar statische html-URLs zu verwandeln. Aus ``index.php?seite=bilder&action=folderImage&systemid=23hgk57gfhdk58jhgjf87`` wird dann  ``bilder.folderImage.23hgk57gfhdk58jhgjf87.html``. Das ist nicht nur für den Menschen, sondern zum Beispiel auch für Suchmaschinen besser lesbar. Zusätzlich zu dieser Einstellung im System muss die Einstellung in der .htaccess-Datei auf „on“ gesetzt werden. In der .htaccess-Datei also einfach aus „RewriteEngine off“ ein „RewriteEngine on“ machen und wie beschrieben alle „RewriteRule“-Einträge aktivieren – das wars. Ist das Modul nun auch aktiviert, so werden die Links umgeschrieben.

##System-Tasks

Systemtasks stellen kleine Programme dar, mit denen alltägliche Aufgaben erfüllt werden können. Dies können wartende Aufgabe, wie bspw. das Löschen der erzeugten Bilder sein oder auch verwaltende Aufgaben wie das Erzeugen oder Importieren von Datenbanksicherungen. Das Erstellen von eigenen Tasks ist auf Grund der vorgegebenen Schnittstellen einfach zu bewerkstelligen.

##Debug-Optionen / config.php
Die Datei /project/system/config/config.php stellt die zentrale Konfigurationsdatei des Systems dar. In dieser werden nicht nur die Einstellungen für die Datenbank vorgenommen, ebenso bietet die Datei die Möglichkeit, zusätzliche Parameter, z.B. zur Fehlersuche, zu setzen.
Oft kann es nützlich sein, bei der Suche nach einem Fehler mehr Informationen zur Verfügung zu haben. Hierfür bietet Kajona verschiedenen Debug-Optionen an. Diese werden alle in der config.php-Datei im Ordner /project/system/config definiert wobei die default-Werte aus der Datei /core/module_system/system/config/config.php stammen.

Die Einstellungen befinden sich im Abschnitt „System-Settings“.
Einschalten lassen sich normale Debug-Ausgaben, wie die Zeit (time), Anzahl Datenbankabfragen (dbnumber) und Anzahl verarbeiteter Templates (templatenr) die zur Generierung der Seite benötigt wurden. Zusätzlich kann über die Einstellung „memory“ der Speicherverbrauch ausgegeben werden, welcher für die Generierung einer Seite benötigt wird.

Es lassen sich aber auch Werte für das Debuggen im Hintergrund einstellen: Ist „dblog“ auf „true“ gesetzt, wird in ein Logfile ein Protokoll aller Datenbankabfragen geschrieben. Der Wert „debuglevel“ gibt an, ob Fehler wie Datenbankfehler ausgegeben werden sollen, oder stillschweigend übergangen werden sollen. Auf Live-Systemen sollte dieser Wert immer „0“ sein!
Um auch in den Java-Script Dateien erweiterte Nachrichten zu erhalten, kann der Wert ebenfalls auf 1 gesetzt werden.

Mit der Einstellung „debuglogging“ wird festgelegt, wie detailliert das Log-File aufgebaut werden soll. 
Um die Einträge des globalen Caches einzusehen kann im Modul System die Aktion „Cache“ gewählt werden. Per Default wird die Anzahl an Zugriffen auf einen Cache-Eintrag nicht protokolliert, dies kann jedoch durch die System-Einstellung „cache“ aktiviert werden. Es wird geraten den Wert nur temporär zu Testzwecken zu aktivieren, da sich die Protokollierung negativ auf die Portalgeschwindigkeit auswirken kann.

Wenn über die Systemeinstellungen der HTTPS Modus aktiviert wurde, dann können die entsprechend zu prüfenden HTTP Header ebenfalls definiert werden.
Per Default werden die Einstellungen

	$config['https_header']         = "HTTPS";
	$config['https_header_value']   = "on";
	
verwendet. Je nach Serverkonfiguration sollten diese aber angepasst werden, anderenfalls kann ein Login am System fehlschlagen. Auf Systemen des Hosters 1&1 gelten beispielsweise die Werte 

	$config['https_header']         = "HTTPS";
	$config['https_header_value']   = "1";
