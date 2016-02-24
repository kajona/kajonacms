#Simple Pageelement


Mit diesem kleinen Tutorial soll der Aufbau einfachster Seitenelemente in Kajona beschrieben werden. Das Tutorial beschreibt hierfür die Entwicklung eines Seitenelements zur Ausgabe des Datums des letzten Bearbeitens der aktuellen Seite – Besucher der Internetseite können so  schnell erkennen, ob die Seite seit dem letzten Besuch verändert wurde, oder nicht. Das Element „lastmodified“ benötigt hierfür keine eigenen Datenbankeinträge, weswegen auf die Erstellung von Elementen mit Tabellen hier nicht eingegangen wird.

Analog zu diesem Seitenelement lassen sich natürlich auch andere, einfache Seitenelemente aufbauen.
Eine aktuelle Version dieses Seitenelements gibt es auch auf www.kajona.de zum Download.

> Dieses Dokument basiert auf Kajona 4.2. Einige der genannten Funktionen haben sich in Version 4.3 vereinfacht. Kajona 4.3 ist jedoch rückwärtskompatibel, so dass die genannten Funktionen nach wie vor funktionieren.
> Weitere Informationen finden sich auf http://www.kajona.de.


##Dateistruktur erstellen

Das im Folgenden entwickelte Seitenelement soll natürlich nicht nur für das eigene System verwendet werden, sondern soll auch an andere Kajona-User weitergegeben werden können. Von daher sollte das Seitenelement gleich in einer Version erstellt werden, die später weiter verteilbar ist.


In unserem Fall legen wir nun einen Ordner „module_lastmodified“ an, der nachstehende Dateien und Ordner beinhaltet:

```
module_lastmodified
    |- admin
    |    |- elements
    |         |- ElementLastmodifiedAdmin.php
    |
    |- installer
    |    |- InstallerElementLastmodified.php
    |
    |- lang
    |    |- module_elements
    |         |- lang_lastmodified_[de|en|bg|pt].php
    |- portal
    |    |-elements
    |         |- ElementLastmodifiedPortal.php
    |
    |- metadata.xml
```    

Doch nun zur Bedeutung der einzelnen Dateien im Detail:

* /admin/elements/ElementLastmodifiedAdmin.php
In dieser Datei befindet sich die Repräsentation des Seitenelements im Administrations-Bereich. Diese besteht aus dem Formular im Admin-Bereich zum Anlegen des Elements.
* /installer/InstallerElementLastmodified.php
Wie der Name schon sagt übernimmt diese Datei die Installation des Seitenelements in einem Kajona System. Dies wird vor allem dann interessant, wenn man das Seitenelement auch an andere Anwender weitergeben möchte.
* /portal/elements/ElementLastmodifiedPortal.php
Alles was im Portal passiert, wird in dieser Datei definiert. In unserem Fall wäre das nun die Ausgabe des Datums der letzten Modifikation der aktuellen Seite.
* /lang/module_elements/lang_lastmodified_de.php
In dieser Datei schließlich liegen die Texte, die das Element zur Aufbereitung der Daten für das Portal/Backend benötigt. Möchte man weitere Sprachen unterstützen, so muss lediglich eine weitere Datei mit entsprechendem Kürzel angelegt werden.
 
##Benötigte Klassen erstellen
Nachdem nun alle benötigten Dateien angelegt wurden, geht es nun an das Erstellen der benötigten Klassen, also des Programmcodes.

Beginnen möchten wir hier mit dem

###Installer

Der Dateiname des Installers des Seitenelements sollte nach dem Namensschema InstallerElementName.php aufgebaut sein, in unserem Fall also InstallerElementLastmodified.php. Der Installer ist die umfangreichste Klasse und soll nun im Detail erläutert werden. Der fertige Installer ist wie folgt aufgebaut (der Inhalt der gesamten Datei sowie aller anderen Dateien befindet sich auch noch einmal im Anhang dieses Dokuments).

```
<?php
```

