#Seitenverwaltung

Das Modul Pages ist eines der wichtigsten Module des Systems, es steuert alle Portalausgaben und verwaltet die Seiten samt ihren Inhalten. Mit der Seitenverwaltung können Seiten editiert, bearbeitet, gelöscht und in verschiedenen Hierarchien kategorisiert werden.

Aus historischen Gründen werden Ordner nach wie vor voll unterstützt, die Anlage neuer Ordner ist aber nur noch auf oberster Ebene möglich. Dort werden diese vor Allem zum Kennzeichnen der einzelnen Navigationsbäume verwendet (vergl. Handbuch Navigationen, automatische Navigationen).

In einer Hierarchie an Seiten kann jede Seite eine beliebige Anzahl an Unterseiten umfassen, vergebene Rechte werden dann die Unterseiten weiter vererbt.

Über die Aktion „Neue Seite“ kann eine neue Seite im System hinterlegt werden. Dabei werden das zu verwendende Template für die Seite sowie Zusatzinformationen wie der Browser-Seitentitel festgelegt. Jeder Seitenname muss dabei im System eindeutig sein!

In der Seitenliste können die Seiteninhalte per Klick auf das Symbol „Seiteninhalte bearbeiten“ verwaltet werden. Sind mehrere Portalsprachen eingerichtet, so lassen sich die Inhalte pro Sprache definieren.
Hierbei wird das verwendete Template auf Platzhalter analysiert, mit den im System hinterlegten Elementen abgeglichen und nach bereits angelegten Platzhaltern gesucht. Lässt es das Element zu (es ist also „wiederholbar“), dann kann an einem Platzhalter ein Element mehrfach angelegt werden, ansonsten natürlich nur einmal.

Die Formulare der Elemente werden von den Elementen selbst gesteuert, diese werden von der Seitenverwaltung aufgerufen.
Außerdem kann über die Seitenverwaltung der Status von Seiten und Elementen verändert werden: Eine Anzeige im Portal erfolgt nur mit dem Status aktiv.
Seitenelemente sind kleine Bausteine, die später den Inhalt auf der Seite darstellen. Seitenelemente bestehen immer aus einer Klasse, die die Eingabe im Admin-Bereich steuert, so wie einer Klasse, die die Ausgabe im Portal-Bereich steuert.

Neue Seitenelemente lassen sich mit der Aktion „Neues Element“ anlegen. Eine Liste der verfügbaren Seitenelemente erhält man über die Aktion „Elemente“. Die hier aufgelisteten Elemente können im zweiten Teil des Platzhalters in den Templates verwendet werden. Wird in einem Template ein Element angegeben, welches dem System unbekannt ist, so wird dieser Platzhalter später auch nicht ausgegeben.

In den Eigenschaften des Elements kann angegeben werden, ob es Wiederholbar ist, oder nicht. Wenn ein Element nicht wiederholbar ist, kann es pro Platzhalter nur einmal angelegt werden, sollte es wiederholbar sein, so kann es mehrfach angelegt werden.
Ebenfalls kann die Cachedauer des Elements bestimmt werden. Es gilt immer: Das Element mit der kleinsten Cachedauer bestimmt die maximale Zeit der Seite im Seitencache.

Seitentemplates können mit einer Liste an globalen Platzhaltern befüllt werden, hierzu gehört der Seitentitel, die Description oder Ähnliches, siehe unten. Der Seitentitel kann hierbei von Modulen dynamisch ergänzt werden. So könnte beispielsweise der Name eines Newsartikels in den Titel aufgenommen werden. Ein bisheriger Titel bei einer News-Detailansicht wäre zum Beispiel `Kajona³ [News]` gewesen. Nun wird aber ein zusätzlicher Text automatisch mit angehängt, so dass daraus wird: `Kajona³ [News | Kajona erfolgreich installiert]`. Zu beachten ist hierbei, dass der fixe Title vom dynamischen Title durch einen Trenner, hier ` | ` abgetrennt ist. Dieser Trenner wird in der Datei `global_includes.php` (`/core/module_pages/portal/global_includes.php`) definiert und wird nur dann eingefügt, wenn ein dynamischer Titel vorhanden ist. Der Trenner kann in der Datei beliebig angepasst werden (zuvor sollte die Datei jedoch nach `/project/portal/global_includes.php` kopiert werden).

