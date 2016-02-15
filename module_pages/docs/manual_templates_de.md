#Templates
Bisher wurde von Elementen und Einbetten von Content gesprochen – aber wo soll das denn passieren? Genau da kommen die Templates ins Spiel!

Was also ist ein Template? Templates werden in vielen System an vielen Stellen in vielen Formaten verwendet, deshalb sollen hier nur Templates in Kajona erläutert werden.
Unter einem Template versteht man eine Art „Dokumentvorlage“. Diese liegt allen Seiten zu Grunde und bestimmt so deren Layout. So ist es möglich, das Layout in einer Datei zu definieren, und diese Datei allen Seiten zuzuweisen. Möchte man nun das Layout verändern, so muss lediglich eine einzige Datei verändert werden.

Templates haben den Vorteil, dass durch die Verwendung von Templates die Inhalte strikt vom Layout getrennt werden. In den Templates stehen lediglich Platzhalter für Inhalte, die Inhalte befinden sich beispielsweise in der Datenbank. Bei einer Anfrage geht das System nun her und führt diese zu einer Seite zusammen.

##Platzhalter

Platzhalter werden immer dann benötigt, wenn das System aktiv werden soll.

Das bedeutet: Wenn in einem Template an einer bestimmten Stelle ein Text angezeigt werden soll, dann wird an dieser Stelle ein Platzhalter eingefügt. Dieser wird von Kajona ausgewertet und dann je nach Element mit hinterlegtem Content befüllt.
Diese Platzhalter haben in Kajona eine fest definierte Syntax. Diese soll an einem Beispiel erklärt werden:

	%%einleitung_paragraph%%

Als allererstes wird ersichtlich, dass Platzhalter von zwei Prozentzeichen umrahmt werden: %% leitet den Platzhalter ein und beendet diesen auch wieder. So werden Nebeneffekte mit normalem Text vermieden.
Als zweites fällt ein Art „Zweiteilung“ des Platzhalters auf: Ein Unterstrich trennt den Platzhalter in zwei Bereiche. 

Vor dem Unterstrich befindet sich der Titel des Platzhalters. Dieser dient nur dem Benutzer, um die Stelle im Template sinnvoll zu benennen. Im Admin-Bereich wird dieser Titel als Platzhalter mit ausgegeben, so weiß man beim Bearbeiten einer Seite schnell, um welche Stelle es denn überhaupt geht.
Zum anderen folgt nach dem Unterstrich das Element an sich.

In Kajona können beliebig viele Seitenelemente hinterlegt werden, die alle einen Titel bekommen. In diesem Beispiel würde es sich also um einen Absatz (ein Absatzelement) handeln.
So weiß Kajona sofort, welches Element geladen werden muss, wenn die Seite in der Administration oder im Portal aufgerufen wird.

Sollen an einem Platzhalter verschiedenen Seitenelemente möglich sein, so lassen sich diese durch ein Pipe-Symbol voneinander trennen:

	%%einleitung_paragraph|image%%
	
So können an diesem Platzhalter Bilder und Absätze angelegt werden, welche dann zueinander sortiert werden können.
Werden Platzhalter zu einem späteren Zeitpunkt erweitert, so muss das System darüber benachrichtigt werden. Dies erfolgt in der Seitenverwaltung mit Hilfe der Aktion „Platzhalter anpassen“.
Anmerkung: Wird im Platzhalter der Name eines Elements verwendet welches nicht installiert ist, so wird der Platzhalter im Admin-Bereich ausgeblendet!

##Seitentemplates
Seitentemplates sind die zentralen Templates, um die es in Kajona geht. Diese bestimmen das „grobe“ Layout des Portals. An sich sind Seitentemplates zu Beginn nichts anderes als normale HTML-Seiten, jedoch anstatt als *.html Dateien gespeichert, bekommen Templates in Kajona die Endung *.tpl.
Ein weiterer Unterschied zu normalen HTML-Seiten zeigt sich im Inhalt. Bei herkömmlichen Seiten würde man seinen Content einfach an die gewünschte Stelle im HTML-Baum schreiben. Bei Templates hingegen wird lediglich ein Platzhalter hinterlegt.
Merke: Anstatt des Contents wird einfach ein Platzhalter an die Stelle im HTML-Layout geschrieben. Der Content selber wird später über die Administration eingepflegt und verwaltet.
Ein Seitentemplate könnte so aussehen:

	<html>
		<head>[...]</head>
		<body>
		    [...]
			<div>
				%%headline_row%%
				<hr />
				%%content_paragraph%%
				%%intro_image%%
			</div>
		    [...]
		</body>
	</html>
	