Es soll mit der Anlage der Installer-Klasse begonnen werden. Wichtig ist hierbei, dass als Klassenname der Name der aktuellen Datei verwendet wird. Sonst kann Kajona keine Instanz der Klasse anlegen. Damit die Klasse auch als Installer-Klasse gekennzeichnet wird, muss diese das Interface „InstallerInterface“ implementieren, sowie von der Klasse „InstallerBase“ abgeleitet werden. So wird sichergestellt, dass alle benötigten Methoden und Helfer der Klasse zur Verfügung stehen.

```
class InstallerElementLastmodified extends InstallerBase implements InstallerInterface {
```

Im Installer des Elements werden dem System die Metadaten des Elements bekannt gemacht. Hierfür wird auf die sowieso schon bestehende metadata.xml-Datei referenziert und der Paketmanager mit dieser intialisiert (autoInit())

```
public function __construct() {
  $this->objMetadata = new PackagemanagerMetadata();
  $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR."installer", _realpath_), array("", ""), __DIR__));
  $this->setArrModuleEntry("moduleId", __pages_content_modul_id__modul_id_);
  parent::__construct();
}
```

In der Methode „install“ wird nun die Hauptarbeit gemacht – das Element wird im System angelegt.
Hierfür wird zu Beginn geprüft, ob das Element nicht bereits angelegt wurde. Ist dies nicht der Fall, dann wird eine neue Instanz der Klasse „PagesElement“ angelegt. Diesem Objekt wird dann über die Methode „setStrName“ der Name des Seitenelements mitgeteilt. Über diesen Wert wird das Seitenelement später den Platzhaltern zugeordnet. Wählt man also den Namen „lastmodified“, so werden in den Templates Platzhalter nach dem Schema „%%titel_lastmodified%% erwartet. Danach werden die Dateien genannt, in denen sich die Administrations- und die Portaldarstellung befinden. Mit Hilfe des Aufrufs von „setIntCachetime“ wird bestimmt, wie lange das Element cachebar ist. Per Default wird dieser Wert auf 60 gesetzt, ein gecachtes Element verfällt also nach 60 Sekunden. Die aktuelle Versionsnummer des Elements wird dem System durch „setStrVersion“ mitgeteilt. Bevor das Element durch „updateObjectToDb“ im System angelegt wird, wird durch „setIntRepeat“ festgelegt, ob das Element an einem Platzhalter wiederholt werden darf, oder nicht.

```
public function install() {
    $strReturn = "";
    //Register the element
    $strReturn .= "Registering lastmodified-element...\n";
    //check, if not already existing
    if(PagesElement::getElement("lastmodified") == null) {
        $objElement = new PagesElement();
        $objElement->setStrName("lastmodified");
        $objElement->setStrClassAdmin("ElementLastmodifiedAdmin.php");
        $objElement->setStrClassPortal("ElementLastmodifiedPortal.php");
        $objElement->setIntCachetime(60);
        $objElement->setIntRepeat(0);
        $objElement->setStrVersion($this->objMetadata->getStrVersion());
        $objElement->updateObjectToDb();
        $strReturn .= "Element registered...\n";
    }
    else {
        $strReturn .= "Element already installed!...\n";
    }
    return $strReturn;
}
```

Seit einigen Versionen ist es auch Elementen möglich, diese entsprechend mit Updates zu versorgen. Hierfür greift im Falle von Element die Methode „update“. Diese sollte in erster Instanz die aktuell vorliegende Version überprüfen und dann ggf. weiter Updates oder Aktionen starten. Die hierbei aufzurufenden Methoden sind frei definierbar.

```
public function update() {
    $strReturn = "";

    if(PagesElement::getElement("lastmodified")->getStrVersion() == "3.4.2") {
      $strReturn .= $this->postUpdate_342_349();
      $this->objDB->flushQueryCache();
    }
    return $strReturn;
}
public function postUpdate_342_349() {
    $strReturn = "Updating element lastmodified to 3.4.9...\n";
    $this->updateElementVersion("lastmodified", "3.4.9");
    return $strReturn;
}
```

###Admin-Darstellung
Nachdem nun der Installer angelegt wurde, geht es um die Darstellung des Elements in der Adminstration. 

```
<?php
```

