# Languages

Seit Version 3.0 beherrscht Kajona das Verwalten verschiedener Sprachen im Portal. Hierfür wird eine Seite in der Seitenverwaltung, wie sonst auch, angelegt. Der Unterschied ist aber nun, dass sowohl die Seiteneigenschaften, also auch die Seiteninhalte sprachabhängig gespeichert werden. Die Seite „Über uns“ kann in der Sprache Deutsch also das Template „standard_de.tpl“, im Englischen aber „standard_en.tpl“ als Template verwenden.
Die Sprache, die als default gewählt wurde, wird als Start-Sprache gewählt, bis der Benutzer eine andere Sprache auswählt (Anmerkung: Im Portal werden die Sprachen ausgewertet, die der Browser als default-Sprachen sendet).
Eine Spezialaktion ist „Content zuweisen“. Diese kommt nur dann in Aktion, wenn das Sprachen-Modul später in das System installiert wurde. In diesem Fall sollen ja die bisherigen Inhalte nach wie vor verwendet werden können, obwohl diese ja bisher keiner Sprache zugeordnet wurden. Mit dieser Aktion werden alle noch nicht zugeordneten Inhalte der Default-Sprache zugeordnet.

Auf Grund der häufigen Nachfragen soll das Modul Sprachen nachfolgend ein wenig detaillierter erklärt werden.
Kajona stellt „lediglich“ im Modul Seiten (auf Seite der Administration) die Möglichkeit zur Verfügung, Inhalte unter verschiedenen Sprachen abzulegen. Alle anderen Module bieten diese Möglichkeit nicht, lassen sich aber nahtlos in dieses Konzept integrieren. 

Nachstehend soll ein kleines Diagramm die generelle Funktionsweise im Modul Seiten illustrieren:
![](https://www.kajona.de/files/images/upload/manual/017_languages_1.png)

Möchte man nun auch andere Module sprachabhängig in die Seiten integrieren, so sollten in den jeweiligen Modulen die entsprechenden Einträge für jede Seite angelegt werden. Am Beispiel der Navigationen würde dies bedeuten, dass dort zwei Navigationsbäume angelegt werden würden. Einer für die Seiten in de, der andere für die Seiten in en. Diese Bäume werden dann über die Masterseiten der jeweiligen Sprachen (oder direkt über Platzhalter im Template) in die Seiten eingebunden. In Abbildung 2 wird diese verhalten noch einmal grafisch verdeutlicht.

![](https://www.kajona.de/files/images/upload/manual/018_languages_2.png)