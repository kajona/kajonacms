# Newsverwaltung

Das News Modul dient zum Anlegen, Steuern und Verwalten von News. 
Beim Anlegen von News wird ein Titel für die News vergeben, sowie ein Start-, Ende- und Archivdatum. Außerdem wird festgelegt, in welchen Kategorien die News erscheinen soll.
Über das Seitenelement News werden die angelegten News im Portal veröffentlicht. Hierbei wird die anzuzeigende Kategorie, ein Template für die Anzeige und ein Modus definiert. Der Modus unterscheidet hier zwischen der Listenansicht und der Detailansicht der News.
Zusätzlich gibt es den Archivmodus einer Newskategorie. In diesem landen automatisch alle News, die das Archivdatum überschritten haben, das Enddatum aber noch nicht erreicht haben. So kann man nicht mehr aktuelle News von der Top-News Liste verbannen, aber nach wie vor auf der Webseite im Archiv anbieten.

## News-Feeds (RSS)

Kajona beherrscht auch News-Feeds im RSS-Format. Diese werden dazu verwendet, um Inhalte der Seite abonnieren zu können, um bei neuen News diese sofort lesen zu können.
Hierfür müssen Feeds angelegt werden, die dann News aller oder einer bestimmten Kategorie anzeigen können. Dazu muss jedoch das Seitentemplate angepasst werden. Es sollte ein Abschnitt wie der folgende eingefügt werden:

`<link rel="alternate" type="application/rss+xml" title="Kajona News" href="Adresse/zum/feed" />`

Die Adresse des Feeds kann man sich aus der Liste der Feeds im Admin-Bereich kopieren. Das ist der Text zwischen dem Feedtitel und den Aktionen. Sollte das System mit eingeschaltetem Mod-Rewrite laufen, dann ist auch ein Aufruf des Feeds über das Schema /feedName.rss möglich. Das System erstellt daraus dann automatisch die URL zum gewünschten Feed. Um diesen Titel und den Feed-Titel an sich unterschiedlich vergeben zu können, können zwei unterschiedliche Titel vergeben werden – einer für den Aufruf, und einer als interner Feedtitel.
Das link-Tag samt Attribut wird innerhalb des Head-Bereichs des Seitentemplates platziert, also NICHT im Body-Bereich.