##Settings
Die Startseite legt logischerweise fest, welche Seite als Startseite ausgeliefert wird, also immer dann, wenn kein expliziter Seitenname übergeben wurde.

Das „Standardtemplate“ legt fest, welches der vorhandenen Templates bei neuen Seiten als Template vorausgewählt sein soll.

Die Fehlerseite legt eine Seite fest, die immer dann geladen wird, wenn es zu einem Fehler kam. Dies kann der Fall sein, wenn eine Seite nicht gefunden wurde, zum Beispiel wenn keine Startseite definiert wurde oder wenn die Rechte für die angeforderte Seite nicht ausreichend sind.

Die Einstellung „Templatewechsel“ schließlich legt fest, ob das Ändern des Templates einer Seite möglich ist, wenn auf dieser bereits Elemente angelegt wurden. Wird dies zugelassen, so kann dies unter Umständen zu Geist-Datensätzen und unerwarteten Nebeneffekten führen (Was passiert mit einem Element, das zuvor im Template vorhanden war, nun aber nicht mehr vorhanden ist?).

Mit dem Wert der Einstellung „Neue Seiten inaktiv“ wird festgelegt, ob neue Seiten beim Anlegen aktiv oder inaktiv geschaltet werden sollen. Sind Seiten inaktiv, so ist eine Vorschau über den Link „Vorschau anzeigen“ in der Seitenverwaltung trotzdem möglich.

Der Seitencache dient zum Zwischenspeichern bereits erzeugter Seiten. So müssen diese, bei einer erneuten Anfrage, nicht komplett neu generiert werden, sondern können sofort ausgeliefert werden. Aktiviert oder deaktiviert wird dieser über die Einstellung „Seitencache aktiv“.

Über den Wert der Einstellung „Portaleditor aktiv“ wird das Verhalten des Portaleditors eingestellt. Wenn der Wert auf true steht, und der aktuelle Benutzer die benötigten Rechte besitzt, dann können Seiten auch direkt komfortable im Portal editiert werden.

##Seitencache
Der Seitencache sorgt dafür, dass eine Seite beim ersten Aufruf und damit dem ersten Erzeugen in der Datenbank gespeichert wird. Somit wird bei einem späteren Aufruf der Seite diese nicht neu erzeugt, sondern aus dem Cache geladen und von dort ausgeliefert – was sich mit einem deutlichen Performancegewinn bemerkbar macht.
 
Im Gegensatz zu früheren Version wird ab Kajona Version 3.3.1 nicht mehr die gesamte Seite im Cache gespeichert.
Statt dessen wird für jedes Element auf der Seite die aktuelle Ausgabe gespeichert.

Das bedeutet, dass beim Laden einer Seite die einzelnen Elemente entweder aus dem Cache geladen werden, oder neu erzeugt werden.
Das hat den Vorteil, dass eine Seite sowohl aus Elemente bestehen kann die gecached werden können (bspw. Absätze), aber auch Elemente beinhalten kann die sich nicht zwischenspeichern lassen (bspw. Ein Formular, hier soll ja jeder Anwender seine entsprechende Antwort sehen).