Wie auch im Installer erfolgt zu Beginn die Definition der Klasse. Hierbei ist wichtig, dass der Klassenname dem Dateinamen entspricht. Ebenso muss das Interface „AdminElementInterface“ implementiert, sowie von der Klasse „ElementAdmin“ abgeleitet werden.

```
class ElementLastmodifiedAdmin extends ElementAdmin implements AdminElementInterface {
Im Konstruktor der Klasse wird lediglich der Name des Elements registriert – mehr nicht.
public function __construct() {
   $this->setArrModuleEntry("name", "element_lastmodified");
   parent::__construct();
}
```

Die nun folgende Methoden muss auf Grund des Interfaces definiert werden. Da das Element aber keine eigenen Daten in eigenen Tabellen speichert, bleibt der Methodenrumpf quasi leer und gibt nur den vom System erwarteten Rückgabewert zurück.

```
  public function getEditForm($arrElementData) {
  	return "";
  }

}
```



###Portal-Darstellung
Nachdem nun der Installer und der Teil der Administration angelegt wurden, geht es um die Darstellung des Elements im Portal. Vieles wiederholt sich hier, weswegen nur auf die neuen Aspekte eingegangen werden soll.

```
<?php

class ElementLastmodifiedPortal extends ElementPortal implements PortalElementInterface {

public function __construct($objElementData) {
	parent::__construct($objElementData);
}
```

Die eigentliche Logik steckt in der Methode „loadData“. Der Name der Methode kann hierbei nicht verändert werden, da dieser über das Interface vorgegeben wird. In der Methode wird nun ein Seiten-Objekt der aktuell geladenen Seite geholt (PagesPage::getPageByName($this->getPagename())). Danach wird dem Rückgabewert ein Text mit dem Titel „lastmodified“ hinzugefügt (dieser wird in einer Textdatei hinterlegt, siehe unten), sowie das durch die Funktion „timeToString“ formatierte Datum des letzten Bearbeitens der Seite – fertig. 

```
  public function loadData() {
    $strReturn = "";
    //load the current page
    $objPage = PagesPage::getPageByName($this->getPagename());
    $strReturn .= $this->getLang("lastmodified").timeToString($objPage->getIntLmTime());
    return $strReturn;
  }
}
```


###Texte
Um den Text „lastmodified“ laden zu können, muss dieser in der zu Beginn hinterlegten Textdatei eingetragen werden. Das ist in diesem Fall lediglich eine einzelne Zeile.

```
<?php
$lang["lastmodified"]                   = "Bearbeitet am: ";
```

###Metadata.xml
In den Metadaten werden die erforderlichen Informationen wie die Versionsummer aufgeführt:

```
<?xml version="1.0" encoding="UTF-8"?>
<package
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="http://apidocs.kajona.de/xsd/package.xsd"
    >
  <title>lastmodified</title>
  <description>Renders the date of the pages' last modification.</description>
  <version>3.4.9</version>
  <author>Kajona Team</author>
  <target>element_lastmodified</target>
  <type>MODULE</type>
  <providesInstaller>TRUE</providesInstaller>
  <requiredModules>
    <module name="system" version="3.4.9.3" />
    <module name="pages" version="3.4.9.1" />
  </requiredModules>
</package>
```

Die meisten der Werte sind dabei selbsterklärend. Besonderes Augenmerk sollte aber auf die Werte für „title“ und „target“ gelegt werden. Der Wert für „title“ muss mit dem Namen des Elements im System übereinstimmen. Anderenfalls können mögliche Updates des Elements nicht sauber durchgeführt werden. Das „target“ bestimmt den Ordnernamen des Elements unterhalb von /core, d.h. während der Installation des Elements wird dieses in diesen Ordner kopiert.

Und das wars schon. Fertig ist das erste, eigene Seitenelement. Nun muss dieses nur noch im System getestet werden. 

Viel Spaß beim Erstellen weiterer, eigener Seitenelemente. 

P.S.: Wir freuen uns immer darüber, wen neue Elemente und Funktionen auf der KajonaBase, http://www.kajonabase.net veröffentlicht werden.

