#Simple Pageelement


Mit diesem kleinen Tutorial soll der Aufbau einfachster Seitenelemente in Kajona beschrieben werden. Das Tutorial beschreibt hierfür die Entwicklung eines Seitenelements zur Ausgabe des Datums des letzten Bearbeitens der aktuellen Seite – Besucher der Internetseite können so  schnell erkennen, ob die Seite seit dem letzten Besuch verändert wurde, oder nicht. Das Element „lastmodified“ benötigt hierfür keine eigenen Datenbankeinträge, weswegen auf die Erstellung von Elementen mit Tabellen hier nicht eingegangen wird.

Analog zu diesem Seitenelement lassen sich natürlich auch andere, einfache Seitenelemente aufbauen.
Eine aktuelle Version dieses Seitenelements gibt es auch auf www.kajona.de zum Download.

> Dieses Dokument basiert auf Kajona 4.2. Einige der genannten Funktionen haben sich in Version 4.3 vereinfacht. Kajona 4.3 ist jedoch rückwärtskompatibel, so dass die genannten Funktionen nach wie vor funktionieren.
> Weitere Informationen finden sich auf http://www.kajona.de.


##Dateistruktur erstellen

Das im Folgenden entwickelte Seitenelement soll natürlich nicht nur für das eigene System verwendet werden, sondern soll auch an andere Kajona-User weitergegeben werden können. Von daher sollte das Seitenelement gleich in einer Version erstellt werden, die später weiter verteilbar ist.


Doch nun zur Bedeutung der einzelnen Dateien im Detail:

* /admin/elements/class_element_lastmodified_admin.php
In dieser Datei befindet sich die Repräsentation des Seitenelements im Administrations-Bereich. Diese besteht aus dem Formular im Admin-Bereich zum Anlegen des Elements.
* /installer/class_installer_element_lastmodified.php
Wie der Name schon sagt übernimmt diese Datei die Installation des Seitenelements in einem Kajona System. Dies wird vor allem dann interessant, wenn man das Seitenelement auch an andere Anwender weitergeben möchte.
* /portal/elements/class_element_lastmodified_portal.php
Alles was im Portal passiert, wird in dieser Datei definiert. In unserem Fall wäre das nun die Ausgabe des Datums der letzten Modifikation der aktuellen Seite.
* /lang/module_elements/lang_lastmodified_de.php
In dieser Datei schließlich liegen die Texte, die das Element zur Aufbereitung der Daten für das Portal/Backend benötigt. Möchte man weitere Sprachen unterstützen, so muss lediglich eine weitere Datei mit entsprechendem Kürzel angelegt werden.
 
##Benötigte Klassen erstellen
Nachdem nun alle benötigten Dateien angelegt wurden, geht es nun an das Erstellen der benötigten Klassen, also des Programmcodes.

Beginnen möchten wir hier mit dem

###Installer

Der Dateiname des Installers des Seitenelements sollte nach dem Namensschema class_installer_element_name.php aufgebaut sein, in unserem Fall also class_installer_element_lastmodified.php. Der Installer ist die umfangreichste Klasse und soll nun im Detail erläutert werden. Der fertige Installer ist wie folgt aufgebaut (der Inhalt der gesamten Datei sowie aller anderen Dateien befindet sich auch noch einmal im Anhang dieses Dokuments).

```
<?php
```

Es soll mit der Anlage der Installer-Klasse begonnen werden. Wichtig ist hierbei, dass als Klassenname der Name der aktuellen Datei verwendet wird. Sonst kann Kajona keine Instanz der Klasse anlegen. Damit die Klasse auch als Installer-Klasse gekennzeichnet wird, muss diese das Interface „interface_installer“ implementieren, sowie von der Klasse „class_installer_base“ abgeleitet werden. So wird sichergestellt, dass alle benötigten Methoden und Helfer der Klasse zur Verfügung stehen.

```
class class_installer_element_lastmodified extends class_installer_base implements interface_installer {
```

