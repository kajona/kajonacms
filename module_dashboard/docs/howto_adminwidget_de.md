# HowTo: Adminwidget

Das Dashboard des Kajona-Backends bietet die Möglichkeit, Widgets hinzuzufügen, zu löschen oder zu verschieben. Um eigene Funktionalitäten auf das Dashboard zu bringen bietet Kajona die Möglichkeit, eigene Widgets zu erstellen und auf das Dashboard zu legen.

Das Erstellen eines eigenen Widgets ist recht einfach und soll anhand des Wetter-Widgets beschrieben werden. Die hier beschriebene Vorgehensweise lässt sich einfach auf eigene Widgets übertragen und anwenden.

Das Wetter-Widget soll die aktuelle Temperatur sowie einen Vorhersagewert für einen zu definierenden Ort ausgeben können. Hierfür soll auf die Webservices von YAHOO zugegriffen werden. Um das Widget international einsetzen zu können sollte zwischen Grad Celsius und Fahrenheit umgeschaltet werden können.
Eine aktuelle Version dieses Widgets gibt es auch auf www.kajona.de zum Download.
Neue Widgets nehmen wir gerne in unsere Download-Archive auf, einfach per Forum, Mail oder Kontaktformular schreiben.

## Dateistruktur erstellen

Das im Folgenden entwickelte Widget soll natürlich nicht nur für das eigene System verwendet werden, sondern soll auch an andere Kajona-User weitergegeben werden können. Von daher sollte das Widget gleich in einer Version erstellt werden, die später weiter verteilbar ist.
In unserem Fall legen wir nun einen Ordner „module_weatherwidget“ an, der nachstehende Dateien und Ordner beinhaltet:

	module_weatherwidget
	    |- admin
	    |    |- widgets
	    |         |- AdminwidgetWeather.php
	    |
	    |- lang
	    |    |- module_adminwidget
	    |         |- lang_adminwidgetweather_de.php
	    |
	    |- metadata.xml
	    
	    
Doch nun zur Bedeutung der einzelnen Dateien im Detail:

* /admin/widgets/AdminwidgetWeather.php

Diese Datei beinhaltet die eigentliche Logik des Widgets. Hier wird die Ausgabe aufbereitet und an Kajona übergeben.
* /lang/module_adminwidget/lang_adminwidgetweather_de.php

Texte die auf der Oberfläche des Widgets angezeigt werden sind in dieser Datei erfasst.
* /metadata.xml

Enthält beschreibende Informationen über das Paket, hier das Widget

## Benötigte Klassen erstellen

Nachdem nun alle benötigten Dateien angelegt wurden, geht es nun an das Erstellen der benötigten Klasse, also des Programmcodes.
Da nur eine Klasse implementiert werden muss handelt es sich um das

###Widget

