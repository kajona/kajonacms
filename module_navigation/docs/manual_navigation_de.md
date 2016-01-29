#Navigation

Im Modul Navigation werden die im System zur Verfügung stehenden Navigationen angelegt. Jede Navigation kann beliebig viele Unterpunkte beinhalten und auf externe oder interne Seiten als auch auf eine Datei verweisen.

Ist der System-Status eines Navigationspunktes auf inaktiv gesetzt, so erscheint er nicht im Portal, entsprechendes auch bei fehlenden Rechten.

Das Layout der Navigation wird über das Navigations-Element festgelegt. Hierzu wird der Navigation ein Template zugewiesen. Durch eine Vielzahl von möglichen dynamischen Template-Abschnitten (z.B. level_1_active, level_2_inactive) können alle Ebenen in allen Zuständen genau definiert werden.
Jede Ebene der Navigation wird durch den Template-Abschnite „level_x_wrapper“ umschlossen.
Sämtliche Modellierung der Navigation erfolgt über die Templates, wobei es hier zwei grundlegende Arten gibt:

1. Sitemap-Modus
Im Sitemap-Modus werden alle verfügbaren Navigationspunkte angezeigt. Bei vielen modernen Webseiten wird die Navigation so realisiert, da auf diese Weise alle Unterseiten durch ein Menü erreichbar sind.

2. Baum-Modus
Im Baum-Modus werden alle Punkte der Ebene 1 dargestellt, weiter Unterebenen werden nur für aktive Navigationspunkte angezeigt (aufgeklappt).
Beide Varianten können rein über die Ausgestaltung der Templates dargestellt werden: Während im Seitemap-Modus sowohl bei aktiven als auch inaktive Elementen (level_x_active, level_x_inactive) auf die nächste Ebene verlinkt wird (Eintrag %%level(x+1)%%), erfolgt im Baum-Modus die Angabe des nächsten Levels nur in den active-Abschnitten.

Seit Kajona 2.1 unterstützt Kajona die optionalen Abschnitte „first“ sowie „last“, um das Layout der Navigation noch flexibler zu halten.

Mit der Version 3.4 wurden die Navigationen um einen grundlegend neuen Modus erweitert: Automatische Naivgationen. Diese werden auf Basis der Seitenstruktur automatisch generiert, ein manuelles Anlegen von Navigationspunkten ist dabei nicht mehr nötig und auch nicht mehr möglich. Wurden die Seiten bisher in Ordnerstrukturen gegliedert, so wird dieses Verhalten in Zukunft durch eine Hierarchie in der Seitenverwaltung abgebildet.

Die Ordner werden aber nach wie vor benötigt, um den Einstieg in eine solche Navigation zu markieren.

D.h. es gibt auf oberster Ebene für jeden Navigationsbaum einen Ordner, darin liegt dann eine Hierarchie an Seiten (Seiten und Unterseiten). Eine Navigation kann nun entweder einen manuell gepflegten Navigationsbaum umfassen (wie in Kajona <3.4) oder ab 3.4 auch den Verweis auf einen Ordner. Sobald ein Ordner angegeben wurde, ist die manuelle Pflege nicht weiter möglich da die Navigationspunkte automatisch generiert werden.
Hierbei ist zu beachten, dass eine Seite nur dann in die Navigation eingefügt wird, wenn auf dieser Inhalte angelegt wurden. Leer Seiten werden in automatisch generierte Navigationsbäume nicht aufgenommen.

Nun kann es ja aber noch den Fall geben, dass bspw. eine Portal- und eine Hauptnavigation automatisch erstellt werden soll, die Home-Seite aber in beiden Navigationen erscheinen soll. Da  eine Navigation immer aus genau einer (!) Hierarchie gebaut wird, müsste man die Home-Seite an sich doppelt in beiden Hierarchien pflegen. Um auch diesen Fall abbilden zu können wurden sogenannte Alias-Seiten eingeführt, die letzten Endes nur ein Verweis auf eine andere, bestehende Seite darstellen. D.h. man kann die Home-Seite im Baum der Hauptnavigation ablegen, im Baum der Portalnavigation hingegen verwendet man einen Alias auf die Home-Seite des Hauptbaumes.

Als neue Funktion wurde in Kajona Version 4 die automatische Ergänzung von Navigationspunkten durch Dritt-Module ergänzt. Liegt auf einer Seite eine Bildergalerie mit zugehörigen Unterordnern vor, so kann das Galerie-Element die Unterordner in den Navigationsbaum mit einbetten. Für den Besucher der Webseite sieht es dann so aus, als wären die Unterordner der Gallerie als reguläre Navigationspunkte erfasst.
Da dieses Verhalten sowohl die Geschwindigkeit des Portals beeinflusst als auch die Anzahl Navigationspunkte drastisch erhöhen kann, ist das Hinzufügen von Dritt-Modulen zur Navigation im jeweiligen Navigationen-Seiten-Element konfigurierbar.