#Template-Erstellung am Beispiel eines Bootstrap-Templates

Dieses Tutorial zeigt, wie man ein eigenes Website-Template für Kajona V5 erstellt. Es wird auf Basis des Bootstrap-Templates "Simple Sidebar" ein responsives Kajona-Template mit fontawesome-Icons erstellt, bei dem die Navigationsleiste links angeordnet ist und entwerder manuell eingeklappt werden kann oder bei kleinen Bildschirmgrößen automatisch ausgeblendet wird.

_Die Grundlagen zur Funktionsweise von Kajona-Templates zeigt das Tutorial "tutorial_kajona_templates_de"._

##Kajona installieren
Es wird ein laufendes Kajona-System der Version 4.x oder 5.x vorausgesetzt. Der Webserver und der Benutzer müssen Schreibrechte im Ordner /templates haben.

##Bootstrap-Template herunterladen
Als Ausgangsbasis soll das Bootstrap-Template "Simple Sidebar" von http://startbootstrap.com/template-overviews/simple-sidebar/ verwendet werden. Also zip-Datei herunterladen und entpacken, wo ist egal, es werden später nur Dateien herauskopiert.

Folgende Struktur kommt dabei heraus:

```
startbootstrap-simple-sidebar-1.0.5
  |- LICENSE
  |- README.md
  |- css
  |- fonts
  |- index.html
  |- js
```

##Neues Template-Pack anlegen
Im Kajona-Backend wird nun unter Paketverwaltung -> Template-Verwaltung ein neues Template-Pack angelegt. Es muss ein Name angegeben werden (z.B. demo-simple-sidebar) und es müssen mindestens die Elemente home.tpl und standard.tpl per Checkbox ausgewählt werden (damit der Sample Content weiter funktioniert! Grds. können natürlich eigene tpl erstellt werden). Nach dem Speichern erscheint das neue Template in der Liste und kann aktiv geschaltet werden.

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/kajona_templates_backend.png&maxWidth=500)

> ###Zwischen-Resultat :-)
_Ruft man nun das Portal auf sieht alles aus wie zuvor, das neue Template entspricht genau dem Default-Template._

####Wichtig!
_Je nach Betriebssystem müssen nun noch die Rechte im Verzeichnis /templates geändert werden damit der eigene Benutzer die Dateien des vom Webserver erstellten Templates bearbeiten kann._

Folgende Verzeichnisstruktur wurde nun angelegt:

```
templates/demo-simple-sidebar
  |- css
  |- js
  |- tpl
  |	  |- module_pages 
  |	  		|- home.tpl
  |	  		|- standard.tpl
```

Im Sample Content wird home.tpl für die Startseite (index) verwendet, alle anderen Seiten verwenden standard.tpl. Damit ist es einfach möglich, die Willkommensseite anders zu designen, z.B. mit einem großen Bild oder einem Slider.

##Dateien kopieren
Aus dem zuvor entpackten Bootstrap-Template werden nun folgende Dateien in das neue Template-Pack kopiert:

* 3 Dateien im css-Ordner (je von css nach css)
* 3 Dateien im js-Ordner (je von js nach js)
* den kompletten Ordner fonts in den Ordner des Template-Packs

Die neue Struktur sieht danach so aus:

```
templates/demo-simple-sidebar
  |- css
  |	  |- bootstrap.css  
  |	  |- bootstrap.min.css 
  |	  |- simple-sidebar.css
  |- fonts
  |	  |- glyphicons-halflings-regular.eot
  |	  |- glyphicons-halflings-regular.svg
  |	  |- glyphicons-halflings-regular.ttf
  |	  |- glyphicons-halflings-regular.woff
  |	  |- glyphicons-halflings-regular.woff2 
  |- js
  |	  |- bootstrap.js
  |	  |- bootstrap.min.js
  |	  |- jquery.js 
  |- tpl
  |	  |- module_pages 
  |	  		|- home.tpl
  |	  		|- standard.tpl
  
```

##HINWEIS ZUM CACHE
Wichtiger Hinweis: Wenn Templates o.ä. geändert werden, werden die Änderungen nicht direkt im Portal angezeigt da der Seiten-Cache erst nach einer gewissen Zeit die Änderungen übernimmt. Der Cache muss also manuell gelöscht werden. Dazu entweder im Backend unter 

_System -> System-Tasks ->Globalen Cache leeren_

aufrufen oder im Filesystem den Ordner 

_/project/temp/cache/_

löschen.

