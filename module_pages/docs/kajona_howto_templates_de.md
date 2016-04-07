#Wie funktionieren Kajona-Templates?

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_01.jpg&maxWidth=600)

Dieses Tutorial erklärt die in Kajona eingesetzten Templates und zeigt wie lediglich durch Bearbeiten der HTML-basierten Templates und CSS-Dateien das Layout einer Kajona-Webseite angepasst werden kann. Es sind also keinerlei PHP-Programmierkenntnisse erforderlich um das Portallayout (also das, was der Webseitenbesucher zu sehen bekommt) den eigenen Wünschen und Bedürfnissen anzupassen.

Bevor es los geht ist es wichtig, eine laufende Kajona-Installation auf dem lokalen oder entfernten Webserver zu haben. Hilfe hierzu gibt es in unserem Kajona Quick Install Guide auf unserer Webseite.

##Die Ordner- und Dateistruktur
Zuerst ist es wichtig die Ordner- und Dateistruktur von Kajona zu verstehen. Für dieses Tutorial sind folgende Ordner und Dateien relevant:

```
<kajona_root>
    |- admin
    |- core
    |- files
    |- project
    |- templates
    |    |- default
    |         |- css
    |              - styles.css
    |              - <ggf. weitere css Dateien>
    |         |- js
    |         |- pics
    |         |- tpl
    |              |- <div. module_xxxx Ordner>
    |    |- <später hier eigener Template Ordner>
```

###/templates
Hier werden alle Templates gespeichert. Ein default Ordner wird mitgeliefert, eigene Layouts werden in zusätzlichen Ordnern angelegt.
Unterhalb eines Layout-Ordners gibt es den Ordner tpl, dieser enthält für jedes Modul einen eigenen Ordner um eine saubere Trennung der einzelnen Module zu ermöglichen. Zusätzlich legen auch manche Seitenelemente und Module ihre Templates hier ab (Ordner, die mit module_ beginnen).

###/templates/default/css
Alle CSS-Dateien werden hier abgelegt. 

###/templates/default/pics
In diesem Ordner sind alle öffentlichen Grafikdateien des mitgelieferten Layouts abgelegt. 


##Neues Template Set erstellen
Logge dich im Backend ein, wechsle in den Aspekt „Verwaltung“ (Drop Down oben rechts) und rufe den Punkt „Paketverwaltung" auf. Unter „Installierte Templates" werden alle verfügbaren Templates angezeigt und neue können angelegt werden.
Über die Buttons rechts unter der Liste können neue Templates hochgeladen oder erstellt werden.

Klicke auf „Neues Template-Pack anlegen“ und es erscheint eine Liste mit alles verfügbaren Elementen und Modulen, die im default Template enthalten sind. Gib dem neuen Template eine Bezeichnung (z.B. mynewtemplate) und wähle alle Elemente und Module, die du überschreiben willst. Was nicht ausgewählt und damit später nicht überschrieben wird, wird weiterhin aus dem default Template verwendet.
Wähle mindestens „module_pages“ aus. Das ist das wichtigste Element zum layouten der Seite. Das Modul sollte aber auch vorausgewählt sein.

Nachdem die Aktion mit „Speichern“ beendet wurde, existiert im Filesystem unter /projects/templates/ ein neuer Ordner mit dem neuen Namen.
Folgende Struktur ist nun vorhanden (wenn nur module_pages ausgewählt wurde):

```
|- templates
    |    |- default
    |    |- mynewtemplate
    |         |- css
    |         |- js
    |         |- tpl
    |              |- element_date
    |              |- element_image
    |              |- element_paragraph
    |              |- element_plaintext
    |              |- element_row
    |              |- module_pages
```

##Template anpassen: Das Haupttemplate module_pages
Unter module_pages findest du drei Dateien: home.tpl, master.tpl und standard.tpl. Die standard.tpl und die home.tpl kannst du nun nach eigenen Wünschen anpassen. 

Öffne die neue Template-Datei `/templates/mynewtemplate/tpl/modul_pages/standard.tpl` in einem Text- bzw. HTML-Editor deiner Wahl. Wichtig ist hierbei, dass die Zeichenkodierung auf UTF-8 eingestellt ist. Ansonsten kann es später zur fehlerhaften Darstellung von Sonderzeichen kommen.

Wie du siehst besteht es ausschließlich aus reinem HTML-Code und ein paar Kajona-Platzhaltern (`%%platzhalter_name%%`) und Kajona blocks.
Es beinhaltet die Basis-HTML-Struktur mit allen Head-Definitionen wie den Seitentitel, Meta-Tags und Referenzen auf CSS-Dateien.

Kajona-Platzhalter existieren in verschiedenen Varianten:

* Standard-Platzhalter
z.B. `%%title%%`
Diese enthalten z.B. den Seitentitel oder Meta-Daten.

* Blocks-Element
Ein Blocks-Element dient als Container für verschiedenen Block-Elemente. Diese können später angelegt werden.
`<kajona-blocks kajona-name="Headline">`

