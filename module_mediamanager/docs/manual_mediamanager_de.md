#Mediamanager

Der Mediamanager dient zur Dateiverwaltung im System, bspw. für den Upload von Bildern oder anderen Dateien. Diese Inhalte lassen sich im Portal in Form verschiedener Ausgaben darstellen – als Download-Liste, als Bildergalerie o.Ä. - letzten Endes eine Frage der jeweiligen Templates und gewählten Seitenelemente.

Hierfür werden verschiedenen Repositories angelegt und mit verschiedenen Parametern wie einem Upload- oder einem Ansichtsfilter angelegt. Sollen z.B. im Repository „Pics“ nur jpg- und gif-Dateien als Upload erlaubt sein, so wäre als Upload-Filter „.gif,.jpg“ zu setzten. 

Zusätzlich bietet der Mediamanager für Bilder integrierte Bildbearbeitungsfunktionen an. So können Bilder zugeschnitten und gedreht werden. Da die Bilder an verschiedenen Stellen im System verwendet werden können (Seitenelemente, Bildergalerien) sollte ein Bearbeiten von Bildern wohl bedacht werden.

##Settings
Die Einstellungen „Standard Dateien-Repository“ und „Standard Bilder-Repository“ verweisen auf im Filemanager angelegte Repositories. Diese werden beispielsweise vom WYSIWYG-Editor bei der Auswahl von Bildern und Dateien verwendet. Ist kein Wert angegeben werden alle verfügbare Repositories angeboten.