# Packages

Seit Kajona V4 werden alle wichtigen Komponenten des Systems in Form von Paketen verteilt und verwaltet. Ein Paket ist in der Regel eine Sammlung von Elementen und Modulen oder aber ein Template-Pack, d.h. ein an sich abgeschlossenes Portal-Layout.

Pakete können automatisiert über die im System integrierte Paketvewaltung installiert und aktualisiert werden. Damit dies möglich ist, müssen Pakete hinsichtlich des Aufbaus ein paar Konventionen folgen.

## Modul / Element Pakete
Modul- und Element-Pakete bilden jeweils ein in sich abgeschlossenes Dateisystem. Dieses folgt dem bekannten Aufbau und enthält auf oberster Ebene die Verzeichnisse admin, installer, lang, portal, system, templates sowie die später beschriebene metadata.xml Datei.

##Template Pakete
Auch Template-Packs folgen den Konventionen hinsichtlich des Dateisystems. Auf oberster Ebene sind daher oftmals die Verzeichnisse css, images und tpl zu finden, ergänzt um die metadata.xml Datei.

##metadata.xml
Die metadata.xml Datei liegt jedem Paket bei und beschreibt das Paket. Neben allgemeinen Werten wir dem Namen des Pakets und einer Beschreibung definiert die Datei außerdem, um welche Art von Paket (Modul / Element / Template)oder welche Version des Pakets vorliegt, aber auch welche Voraussetzungen für die Installation des Paketes erfüllt sein müssen.

Der Aufbau der Datei soll exemplarisch beschrieben werden:

```
<?xml version="1.0" encoding="UTF-8"?>
<package
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="http://apidocs.kajona.de/xsd/package.xsd"
    >
    <title>faqs</title>
```
Der Titel bescheibt den Namen des Pakets möglichst knapp und eindeutig. Die folgende Bechreibung kann für einen kurze textuelle Beschreibung der Funktion des Pakets verwendet werden.

```
    <description>A module to organize frequently asked questions.</description>
```

Zentrales Element der Datei ist die Angabe der vorliegenden Versionsnummer. Daraus kann das System die Verfügbarkeit von Updates ermitteln.

```
    <version>3.4.9</version>
    <author>Kajona Team</author>
```

Die Angabe des Paket-Erstellers (Author) ist eher informativer Natur, dient aber zum Ernten des Ruhm.

Das nächste Element, „target“, ist für die Installation des Paketes relevant. Der Wert benennt den Zielordner innerhalb des Subsystems (/core oder /templates). Jedoch ist dieses Element optional. Sofern es nicht angegeben wurde, wird das Zielverzeichnis automatisch aus dem jeweiligen Paket-Namen (Feld title) aufgebaut. Die Angabe ist also nur dann erforderlich, wenn der Name des Pakets und der Ordner im Dateisystem voneinander abweichen.

```
    <target>module_faqs</target>
```

Über den Typ wird nicht nur die Installation des Pakets, sondern auch im Paketsystem die ensprechende Verwaltung initialisiert. Erlaubte Werte sind hier: MODULE, ELEMENT, TEMPLATE.

```
    <type>MODULE</type>
    <providesInstaller>TRUE</providesInstaller>
``` 
       
Ob ein Paket überhaupt einen Installer mitbringt wird durch den Wert des Eintrages providesInstaller eingestellt. Nur wenn hier TRUE hinterlegt ist wird dieser während der Paketinstallation / Aktualisierung aufgerufen. Bei Templates muss hier der Wert FALSE eingetragen werden.
Über die requiredModules werden Abhängigkeiten definiert. Alle genannten Module müssen in mindestens der angegebenen Version im System installiert sein, erst dann kann das Modul installiert werden.

```
    <requiredModules>
        <module name="system" version="3.4.9.3" />
        <module name="pages" version="3.4.9.1" />
    </requiredModules>
```

Je nach Art des Pakets, z.B. bei Templates, machen aussagekräftige Screenshots mehr Sinn als lange Texte. Hierfür können innerhalb des optionalen screenshot-Elements bis zu drei screenshot-Angaben gemacht werden. Je screenshot-Element referenziert das path-Attribut auf eine Datei innerhalb des ZIP-Paketes. Für Screenshots ist einer der folgenden Dateitypen erlaubt: jpg, png, gif.

```
    <screenshots>
        <screenshot path=”/screenshot.jpg” />
    </screenshots>
</package>
```
##ZIP-Struktur
Alle Pakete werden in Form eines ZIP-Archives verteilt. Beim Erstellen des Paketes ist darauf zu achten, dass dieses direkt die einzelnen Ordner und die metadata.xml Datei auf oberster Ebene führt. Es darf also kein Ordner wie „module_faqs“ mit gepackt werden.