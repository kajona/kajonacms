#Anpassung der Navigationselemete über CSS

Um die einzelnen Elemente der Navigation anzupassen wird einfach das CSS angepasst. 

_Hinweis: Voraussetzung ist, dass man ein eigenes Template-Pack unter angelegt hat! Im Beispiel hier: /templates/demo2_

###Beipiel: Links der Ebene 2 sollen in roter Schrift dargestellt werden, Links der Ebene 3 in grün.

##Möglichkeit 1: direkt über den CSS-Pfad
Zuerst wird unter /templates/demo2/css eine neue CSS-Datei angelegt, z.B. demo2.css. Um die Links der Ebene 2 zu stylen wird folgender Inhalt darin angelegt. (Je nach Betriebssystem erst Rechte anpassen!)

```
/* style navi links level 2 */
#mainnav li li a {
     color: red;
}

/* style navi links level 3 */
#mainnav li li li a {
     color: green;
}
```

Danach muss die CSS-Datei im Seitentemplate eingebunden werden. Es wird also unter /templates/demo2/tpl/module_pages in den Dateien home.tpl und standard.tpl im Header folgende Zeile hinzugefügt:

```
<link rel="stylesheet" href="_webpath_/templates/demo2/css/demo2.css?_system_browser_cachebuster_" type="text/css"/>
```

Das Ergebnis sieht dann so aus:

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/module_navigation/kajona_navigation_style-1.png&maxWidth=500)

Sollen nun noch die Listensymbole entfernt werden wird folgendes hinzugefügt:

```
#mainnav li li {
     list-style: none;
}

#mainnav li li li  {
     list-style: none;
}
```

Das Ergebnis sieht dann so aus:

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/module_navigation/kajona_navigation_style-2.png&maxWidth=500)

##Möglichkeit 2: über eine eigene Stylsheet-Klasse
Will man eine eigene Bezeichnung für das Styling unterbringen muss das Navigations-Template erweitert werden.
Über Paketverwaltung -> Template-Verwaltung wird im eigenen Tamplate demo2 das Template mainnavi.tpl hinzugefügt. Es wird unter /templates/demo2/tpl/module_navigation angelegt und kann bearbeitet werden. (Je nach Betriebssystem erst Rechte anpassen!)

Es werden den Elementen der Ebenen 2 und 3 die Klassen **level2-item-link** und  **level3-item-link** **level2-item** bzw. **level2-item** und **level3-item** hinzugefügt:

```
<level_2_wrapper><ul>%%level2%%</ul></level_2_wrapper>

<level_2_active>
<li class="nav-item level2-item"><a href="%%href%%" target="%%target%%" class="nav-link active level2-item-link">%%text%%</a>%%level3%%</li>
</level_2_active>

<!-- Hint: If you do NOT want to show child items under inactive items remove %%level3%% in the "_inactive-sections"!! -->
<level_2_inactive>
<li class="nav-item level2-item"><a href="%%href%%" target="%%target%%" class="nav-link level2-item-link">%%text%%</a>%%level3%%</li>
</level_2_inactive>


<level_3_wrapper><ul>%%level3%%</ul></level_3_wrapper>

<level_3_active>
<li class="nav-item level3-item"><a href="%%href%%" target="%%target%%" class="nav-link active level3-item-link">%%text%%</a></li>
</level_3_active>

<level_3_inactive>
<li class="nav-item level3-item"><a href="%%href%%" target="%%target%%" class="nav-link level3-item-link">%%text%%</a></li>
</level_3_inactive>
```

In der CSS-Datei (anlegen und einbinden wie in Möglichkeit 1) kann dann einfach folgendes definiert werden:

```
.level2-item-link {
    color: red;
}

.level3-item-link {
    color: green;
}

.level2-item {
    list-style: none;
}

.level3-item  {
    list-style: none;
}
```