##Elementtemplates
Elementtemplates stellen eine Eigenheit von Kajona dar, die das System enorm flexibel macht. Hat man nun im Seitentemplate einen Platzhalter hinterlegt und im Admin-Bereich bereits Content für den entsprechenden Platzhalter hinterlegt, so wird dieser ab sofort bei einem Aufruf des Portals dargestellt. Um auch diesen Content layouttechnisch anzupassen, gibt es Elementtemplates, die zwischen Seitentemplates und Content geschaltet werden.

Elementtemplates haben außerdem die Eigenheit, kein reguläres HTML-Dokument darzustellen, sondern lediglich einen Ausschnitt aus einem HTML-Dokument (Code-Snippet). Diese Abschnitte werden mit zuvor definierten Abschnitt-Tags eingeleitet. Ein häufiges Tag für listen ist das <list></list> Tag. Der Inhalt dieses Tags steuert dann, wie die Liste später aussehen soll.

Ein Elementtemplate könnte so aussehen:

	<list>
		Downloads: <br />
		%%rows%%
	</list>
	
	<list_row>
		&middot; %%filename%% &nbsp; &nbsp; %%dllink%%
	</list_row>

Wenn das System nun die Seite erzeugt, dann werden die Inhalte in die Templates geschrieben, und diese nacheinander zusammengebaut. Ein Ablauf könnte folgendem Schema entsprechen:

![](https://www.kajona.de/image.php?image=/files/images/upload/manual/011_platzhalter.png&maxWidth=500)

##Master-Seite & Mastertemplate
Die Masterseite ist eine Spezialseite im System. Diese trägt per Definition den Titel „master“ und kann auch nicht anders benannt werden.

Was aber ist der Sinn der Masterseite? Betrachten wir noch einmal kurz die Element-Situation: Zuvor wurde von vermittelnden Elemente gesprochen, also Elementen, die zwischen einer Seite und einem Modul vermitteln. Nun kann es durchaus Module geben, die auf jeder Seite angezeigt werden sollen. Ein Kandidat hierfür wäre das Modul Navigationen. Macht Sinn, den man möchte dem Benutzer schließlich auf jeder Seite die Navigation anbieten. Eine mögliche (und bisher die einzige Art) dies zu erreichen wäre, auf jeder Seite ein vermittelndes Element anzulegen, um die Navigation einzubinden. Diese Variante ist natürlich unschön, des wir sind ja schließlich faul ;).

Deshalb bietet Kajona für solche Fälle ein Special-Feature: Die Masterseite.
Ein paar kurze Sätze zum Prinzip der Masterseite: Auf der Masterseite werden, wie auf allen anderen Seiten auch, Elemente angelegt. Diese Elemente der Masterseite können nun aber auf allen anderen Seiten mit angezeigt werden, ohne diese erneut anlegen zu müssen. Im Beispiel der Navigation bedeutet dies, dass man die Navigation nur auf der Masterseite anlegt, auf allen anderen Seiten wird die Navigation automatisch eingebunden. 