Wie in Kajona üblich gilt die Namenskonvention Dateiname = Klassenname. Im Vorfeld wurde die Datei bereits unter dem Namen „AdminwidgetWeather.php“ angelegt, dies führt dann zwingender maßen zum Klassennamen „AdminwidgetWeather“. Der Aufbau der Klasse wird durch das zu implementierende Interface und durch die abzuleitende Basisklasse definiert, Näheres folgt nun.
<?php
Die nun zu definierende Klasse muss von der Klasse „Adminwidget“ abgeleitet werden und das Interface „AdminwidgetInterface“ implementieren.

    class AdminwidgetWeather extends Adminwidget implements AdminwidgetInterface {
    
Der Konstruktor der Klasse wird, im Gegensatz zu vielen anderen Klassen, zur Konfiguration des Widgets verwendet. Hier werden die Felder definiert, die später gespeichert werden sollen. Gemäß den Anforderungen sind dies die Werte für die Einheit und den Ort. Diese werden über die Methode „setPersistenceKeys()“ der Basisklasse mitgeteilt.    

    public function __construct() {
        parent::__construct();
        //register the fields to be persisted and loaded
        $this->setPersistenceKeys(array("unit", "location"));
    }
    
Um alles Weitere, das Laden und Speichern der Daten, kümmert sich Kajona.  Der nächste Schritt ist die Methode „getEditForm()“. Wie der Name schon besagt erzeugt diese das Formular um das Widget zu bearbeiten. Da das Wetter-Widget zwei Werte zur Konfiguration besitzt sollten diese hier aufbereitet werden. Für das Einstellen der Einheit wird ein Dropdown-Feld verwendet, für den Ort ein Eingabefeld. Um dem Benutzer beim Ausfüllen des Ortes behilflich zu sein, soll ein Hinweisetext mit einem Verweis auf die Yahoo-Wetter-Seiten eingeblendet werden. Die Texte werden aus den Textdateien ausgelesen. Da bei einem späteren Bearbeiten auch die bisher eingegebenen Werte angezeigt werden sollen, kann auf diese über die Methode „getFieldValue()“ zugegriffen werden. Der Name entspricht hierbei den im Konstruktor definierten PersistenceKeys. Ebenso sollte die ID des Formularelements mit dem jeweiligen PersistenceKey übereinstimmen.

    public function getEditForm() {
       $strReturn = "";
       $strReturn .= $this->objToolkit->formInputDropdown("unit", 
             array("f" => $this->getText("weather_fahrenheit"), 
                    "c" => $this->getText("weather_celsius")), 
             $this->getText("weather_unit"), $this->getFieldValue("unit"));
       $strReturn .= $this->objToolkit->formTextRow($this->getText("weather_location_finder"));
       $strReturn .= $this->objToolkit->formInputText("location", $this->getText("weather_location") , $this->getFieldValue("location"));
        return $strReturn;
    }
    
Das erzeugte Formular sieht dann wie folgt aus:

![](https://www.kajona.de/image.php?image=/files/images/upload/manual/v4_widget_01.jpg&maxWidth=600)

Nachdem nun alle Daten erfasst wurden können diese auf dem Dashboard ausgewertet werden. Dies erfolgt in der Methode „getWidgetOutput()“. Auf die im System gespeicherten Werte kann wieder über die Methode „getFieldValue()“ zugegriffen werden.
 
Um die Ausgabe zu formatieren bietet die Basisklasse Ausgabemethoden wie „widgetText()“ und „widgetSeparator()“ an. An dieser Stelle sollte NICHT auf die Methoden des Toolkits zugegriffen werden.

Im Falle des Wetter-Widgets wird über den Remoteloader auf die Wetterdaten zugegriffen. Das Ergebnis in Form einer XML-Datei wird dann aufbereitet und dem Benutzer präsentiert. Da die Struktur der Datei hier nicht von Interesse ist wird dieser Teil nicht näher betrachtet. An sich kann in dieser Methode jedoch beliebiger Code zum Laden und Aufbereiten der darzustellenden Informationen ausgeführt werden.

    public function getWidgetOutput() {
        $strReturn = "";
        try {
	        $objRemoteloader = new Remoteloader();
	        $objRemoteloader->setStrHost("weather.yahooapis.com");
	        $objRemoteloader->setStrQueryParams("/forecastrss?p=".$this->getFieldValue("location")."&u=".$this->getFieldValue("unit"));
	        $strContent = $objRemoteloader->getRemoteContent();
        }
        catch (Exception $objExeption) {
        	$strContent = "";
        }
        [...]
        
        return $strReturn;
    }
    
Die Ausgabe führt zu einem Bild wie dem folgenden:

![](https://www.kajona.de/image.php?image=/files/images/upload/manual/v4_widget_02.jpg&maxWidth=377)


Last but not least folgt die Methode „getWidgetName“. Diese sollte einen kurzen Namen des Widgets zurückgeben. Dieser wird in den Dialogen und den Dropdowns des Dashboards verwendet.    

    public function getWidgetName() {
        return $this->getText("weather_name");
    }
    
Seit Version 4 muss ein Widget eine weitere Methode implementieren:

	public function onFistLogin($strUserid) {
	        return true;
	    }
	}

Diese Methode wird immer dann aufgerufen, wenn sich ein Anwender zum ersten Mal an einer Kajona-Installation anmeldet. In diesem Fall können Aktionen wir das Installieren des Widgets auf dem Dashboard des Anwenders ausgeführt werden. Sofern (wie hier) keine Aktion nötig ist, genügt es die Methode durch direktes Beenden (return true;) abzuschließen.

###Texte
Die Textdatei stellt alle benötigten Texte zur Verfügung. Diese sollten in der Regel selbsterklärend sein. 



###Metadata.xml
Im Falle des Widgets sind die zugehörigen Metadaten recht einfach aufgebaut. Sie dienen insbesondere der Paketverwaltung dazu, das Paket zu installieren oder nach Updates des Paketes zu suchen.

	<?xml version="1.0" encoding="UTF-8"?>
	<package
	        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	        xsi:noNamespaceSchemaLocation="http://apidocs.kajona.de/xsd/package.xsd"
	    >
	    
Der Titel benennt den Namen des Paketes samt Beschreibung im description-Tag. Die Version wird insbesondere zur Ermittlung möglicher Updates verwendet.

    <title>weatherwidget</title>
    <description>Renders the current weather-forecast.</description>
    <version>3.4.9</version>
    <author>Kajona Team</author>
    
Der Target-Tag ist von besonderer Relevanz: In dieses Verzeichnis wird das Paket unterhalb /core installiert.

    <target>module_weatherwidget</target>
    <type>MODULE</type>
    
Im Falle des Widgets beinhaltet dieses keinen Installer, somit wird als Wert „false“ gesetzt. Als Vorraussetzung werden das system-Modul und das Dashboard zwingend genannt.

	    <providesInstaller>FALSE</providesInstaller>
	    <requiredModules>
	        <module name="system" version="3.4.9.3" />
	        <module name="dasjhboard" version="3.4.9" />
	    </requiredModules>
	</package>