Im Installer des Elements werden dem System die Metadaten des Elements bekannt gemacht. Hierfür wird auf die sowieso schon bestehende metadata.xml-Datei referenziert und der Paketmanager mit dieser intialisiert (autoInit())

```
public function __construct() {
  $this->objMetadata = new class_module_packagemanager_metadata();
  $this->objMetadata->autoInit(uniStrReplace(array(DIRECTORY_SEPARATOR."installer", _realpath_), array("", ""), __DIR__));
  $this->setArrModuleEntry("moduleId", __pages_content_modul_id__modul_id_);
  parent::__construct();
}
```

In der Methode „install“ wird nun die Hauptarbeit gemacht – das Element wird im System angelegt.
Hierfür wird zu Beginn geprüft, ob das Element nicht bereits angelegt wurde. Ist dies nicht der Fall, dann wird eine neue Instanz der Klasse „class_module_pages_element“ angelegt. Diesem Objekt wird dann über die Methode „setStrName“ der Name des Seitenelements mitgeteilt. Über diesen Wert wird das Seitenelement später den Platzhaltern zugeordnet. Wählt man also den Namen „lastmodified“, so werden in den Templates Platzhalter nach dem Schema „%%titel_lastmodified%% erwartet. Danach werden die Dateien genannt, in denen sich die Administrations- und die Portaldarstellung befinden. Mit Hilfe des Aufrufs von „setIntCachetime“ wird bestimmt, wie lange das Element cachebar ist. Per Default wird dieser Wert auf 60 gesetzt, ein gecachtes Element verfällt also nach 60 Sekunden. Die aktuelle Versionsnummer des Elements wird dem System durch „setStrVersion“ mitgeteilt. Bevor das Element durch „updateObjectToDb“ im System angelegt wird, wird durch „setIntRepeat“ festgelegt, ob das Element an einem Platzhalter wiederholt werden darf, oder nicht.