Wie man das erreicht soll der nächste Abschnitt zeigen.
Ganz klar – eine Masterseite benötigt ein spezielles Template. Unterschied zu Seitentemplates: es besteht nicht aus einem HTML-Gerüst, sondern ist an sich erst mal komplett leer. In dieses leere Template werden dann alle Platzhalter geschrieben, für die globale Elemente angelegt werden. Da es sich um Master-Elemente handelt, haben diese eine spezielle Syntax, um sie von regulären Platzhaltern zu unterscheiden.
Kurze Wiederholung eines normalen Platzhalters: ``%%portalnavigation_navigation%%``. So würde der Platzhalter auf einer normalen Seite aussehen, um die Navigation einzubinden.
Auf der Master-Seite jedoch haben Platzhalter per Konvention das Schema ``%%mastertitel_element%%``. Es wird also die Zeichenkette „master“ vorne angestellt. Im Beispiel der Navigation würde der Platzhalter also lauten: ``%%masterportalnavigation_navigation%%``.
Diese Platzhalter können dann über den Admin-Bereich, wie alle andere Platzhalter auch, mit Content befüllt werden.
Nun ist noch die Frage offen, wie denn nun die Elemente der Master-Seite auf die normale Seite kommen. Denn diese Verbindung besteht ja nicht automatisch.
Dies lässt sich aber sehr sehr einfach erreichen: An die Stelle innerhalb des normalen Seitentemplates, an die ein Element der Masterseite eingefügt werden soll, wird ein weiterer Spezialplatzhalter eingefügt, analog nach dem Schema ``%%mastertitel_element%%``. (Der aufmerksame Beobachter erkennt: Genau der gleiche Platzhalter wie im Mastertemplate :) ).
In unserem Beispiel würde man also an die Stelle im Seitentemplate, an der die Navigation erscheinen soll, einen Platzhalter mit dem Titel ``%%masterportalnavigation_navigation%%`` einfügen. Und zack, bei einem Aufruf der Seite wird der Platzhalter automatisch mit dem identisch benannten Element der Masterseite befüllt.

##global_includes

Mit der Masterseite haben wir nun eine Möglichkeit kennen gelernt, Elemente nur einmal anzulegen aber überall anzuzeigen. Doch damit nicht genug, Kajona bietet eine weitere Möglichkeit, schnell und einfach kleine Elemente überall anzuzeigen.

Diese Möglichkeit sind globale Elemente. Warum aber eine weitere Möglichkeit, und wann benutzt man welche Methode, um Elemente überall anzuzeigen?

Die Elemente der Masterseite haben einen Vor- und Nachteil: Es sind reguläre Seitenelement. Das bedeutet, bei der Erzeugung durch das Portal werden verschiedene Dinge im Hintergrund erledigt, dazu gehört die Überprüfung, ob das Element aktiv oder inaktiv ist, oder natürlich auch, ob die Rechte ausreichen, um das Element anzuzeigen. Dies kostet natürlich Laufzeit, denn das alles passiert ja nicht von alleine.
Nun gibt es aber durchaus Elemente, bei denen man darauf verzichten kann, dass die oben genannten Punkte immer abgearbeitet werden. Ein Beispiel hierfür wäre das Anzeigen einer Copyright-Zeile: ``(c) 2005-2006 by mir``. Diese Zeile kann man nun selbstverständlich einfach in die Templates schreiben, also in das HTML-Layout einbauen. Was passiert aber 2007? Genau, es müssen alle Templates bearbeitet werden, um die Zeile anzupassen. Das ist natürlich Arbeit, die uns Kajona abnehmen könnte. Und genau hierfür gibt es globale Elemente.  Diese werden in der Datei ``global_includes.php`` im Verzeichnis /project/portal (eine Vorlage findet sich im Verzeichnis ``/core/module_pages/portal/global_includes.php``) hinterlegt und dann im Template eingebunden. In unserem Beispiel würden wir die Datei also einfach um Folgendes erweitern:

	$arrGlobal["copyright"] = "&copy; 2000 - 2013 kajona.de“;

In den Templates kann nun diese Zeile mit Hilfe des Platzhalters ``%%copyright%%`` eingefügt werden. Ist nun Silvester, und man möchte das Copyright anpassen, so muss lediglich diese eine Zeile in der Datei ``global_includes.php abgeändert werden, und schon erscheint auf allen Seiten das neue Copyright.

##Internationalisierte Textausgaben (i18n)
In den Elementtemplates ist es über eine einfache Konvention möglich, Texte aus den internationalisierten Language-Dateien (Ordner „/lang“) auszugeben.
Ein kleines Beispiel dazu:

	[lang,postacomment_write_new,postacomment]
	
im Template liest den Wert des Language-Keys „postacomment_write_new“ aus der entsprechenden Textdatei des Moduls Postacomment und fügt ihn in der Templateausgabe ein. In der Textdatei sieht die Zeile wie folgt aus:

	$lang["postacomment_write_new"]     = "Write a comment";
