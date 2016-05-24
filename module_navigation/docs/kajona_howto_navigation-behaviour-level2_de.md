
#Unterseiten in Navigation ausblenden


Standardmäßig werden in der Haupt-Navigation (mainnavigation) des Default-Templates immer alle Seiten angezeigt, also auch Unterseiten unterhalb ihrer Haupseite.

Das sieht dann so aus:

```
Home
Seite1
  Unterseite 1
Seite 2
  Unterseite 2
Seite 3
  Unterseite 3
Impressum
```

Will man Unterseiten aber nur dann anzeigen, wenn die entsprechende Hauptseite aktiv ist, also gerade angezeigt wird, muss das Template mainnavi.tpl geändert werden. Dazu geht man wie folgt vor:

Zuerst muss das Navigations-Template mainnavi.tpl im eigenen Seiten-Template unter ```/templates/mein-seiten-template``` vorhanden sein. Standardmäßig ist es das nicht, es wird aus dem Modul module_navigation unterhalb von ```/core``` gezogen. Seit Kajona V5 sind dies übrigens phar-Dateien und keine Ordner mehr! 

Unter Paketverwaltung -> Template-Verwaltung in der Zeile des eigenen Templates auf das Plus-Icon („Default Templates hinzufügen“) klicken. In der Liste „module_navigation“ auswählen und speichern.

Im Filesystem wurde nun der Ordner

```
/templates/mein-seiten-template/tpl/module_navigation
```

mit 4 tpl-Dateien angelegt. Diese können nun angepasst werden (Hinweis: Je nach Betriebssystem Rechte erst anpassen, sonst darf der Benutzer sie nicht bearbeiten!)

In der Datei mainnavi.tpl finden sich Platzhalter für die Navigationspunkte. Wichtig sind jetzt die Zeilen


```
<level_1_inactive>
<li class="nav-item"><a href="%%href%%" target="%%target%%" class="nav-link">%%text%%</a>%%level2%%</li>
</level_1_inactive>

<level_1_inactive_withchilds>
<li class="nav-item"><a href="%%href%%" target="%%target%%" class="nav-link">%%text%%</a>%%level2%%</li>
</level_1_inactive_withchilds>
```


Sie bedeuten grob gesagt: Unter dem inaktiven Navigations-Punkt der Ebene 1 soll Ebene 2 angezeigt werden. Das gleiche findet sich etwas weiter untern analog zu Ebene 2 und 3.

Um jetzt Ebene 2 nicht bei inaktiver Ebene 1 anzuzeigen muss nur der Platzhalter %%level2%% entfernt werden. Das sieht danach dann so aus:

```
<level_1_inactive>
<li class="nav-item"><a href="%%href%%" target="%%target%%" class="nav-link">%%text%%</a></li>
</level_1_inactive>

<level_1_inactive_withchilds>
<li class="nav-item"><a href="%%href%%" target="%%target%%" class="nav-link">%%text%%</a></li>
</level_1_inactive_withchilds>
```

_Hinweis: Die erste Sektion gilt grds. für Links der Ebene 1. Die zweite mit der Erweiterung _withchilds muss nicht unbedingt im Template vorhanden sein. Sie stellt einen Spezialfall dar: Es handel sich um Links der Ebene 1 die Unterpunkte haben. Das wird benötigt, wenn man diese Links anders stylen will, z.B. mit einem vorangestellten "Aufklappsymbol" in Form eines Dreiecks. Braucht man das nicht, kann man diese Sektion auch löschen._



Ggf. das gleiche noch für Ebene 2 und 3 wiederholen.

Um die Änderung direkt im Portal zu begutachten am besten noch schnell den Cache leeren und die Portalseite neu aufrufen. Jetzt sieht die Navigation so aus:

```
Home
Seite1
   Unterseite 1
Seite 2
Seite 3
Impressum
```

Fertig!

Will man das gleiche Verhalten auch in seiner Sitemap haben muss die Datei sitemap.tpl ebenfalls angepasst werden.