```
public function install() {
    $strReturn = "";
    //Register the element
    $strReturn .= "Registering lastmodified-element...\n";
    //check, if not already existing
    if(class_module_pages_element::getElement("lastmodified") == null) {
        $objElement = new class_module_pages_element();
        $objElement->setStrName("lastmodified");
        $objElement->setStrClassAdmin("class_element_lastmodified_admin.php");
        $objElement->setStrClassPortal("class_element_lastmodified_portal.php");
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

    if(class_module_pages_element::getElement("lastmodified")->getStrVersion() == "3.4.2") {
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

Wie auch im Installer erfolgt zu Beginn die Definition der Klasse. Hierbei ist wichtig, dass der Klassenname dem Dateinamen entspricht. Ebenso muss das Interface „interface_admin_element“ implementiert, sowie von der Klasse „class_element_admin“ abgeleitet werden.

```
class class_element_lastmodified_admin extends class_element_admin implements interface_admin_element {
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

class class_element_lastmodified_portal extends class_element_portal implements interface_portal_element {

public function __construct($objElementData) {
	parent::__construct($objElementData);
}
```

Die eigentliche Logik steckt in der Methode „loadData“. Der Name der Methode kann hierbei nicht verändert werden, da dieser über das Interface vorgegeben wird. In der Methode wird nun ein Seiten-Objekt der aktuell geladenen Seite geholt (class_module_pages_page::getPageByName($this->getPagename())). Danach wird dem Rückgabewert ein Text mit dem Titel „lastmodified“ hinzugefügt (dieser wird in einer Textdatei hinterlegt, siehe unten), sowie das durch die Funktion „timeToString“ formatierte Datum des letzten Bearbeitens der Seite – fertig. 

```
  public function loadData() {
    $strReturn = "";
    //load the current page
    $objPage = class_module_pages_page::getPageByName($this->getPagename());
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
  <type>ELEMENT</type>
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














In unserem Fall legen wir nun einen Ordner „element_lastmodified“ an, der nachstehende Dateien und Ordner beinhaltet:

```
element_lastmodified
    |- admin
    |    |- elements
    |         |- class_element_lastmodified_admin.php
    |
    |- installer
    |    |- class_installer_element_lastmodified.php
    |
    |- lang
    |    |- module_elements
    |         |- lang_lastmodified_[de|en|bg|pt].php
    |- portal
    |    |-elements
    |         |- class_element_lastmodified_portal.php
    |
    |- metadata.xml
```    
    
To get a first understanding of the structure, we'll have a look at each file:

* /admin/elements/class_element_lastmodified_admin.php
This file contains the backend-representation of the element. As soon as a user creates or edits a lastmodified-element, this class takes care of the backend-view.

* /installer/class_installer_element_lastmodified.php
As the name already indicates, the installer takes care of setting up the element during the installation of the element. The installation may be run during a full system-installation or afterwards, when adding the element to an installation already existing. 

* /portal/elements/class_element_lastmodified_portal.php
The portal-class takes care of rendering the contents on a portal-page. It is called each time a portal-page is generated, e.g. when a visitor opens the page in a web browser.

* /lang/module_elements/lang_lastmodified_en.php
All strings and translations are placed in a lang-file. If you want to provide translations for different languages, add additional files with the matching suffix (e.g. lang_lastmodified_de.php containing the german translations).

* /metadata.xml
The metadata.xml file contains a description of the element and requirements.
 
##Implementing the required classes
Each file contains a special part of the element, therefore we'll step through each of them.

###Installer
The filename of the installer is based on the scheme class_installer_element_name.php, in this case class_installer_element_lastmodified.php. 

The main purpose of the installer is to register the element with the pages-module, otherwise the element is unknown and could not be created using the backend.

Let's step through the installer line by line:

```
<?php
```

We start with the declaration of the installer-class. By definition, an installer has to extend the class_installer_base base-class, inheriting all relevant methods. In addition, the implementation of the interface interface_installer ensures you provide all relevant methods.

Please note the annotation `@moduleId`. This annotation is required in order to categorize the element within Kajonas system-structure. For all page-elements, the @moduleId value is _pages_content_modul_id_, only modules may use other values.

```
/**
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_lastmodified extends class_installer_base implements interface_installer {
```

The “install” method is the main method of the installer and being called by the framework. The method is used to set up all relevant data and to register the element with the system.

Therefore we check if the element is already installed since we don't want to have the element being registered twice.

If the element is still missing, the installation is handled by a new instance of the class “class_module_pages_element”. We use the object to pass all relevant properties and settings of the lastmodified element:

* setStrName is used to register the name of the new element, “lastmodified”. By this name the element may be used in the portal and templates. When registering the element named “lastmodified”, a valid syntax for placeholders in templates would be %%title_lastmodified%%.
* setStrClassAdmin stores the filename of the admin-representation.
 *setStrClassPortal stores the filename of the portal-representation.
* setIntCachetime takes the maximum number of seconds a generated portal-representation may be cached. This means, a generated portal-output won't be regenerated for the given amount of seconds.
* setIntRepeat allows or disallows to have more then one instance of the element per placeholder (the element is “repeatable”).
* setStrVersion passes the version of the current element from the metadata.xml file to the system.

By calling updateObjectToDb(), the passed data is stored to the database and the element is registered within the system. 

```
public function install() {
    $strReturn = "";
    //Register the element
    $strReturn .= "Registering lastmodified-element...\n";
    //check, if not already existing
    if(class_module_pages_element::getElement("lastmodified") == null) {
        $objElement = new class_module_pages_element();
        $objElement->setStrName("lastmodified");
        $objElement->setStrClassAdmin("class_element_lastmodified_admin.php");
        $objElement->setStrClassPortal("class_element_lastmodified_portal.php");
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

Page-elements may be updated as soon as a new version is released (the version found in the package is higher then the version of the installed element). In case of an update, the framework calls the update() method. Place your update-sequences (if required) within the method or an additional update-method. Make sure to update at least the version of the element to the latest one.

```
public function update() {
  $strReturn = "";

  if(class_module_pages_element::getElement("lastmodified")->getStrVersion() == "4.2") {
      $strReturn .= "Updating element lastmodified to 4.3...\n";
      $this->updateElementVersion("lastmodified", "4.3");
      $this->objDB->flushQueryCache();
  }
  return $strReturn;
}
```

###Backend-View
When placing an element on a page, a simple form is shown to enter all relevant data. Since the lastmodified element doesn't handle any additional settings, the backend-class is kept rather short.

By convention, an elements' backend class has to extend the base-class class_element_admin and implement the interface interface_admin_element.

Nevertheless, the lastmodified-element class remains empty:

```
<?php

class class_element_lastmodified_admin extends class_element_admin implements interface_admin_element {  

}
```

Since the element doesn't take any arguments, no additional code has to be added. If the element could be parametrized, the different properties would be added (see the tutorial “complex page element”).

When creating a new lastmodified element using the backend, the framwork creates the following form:

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/kajona_howto_simplepageelement_en.odt.png&maxWidth=557)

###Portal-View
The third file is the portal-class of the element. As you already guessed, the portal-class has to extend a base-class and implement an interface, too.

In this case, the base class is class_element_portal whereas the interface is named interface_portal_element.

The interface guarantees you implement the method “loadData”, the hook-method being called by the framework as soon as a page is being generated.

```
<?php

class class_element_lastmodified_portal extends class_element_portal implements interface_portal_element {
```

The real work is done in loadData. Since we want to print the date of the last modification of the current page, the first task is to fetch an instance of the current page. This is done via class_module_pages_page::getPageByName(). By using $this->getPagename() the framework looks up the name of the page being generated and passes it to the factory-method getPageByName.
 
By querying the page-object using getIntLmTime(), the date of the last modification is returned as an unix-timestamp. timeToString simply transforms the timestamp to a readable string.

Since a prefix like “Last modified:” would be nice, the text is loaded from the lang-file (see below) using $this->getLang().

```
  public function loadData() {
    $strReturn = "";
    //load the current page
    $objPage = class_module_pages_page::getPageByName($this->getPagename());
    $strReturn .= $this->getLang("lastmodified").timeToString($objPage->getIntLmTime());
    return $strReturn;
  }
}
```

The generated output looks like following:


###Language-Entries
In order to load a lang-entry named “lastmodified”, the entry has to be placed into the lang-file, here lang_lastmodified_en.php.

The file contains two entries: “element_lastmodified_name” is a placeholder being loaded by the framework in order to label the “new”-button when editing a pages' contents. The name of the entry is defined by convention: “element_name_name”.

```
<?php
$lang["element_lastmodified_name"]       = "Date of last modification";
$lang["lastmodified"]                    = "Last modified: ";
```

The second entry, „lastmodifed“ is the text being loaded during the portal-generation of the class. The key „lastmodified“ is the same as when calling $this->getLang(„lastmodified“) and therefore the missing glue between the lang-file and the language-object.

###Metadata.xml
The metadata.xml file contains a descriptive xml-document. It contains general information such as the name of the element or the description, but also technical requirements and the author of the element. Since the metadata.xml is parsed by both, the package-management and the KajonaBase (the central repository for user-extensions), this file is the place to earn all the glory for the element.

```
<?xml version="1.0" encoding="UTF-8"?>
<package
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="http://apidocs.kajona.de/xsd/package.xsd"
    >
  <title>lastmodified</title>
  <description>Renders the date of the pages' last modification.</description>
  <version>4.3</version>
  <author>Kajona Team</author>
  <type>ELEMENT</type>
  <providesInstaller>TRUE</providesInstaller>
  <requiredModules>
    <module name="system" version="4.3" />
    <module name="pages" version="4.3" />
  </requiredModules>
</package>
```

Most of the values are self-describing and require no further explanation.
Make sure that the value of the title-element is the same as the name of the element when installing the element, otherwise the installer may get confused when searching for elements.


That's it, your first element is ready to be used. Deploy it to your local system and give it a try.
If you want to spread your element, make use of the KajonaBase to publish the element to other users. The website contains all relevant information on how to package and upload your element: http://www.kajonabase.net 


Have fun extending Kajona!