Um hier eine gute Balance aus Cachen und Neugenerieren zu erhalten, kann jedes Element entscheiden, wie lange es maximal zwischengespeichert wird. Wird diese Dauer überschritten, dann wird dieses Element neu erzeugt – unabhängig davon, ob auch andere Elemente auf der Seite neu erzeugt werden müssen. Die Dauer im Cache wird in dem Eigenschaften eines Elements eingestellt und in Sekunden angegeben. Der hier verwendetet Wert kann generell eher hoch gesetzt werden – beim Bearbeiten eines Seitenelementes werden die entsprechenden, nun nicht mehr gültigen, Einträge automatisch aus dem Cache entfernt. Darf ein Element nicht zwischengespeichert werden, so kann eine Cachedauer von -1 gesetzt werden.
Jedoch sollten die Einstellungen des Seitencaches mit Vorsicht vorgenommen werden, um Nebeneffekte zu vermeiden. Zur Erklärung dient das Szenario einer Newsseite. Diese ist im Portal ganz normal verlinkt und zeigt die aktuellen News in der Listen- und Detailansicht an. Stellt man nun die Cachezeit des News-Elements auf eine lange Cachedauer, so treten verschiedene Folgeeffekte ein. Hierzu gehört, dass neue News, die laut Newsgrunddaten schon seit 10 Uhr online sein sollten, erst dann erscheinen, wenn die Cachezeit abgelaufen ist. 
Zusammenfassend sei gesagt, dass nicht alle Elemente pauschal eine lange Cachedauer bekommen sollten, sondern individuell für jedes Element abgeschätzt werden sollte, welche Dauer vertretbar ist.

Im Fall von durchgeführten Änderungen, die aber nicht im Portal sichtbar werden, kann der Seitencache verantwortlich sein. In diesem Fall kann es sinnvoll sein, diesen zu leeren. Das Leeren des Caches ist mit Hilfe eines System-Tasks möglich.


##Seitenelemente

Bevor man mit dem System arbeitet, sollte man den Gedanken hinter den Elementen verstanden haben und die zwei grundlegenden Typen von Elementen kennen.
Elemente werden immer dann verwendet, wenn über den Admin-Bereich erfasste Inhalte im Portal präsentiert  werden sollen. Das kann zum Beispiel ein Text-Absatz sein, kann aber auch eine Bildergalerie sein.
Hierfür unterscheidet Kajona zwischen zwei Arten von Elementen: 

* direkte Elemente und
* vermittelnde Elemente

Diese beiden Typen sollen nun genauer beleuchtet werden.

###Direkte Elemente
Direkte Elemente werden immer dann eingesetzt, wenn die Inhalte direkt beim Element hinterlegt werden können. Dies ist beispielsweise bei Überschriften oder Absätzen der Fall. Als kleine Grafik könnte man das so darstellen:
![](https://www.kajona.de/image.php?image=/files/images/upload/manual/007_direkteselement.png&maxWidth=500)

Man erkennt: Wird das Portal aufgerufen, so lädt dieses das Element. Das Element kann dann sofort die zuvor hinterlegten Informationen an das Portal zurückgeben – es liefert den Content direkt.

###Vermittelnde Elemente
Im Gegensatz zu direkten Elementen stellen vermittelnde Elemente lediglich eine Art Schnittstelle zu anderen Modulen dar. Das bedeutet: Der Inhalt wird in den jeweiligen Modulen hinterlegt und gepflegt. Das Element stellt dann lediglich ein Bindeglied zwischen Portal und Modul dar:

![](https://www.kajona.de/image.php?image=/files/images/upload/manual/008_vermittelndeselement.png&maxWidth=500)

Es wird also deutlich: Das Element fungiert lediglich in vermittelnder Rolle. 
Auch hier wird bei einem Aufruf des Portals das Element gebeten, den Inhalt zu liefern. Da dieses aber „nur“ auf ein Modul verweist, leitet das Element die Anfrage an das entsprechende Modul weiter. Das Modul kann nun eigenständig den zuvor hinterlegten Content laden und an das Element zurückgeben. Dieses leitet den Content dann an das Portal weiter, welches dann den Content in die Antwort auf die Anfrage einbettet.

**Zusammenfassend**:
Ein Element wird immer dann benötigt, wenn Inhalt in die Seiten des Portal geschrieben werden soll. Soll hierbei einfacher Content wie Text dargestellt werden, so kommt ein direktes Element zum Einsatz, soll aber komplexerer Content wie eine Bildergalerie oder Zeit gesteuerte News dargestellt werden, so übergibt das Element die Anfrage an das entsprechende Modul und dient lediglich als vermittelndes Element.