* Block-Element
Ein Block Element wird innerhalb eines blocks-elements angelegt und beinhaltetet die eigentlichen Platzhalter. Innerhalb eines blocks kann es beliebig viele block-Elemente geben, die später zueinander sortiert werden können.
```
	<kajona-blocks kajona-name="Headline">
        <kajona-block kajona-name="Headline">
            <div class="page-header">
                <h1>%%headline_plaintext%%</h1>
            </div>
        </kajona-block>
	</kajona-blocks>
 ```
	

* Seitenelement-Platzhalter in block-elementen
`%%<platzhaltername>_<elementname>%%` z.B. `%%text_headline%%`
Der Platzhaltername kann hier beliebig gewählt werden. Ihm folgt ein Unterstrich und anschließend der Name des Seitenelements. 

* Master-Seitenelement-Platzhalter
`%%master<platzhaltername>_<elementname>%%`
z.B. `%%mastermainnavi_navigation%%`
Diese Platzhalter verhalten sich genau wie die vorher erwähnten Seitenelement-Platzhalter. Allerdings werden diese auf der Master-Seite angelegt. Dies ist sehr hilfreich für Seitenelemente, die auf jeder Seite erscheinen sollen, wie beispielsweise ein Navigationselement. Diese Master-Seitenelement-Platzhalter müssen sowohl in den Seitentemplates als auch im Mastertemplate (master.tpl) definiert werden.

Zusätzlich gibt es den Platzhalter `%%kajona_head%%` welcher den im Portal benötigten JavaScript-Code enthält und die Konstante `_ webpath _` welche die aktuelle URL zur Kajona-Installation enthält. Die Konstante `_system_browser_cachebuster _` sollte allen Referenzen zu JavaScript- und CSS-Dateien angehängt werden. Dadurch kann Kajona im Fall eines Updates die Webbrowser zwingen, die JavaScript- und CSS-Dateien erneut herunter zu laden statt die veralteten Dateien aus dem Browsercache zu verwenden. 



##Das Template an eigene Bedürfnisse anpassen
Nachdem du nun die Grundlagen der Kajona-Platzhalter gelernt hast kannst du nun das mitgelieferte Beispieltemplate oder deine kopierten Seiten (home, pages, ...) beliebig anpassen. Nicht benötigte Platzhalter können natürlich einfach gelöscht werden.

Neben einfachen Änderungen der html-Struktur sollen sicher auch die Style Sheets (css) angepasst werden. Es empfiehlt sich, die Standard-Kajona-CSS-Datei zu verwenden und eigene Änderungen zu ergänzen/überschreiben. Diese liegt im Ordner `/templates/default/css`. (Hier liegen zwei Dateien: bootstrap.min.css und bootstrap.css. Sie sind exakt gleich, allerdings wurde die bootstrap.min.css komprimiert um die Ladezeit zu optimieren. Als Vorlage nimmt man die bootstrap.css.)

Dazu hat man mehrere Möglichkeiten:

1. Anpassen der Standard-Styles
Kopiere die Datei `/templates/default/css/bootstrap.css` in dein Template-Paket nach `/templates/mynewtemplate/css/bootstrap.css` und passe die Datei dann deinen Bedüfnissen entsprechend an. Damit die Datei auch verwendet wird, musst du den Link im Template anpassen:
```
	<!-- Template specific stylesheets: CSS and fonts -->
	<link rel="stylesheet" href="_webpath_/mytemplatename/default/css/bootstrap.css?_system_browser_cachebuster_" type="text/css"/>
```  

2. Neue Datei erstellen (empfohlen!)
Erstelle eine neue CSS-Datei im Ordner `/templates/mynewtemplate/css` z.B. `mystyles.css`.
Damit die Datei auch verwendet wird muss in jeder tpl-Datei (home.tpl, pages.tpl, ...) der entsprechende Link eingefügt werden. Füge ihn unter dem Link zur styles.css ein, damit erst alle Standardeinträge eingelesen werden und dann deine Ergänzungen.
```
	<!-- Template specific stylesheets: CSS and fonts -->
	<link rel="stylesheet" href="_webpath_/default/default/css/bootstrap.css?_system_browser_cachebuster_" type="text/css"/>
	<link rel="stylesheet" href="_webpath_/default/mynewtemplate/css/mystyles.css?_system_browser_cachebuster_" type="text/css"/>
``` 

3. Komplett eigene CSS-Datei verwenden
Erstelle wie unter 2. eine eigene Datei und referenziere sie durch einen Link in allen tpl-Dateien. Der Link zur bootstrap.css kann entfernt werden.


Gehe nun in die Administration und aktiviere dein Template-Paket. Hierfür kannst du unter „Paketverwaltung“ => „Installierte Templates“ dein Paket „mynewtemplate“ aktivieren.


##Anpassen der Navigation
Kopiere die Datei `https://raw.githubusercontent.com/kajona/kajonacms/master/module_navigation/templates/default/tpl/module_navigation/mainnavi.tpl` nach `/templates/mynewtemplate/module_navigation/mainnavi.tpl`.
Öffne die Datei `/templates/mynewtemplate/module_navigation/mainnavi.tpl`:

Nun siehst du ein weiteres praktisches Feature der Kajona-Template-Engine: Template-Abschnitte.

Manche Module wie Navigationen oder News unterstützen die Verwendung von mehreren Template-Abschnitten. Je nach Fall wird ein anderer Abschnitt verwendet – beispielsweise wird der Abschnitt `<level_1_active>` verwendet, wenn es sich um einen aktiven Navigationspunkt handelt. `<level_1_inactive>` wird verwendet, wenn ein inaktiver Navigationspunkt dargestellt werden soll.

Im Standardtemplate wird die zweite Navigationsebene (level 2) direkt in den `<LI>`-Tag der ersten Navigationsebene eingefügt. Für unseren Fall möchten wir jedoch die zweite Navigationsebene separat auf der Seite anzeigen. Dafür benötigen wir nun ein zweites Navigationstemplate. Kopiere einfach das vorhandene Navigationstemplate und speicher es unter dem Namen mainnavi2.tpl. Beide Templates sollen nun wie folgt angepasst werden:


`/templates/mynewtemplate/module_navigation/mainnavi.tpl`:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_06.jpg&maxWidth=600)

`/templates/mynewtemplate/module_navigation/mainnavi2.tpl`:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_07.jpg&maxWidth=600)

Abschnitte und Platzhalter welche nicht benötigt werden (in unserem Fall `%%level2%%` in `<level_1_active>` im Template für die erste Navigationsebene) können einfach gelöscht werden.

Im neu erstellten zweiten Template haben wir nun mehrere fast leere Abschnitte, da wir in diesem Template wirklich nur die zweite Navigationsebene darstellen wollen.


##Die Master-Seite
Wir haben bereits von der Master-Seite gesprochen und dass dort Master-Seitenelemente definiert werden (z.B. `%%mastermainnavi_navigation%%`). Schauen wir uns das noch im Detail an.

Öffne die Datei `/templates/mynewtemplate/module_pages/master.tpl`:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_08.jpg&maxWidth=377)

Das Mastertemplate ist sehr kompakt, es besteht lediglich aus Master-Seitenelement-Platzhaltern. Die hier verwendeten Bezeichnungen müssen exakt mit denen in den Seitentemplates übereinstimmen.

Schau dir in der Administration die Seite „master“ im Ordner „_system“ an um zu sehen, welche Seitenelemente hier angelegt wurden.
Nun wollen wir die zweite Navigationsebene separat und mit eigenem Template anzeigen. Dazu fügen wir im Mastertemplate einen neuen Master-Seitenelement-Platzhalter mit der Bezeichnung `%%mastermainnavi2_navigation%%` hinzu:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_09.jpg&maxWidth=356)

Da wir den Platzhalter bereits in unserem Seitentemplate hinzugefügt haben...
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_10.jpg&maxWidth=450)

...muss nun nur noch auf der Master-Seite das entsprechende Element angelegt werden. Gehe dazu in die Navigation, öffne die Master-Seite und erstelle ein neues Navigationselement am Platzhalter `mastermainnavi2_navigation`. Wähle die Navigation „mainnavigation“ und das Template "mainnavi2.tpl".

Nun sollte das Portal wie folgt aussehen:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_11.jpg&maxWidth=600)


##Aber was ist mit den News?
Möglicherweise willst du auch die Darstellung der News in einem individuellen Layout darstellen. Nichts einfacher als das – hierzu muss nur das Template und die CSS-Styles bearbeitet werden.



Kopiere das Template demo.tpl von `https://github.com/kajona/kajonacms/blob/master/module_news/templates/default/tpl/module_news/demo.tpl` nach `/templates/mynewtemplate/module_news/demo.tpl`.

Schau dir das Template `/templates/mynewtemplate/module_news/demo.tpl` an und bearbeite es nach den eigenen Bedürfnissen, wie zum Beispiel:
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_13.jpg&maxWidth=600)

Wie du siehst ist der Aufbau ähnlich zu den Templates des Moduls Navigation. Zusätzlich kann hier auch die dynamischen Sprach-Syntax `[lang,key,modul]` verwendet werden. Darüber können sprachabhängige Texte aus den Sprachdateien im Ordner /lang eingefügt werden.
Der Platzhalter `[lang,news_mehr,news]` wird den deutschen Text „[weiterlesen]“ einfügen, welcher in der Datei `/core/module_news/lang/module_news/lang_news_de.php` hinterlegt ist. Je nach eingestellter Browser-Sprache wird eine andere Sprachdatei geladen.
![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/v4_templht_14.jpg&maxWidth=600)

##Schlussworte
Alle in diesem Tutorial verwendeten Templates, CSS-Dateien und Grafiken stehen auf der KajonaBase sowie über die Paketverwaltung zum Download bereit: 
http://www.kajonabase.net/Templates/templates.simpleday.fileDetails.52e720850b762e0c6b21.html

Das im Tutorial verwendete Layout basiert auf dem Layout „Simpleday“ von Igor Jovic (http://www.spinz.se/csstemplates.htm).

Und nun viel Spaß beim Umsetzen eigener individueller Layouts ;-)