##tpl Dateien anpassen bzw. umbauen
Nun folgt die eigentliche Anpassung bzw. der Umbau der Seiten-Templates. Auf Basis der Seite index.html aus dem Bootstrap-Templates werden home.tpl und standard.tpl umgebaut.

Wie man die Dateien ändert ist natürlich Geschmackssache und im Prinzip egal. Hier wird folgender Weg beschritten: Oroginal umbenennen, index-Datei aus Bootstrap zur neuen tpl-Datei machen und anpassen.

* home.tpl um benennen nach home_org.tpl
* index.html nach module_pages kopieren und umbenennen nach home.tpl

Dann werden die Dateien home_org.tpl und die neue home.tpl (am besten in zwei Editorfenstern nebeneinander) geöffnet und die home.tpl wird angepasst.

> ###Zwischen-Resultat :-)
_Ruft man zu diesem Zeitpunkt das Portal auf sieht man schon den ersten Effekt: Es wird kein Kajona-Sample-Content mehr angezeigt sondern nur der (statische) Demo-Inhalt des Boorstrap-Templates. Styles und Javascript klappt allerdings nicht weil die Pfade nicht stimmen._

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/kajona_templates_simplesidebar-1.png&maxWidth=500)

###Title und URL für SEO anpassen
Vorher 

```
<title>Simple Sidebar - Start Bootstrap Template</title>
```
Nachher

```
<title>%%additionalTitle%%%%title%% | New Kajona Template</title>
<!-- IMPORTANT FOR SEO! Include canonicalUrl to tell search engines the correct URL handling -->
<link rel="canonical" href="%%canonicalUrl%%"/>
```
###Meta-Tags
Vorher

```
<meta name="description" content="">
```

Nachher

```
<meta name="description" content="%%description%%"/>
<meta name="keywords" content="%%keywords%%"/>
```

###Pfade zu css-Dateien anpassen
Vorher

```
<!-- Bootstrap Core CSS -->
<link href="css/bootstrap.min.css" rel="stylesheet">
<!-- Custom CSS -->
<link href="css/simple-sidebar.css" rel="stylesheet">
```
    
Nachher (Achtung, ggf. Template-Namen anpassen, hier: demo-simple-sidebar)

```
<!-- Bootstrap Core CSS -->
<link href="_webpath_/templates/demo-simple-sidebar/css/bootstrap.min.css?_system_browser_cachebuster_" rel="stylesheet">
<!-- Custom CSS -->
<link href="_webpath_/templates/demo-simple-sidebar/css/simple-sidebar.css" rel="stylesheet">
```

_Die Ergänzung ?_system_browser_cachebuster_ _hinter dem Pfad zur css-Datei bewirkt, dass beim Aufruf der Seite geprüft werden kann, ob die css-Datei vom Browser neu geladen werden soll._

###Ggf. Fontawesome einbinden
Damit im Template Fontawesome-Icons verwendet werden können kann noch zus. folgende Zeile eingebunden werden:

```
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">   
```

###Favicon einbinden
Für ein Favicon wird noch eingefügt:

```
<link rel="shortcut icon" href="_webpath_/favicon.ico" type="image/x-icon"/>
```

###Kajona-Head!
Ganz wichtig: Damit unter Kajona alles richtig funktioniert (z.B. der Portal-Editor muss noch folgende Zeile in den Head:

```
<!-- IMPORTANT! Include the kajona_head!! This injects jQuery, too-->
%%kajona_head%%
```

###Pfade zu js-Dateien anpassen
Außer den Anpassungen im head der Seite müssen noch die Pfade zu den js-Datei angepasst werden. Diese finden sich ganz unten in der Datei.

Vorher

```
<!-- jQuery -->
<script src="js/jquery.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="js/bootstrap.min.js"></script>
```
    
Nachher (Achtung, ggf. Template-Namen anpassen, hier: demo-simple-sidebar)

```
<!-- jQuery -->
<script src="_webpath_/templates/demo-simple-sidebar/js/jquery.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="_webpath_/templates/demo-simple-sidebar/js/bootstrap.min.js"></script>
```

> ###Zwischen-Resultat :-)
_Ruft man zu diesem Zeitpunkt das Portal auf sieht man schon mehr: Die Seite verwendet die Style-Sheets und Javascript. Die Navigation kann auf- und zugeklappt werden._

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/kajona_templates_simplesidebar-2.png&maxWidth=500)

###Kajona-Inhalte im Template anzeigen
Damit im Template die Inhalte wie Navigationselemente, Seiteninhalte usw. angezeigt werden müssen die Platzhalte im Template angelegt werden.

####Portal-Navigation

Um die Portalnavigation einzubinden müssen folgende Zeilen angepasst werden:

Vorher

```
<!-- Sidebar -->
        <div id="sidebar-wrapper">
            <ul class="sidebar-nav">
                <li class="sidebar-brand">
                    <a href="#">
                        Start Bootstrap
                    </a>
                </li>
                <li>
                    <a href="#">Dashboard</a>
                </li>
                <li>
                    <a href="#">Shortcuts</a>
                </li>
                <li>
                    <a href="#">Overview</a>
                </li>
                <li>
                    <a href="#">Events</a>
                </li>
                <li>
                    <a href="#">About</a>
                </li>
                <li>
                    <a href="#">Services</a>
                </li>
                <li>
                    <a href="#">Contact</a>
                </li>
            </ul>
        </div>
<!-- /#sidebar-wrapper -->
```

Nachher

```
<!-- Sidebar -->
        <div id="sidebar-wrapper">
            <ul class="sidebar-nav">
                %%mastermainnavi_navigation%%
            </ul>
        </div>
<!-- /#sidebar-wrapper -->
```
 
> ###Zwischen-Resultat :-)
 _Ruft man jetzt das Portal auf wird bereits die Kajona-Navigation angezeigt! Klickt man einen Link an wird die entsprechende Seite aufgerufen, allerdings mit dem Layout des Default-Template-Packs (hier: standard.tpl)._

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/kajona_templates_simplesidebar-3.png&maxWidth=500)

####Seiteninhalte einbinden
Es fehlen noch die Platzhalter für Seiteninhalte. Diese werden nun eingebunden.


Vorher

```
 <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <h1>Simple Sidebar</h1>
                        <p>This template has a responsive menu toggling system. The menu will appear collapsed on smaller screens, and will appear non-collapsed on larger screens. When toggled using the button below, the menu will appear/disappear. On small screens, the page content will be pushed off canvas.</p>
                        <p>Make sure to keep all page content within the <code>#page-content-wrapper</code>.</p>
                        <a href="#menu-toggle" class="btn btn-default" id="menu-toggle">Toggle Menu</a>
                    </div>
                </div>
            </div>
        </div>
<!-- /#page-content-wrapper -->
```

Es werden einfach alle Zeilen (bzw. nur die, die man wirklich braucht) in den div-Container kopiert in dem der Bootstrap-Sample Content steht:

```
 <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">


            <kajona-blocks kajona-name="Headline">

                <kajona-block kajona-name="Headline">
                    <div class="page-header">
                        <h1>%%headline_plaintext%%</h1>
                    </div>
                </kajona-block>

            </kajona-blocks>


            <kajona-blocks kajona-name="Page Intro">

                <kajona-block kajona-name="Header and Text">
                    <h3>%%headline_plaintext%%</h3>
                    <p>%%content_richtext%%</p>
                </kajona-block>

                <kajona-block kajona-name="Two Columns Header and Text">

                    <div class="row">
                        <div class="col-sm-6">
                            <h3>%%headlineleft_plaintext%%</h3>
                            <p>%%contentleft_richtext%%</p>
                        </div>

                        <div class="col-sm-6">
                            <h3>%%headlineright_plaintext%%</h3>
                            <p>%%contentright_richtext%%</p>
                        </div>
                    </div>

                </kajona-block>


                <kajona-block kajona-name="Two Columns Large Text and Image">

                    <div class="row">
                        <div class="col-sm-9">
                            <h3>%%headlineleft_plaintext%%</h3>
                            <p>%%contentleft_richtext%%</p>
                        </div>

                        <div class="col-sm-3">
                            <img src="[img,%%imageright_imagesrc%%,400,600]" />
                        </div>
                    </div>

                </kajona-block>


                <kajona-block kajona-name="Three Columns Text and Image">

                    <div class="row">
                        <div class="col-sm-4">
                            <h3>%%headlineleft_plaintext%%</h3>
                            <p>%%contentleft_richtext%%</p>
                        </div>

                        <div class="col-sm-4">
                            <h3>%%headlinecenter_plaintext%%</h3>
                            <p>%%contentcenter_richtext%%</p>
                        </div>

                        <div class="col-sm-4">
                            <img src="[img,%%imageright_imagesrc%%,300,600]" />
                        </div>
                    </div>

                </kajona-block>


                <kajona-block kajona-name="Text Only">
                    <p>%%content_richtext%%</p>
                </kajona-block>

            </kajona-blocks>

            <kajona-blocks kajona-name="Special Content">

                <kajona-block kajona-name="News">
                    <div class="row">
                        <div class="col-sm-12">
                            %%news_news%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Guestbook">
                    <div class="row">
                        <div class="col-sm-12">
                            %%guestbook_guestbook%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Downloads">
                    <div class="row">
                        <div class="col-sm-12">
                            %%downloads_downloads%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Gallery">
                    <div class="row">
                        <div class="col-sm-12">
                            %%gallery_gallery%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Form">
                    <div class="row">
                        <div class="col-sm-12">
                            %%contact_form%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Maps">
                    <div class="row">
                        <div class="col-sm-12">
                            %%maps_maps%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Search">
                    <div class="row">
                        <div class="col-sm-12">
                            %%search_search%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Sitemap">
                    <div class="row">
                        <div class="col-sm-12">
                            %%sitemap_navigation%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Faqs">
                    <div class="row">
                        <div class="col-sm-12">
                            %%faqs_faqs%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Votings">
                    <div class="row">
                        <div class="col-sm-12">
                            %%votings_votings%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Feed">
                    <div class="row">
                        <div class="col-sm-12">
                            %%feed_rssfeed%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Portallogin">
                    <div class="row">
                        <div class="col-sm-12">
                            %%login_portallogin%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Portalregistration">
                    <div class="row">
                        <div class="col-sm-12">
                            %%register_portalregistration%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Portalupload">
                    <div class="row">
                        <div class="col-sm-12">
                            %%upload_portalupload%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="Eventmanager">
                    <div class="row">
                        <div class="col-sm-12">
                            %%events_eventmanager%%
                        </div>
                    </div>
                </kajona-block>

            </kajona-blocks>

            <kajona-blocks kajona-name="Footer Area">

                <kajona-block kajona-name="Postacomment">
                    <div class="row">
                        <div class="col-sm-12">
                            %%postacomment_postacomment%%
                        </div>
                    </div>
                </kajona-block>

                <kajona-block kajona-name="TagTo and Lastmodified">
                    <div class="row">
                        <div class="col-sm-6">
                            %%changed_lastmodified%%
                        </div>

                        <div class="col-sm-6">
                            %%tagto_tagto%%
                        </div>
                    </div>
                </kajona-block>

            </kajona-blocks>

<a href="#menu-toggle" class="btn btn-default" id="menu-toggle">Toggle Menu</a>

                    </div>
                </div>
            </div>
        </div>
<!-- /#page-content-wrapper -->
```

Es wird also alles vom ersten <kajona-blocks> bis zum letzten </kajona-blocks> kopiert.

Achtung: Nicht den "Toggle-Link" löschen falls benötigt. Sonst kann man die Navigation nicht einklappen.


#Weitere Schritte
Nun muss noch die Dater standard.tpl angepasst werden. Im einfachsten Fall kopiert man einfach die home.tpl - dann sehen alle Seiten gleich aus.

Um das Template-Pack nun nach eigenen Wünschen anzupassen können jetzt noch die Style-Sheets geändert werden oder weitere Platzhalter wie mainnavigation oder Such-Box im Template eingebauz werden.

##CSS anpassen
Exemplarisch soll noch gezeigt werden, wie man am einfachsten das Aussehen über CSS verändert.

Zuerst wird eine neue CSS-Datei angelegt in der ein paar Definitionen gemacht werden und Klassen aus der simplesidebar-CSS überschrieben werden:

css/my-styles.css

```
/* some simple test stylings */

/* change the font for our site */
body {
    font-family: 'Source Sans Pro', sans-serif;
    font-weight: 400;
}

/* simple-sidebar overrides */
#sidebar-wrapper {
    background: #EBF7DD;
    -moz-box-shadow:    5px 0px 5px  #ccc;
    -webkit-box-shadow: 5px 0px 5px  #ccc;
    box-shadow:         5px 0px 5px  #ccc;
    position: absolute;

}

.sidebar-nav li {
    text-indent: 0px;
    line-height: 10px;
    list-style: none;
}


.sidebar-nav li a {
    color: #007352;
}

.sidebar-nav li a:hover {
    color: #2b2d2f;
}

.sidebar-nav li a.active {
    color: #255625;
    font-weight: bold;
}
```

Nun noch die CSS-Datei und die für den body gewählte (Google-) Schrift im head des Templates einbinden:

```
<!-- Google font -->
<link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,700,900,600italic,700italic' rel='stylesheet' type='text/css'>

<!-- additional styles -->
<link href="_webpath_/templates/demo-simple-sidebar/css/my-styles.css" rel="stylesheet">
```

Nun erscheint die Seite in neuer Optik:

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/kajona_templates_simplesidebar-4.png&maxWidth=500)
 