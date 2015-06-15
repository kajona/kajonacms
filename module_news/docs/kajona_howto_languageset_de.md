

# Languagesets / Sprachumschaltung für News

Seit Version 3.3.0 unterstützt Kajona sogenannte Languagesets. Was diese bedeuten und wie diese funktionieren soll mit diesem HowTo dargestellt werden. Als Anwendungsbeispiel soll die Sprachumschaltung von News konfiguriert werden.
Zusätzlich werden im zweiten Kapitel ein wenig die Details und die Arbeitsweise von Languagesets beleuchtet.

##Sprachumschaltung für News
Das aktuelle Sprachenkonzept von Kajona (siehe hierzu auch http://www.kajona.de/manual_modul_languages.html) sieht nur das direkte Umschalten von einzelnen Seiten vor. So ist es möglich, über die Sprachumschaltung die aktuelle Seite in einer anderen Sprache anzuzeigen, sofern diese in einer anderen Sprache mit vorhandenen Seitenelementen existiert.
Wünschenswert wäre es aber auch, wenn dies bezogen auf den aktuellen Inhalt der Seite möglich wäre. 
Die Detailansicht einer Newsmeldung erfolgt mittels einem News-Element, dass auf die Funktion „Detailansicht“ konfiguriert wird. In der Regel wird dieses auf einer gesonderten Seite platziert, beispielsweise „newsdetails“. 

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/howto_languageset_01.png&maxWidth=820)

Wenn hier nun ein Newsbeitrag angezeigt wird, so ist eine Sprachumschaltung zwar generell möglich (zu erkennen an den Fahnen im Seitenkopf) – jedoch wird die Newsseite in der anderen Sprache leer angezeigt. Das macht Sinn, denn Kajona kennt ja den zugehörigen Newsbeitrag in der anderen Sprache nicht:

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/howto_languageset_02.png&maxWidth=785)

###Languagesets als Brücke
Um diese Lücke zu schließen wurden mit Kajona 3.3 sogenannte Languagesets eingeführt. Beispielhaft verwendet werden diese bei den News – ein Sprachumschaltung kann also möglich sein :).
Hierfür müssen jedoch einzelne News zu Languagesets „zusammen gebunden“ werden. Durch diese weiß Kajona zum einen, welcher Sprache ein einzelner Newsbeitrag zugeordnet wurde. Zum Anderen kennt Kajona dann aber auch logisch bzw. inhaltlich zusammengehörende News.
###Einrichten eines Languagesets
Um die oben genannten Zugehörigkeiten festzulegen, ist die Verwaltung der Languagesets in das Modul News integriert. Sichtbar wird die Sprachverwaltung nur dann, wenn im System mehr als zwei Portalsprachen definiert wurden. In der Standard-Installation wäre das also nicht der Fall, sehr wohl aber wenn über das Modul „Sprachen“ mindestens eine weitere Sprache hinzugefügt wurde.

![](https://www.kajona.de/image.php?image=/files/images/upload/manual/v4_langswitch_01.jpg&maxWidth=600)

Das Zuordnen zu Languagesets erfolgt über die Aktion „Sprachzuordnung bearbeiten“.
In der Ausgangssituation liegen alle News quasi nebeneinander, ohne jegliche Relation zueinander. Parallel hierzu liegen die einzelnen Sprachen.


![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/howto_languageset_04_de.png&maxWidth=637)





Der erste Aufruf der Aktion „Sprachzuordnung bearbeiten“ führt zum Zuweisen der aktuellen News zu einer Sprache. Im Hintergrund wird ein neues Languageset angelegt. 

![](https://www.kajona.de/image.php?image=/files/images/upload/manual/v4_langswitch_02.jpg&maxWidth=600)

Alle weiteren Zuordnungen, also welche News zueinander gehören, erfolgen in späteren Schritten.

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/howto_languageset_06_de.png&maxWidth=630)








Nun folgt das Zusammenfassen zugehöriger News. In der Sprachübersicht der zuvor bearbeiteten News wird diese als der gewählten Sprache zugewiesen angezeigt, die anderen Sprachen sind noch „nicht gepflegt“. Durch das Dropdown am Ende der Liste kann den noch offenen Sprachen jeweils die entsprechende Newsmeldung zugeordnet werden.


![](https://www.kajona.de/image.php?image=/files/images/upload/manual/v4_langswitch_03.jpg&maxWidth=600)

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/howto_languageset_08_de.png&maxWidth=590)





##Portaleinbindung
Das Portal kann nun vollautomatisch die Sprachen samt zugehörigen Beiträgen umschalten, eine weitere Konfiguration ist nicht notwendig. Die Sprachumschaltung prüft, ob es zum angezeigten Beitrag abhängige Beiträge in anderen Sprachen gibt und passt dann, wenn vorhanden, die Links der Sprachumschaltung an. Statt auf eine leere Seite wird dann direkt auf den Newsbeitrag in einer anderen Sprache verwiesen – die Sprachumschaltung funktioniert nun abhängig vom aktuell dargestellten Inhalt der Seite.

##Behind the Scenes
Die Languagesets wurden von Beginn an so konzipiert, dass diese beliebige Inhalte über Sprachen hinweg miteinander verknüpfen können. Die Implementierung bei den News stellt sozusagen lediglich den Proof of Concept dar, eine Anwendung bei anderen Inhalten (wie bspw. Faqs) ist ohne Weiteres möglich.
Ein Languageset besteht dabei aus einer Anzahl an Datensätzen und Sprachen. Rein relational besteht ein einzelner Eintrag eines Languagesets aus den Feldern

`languageset_id | languageset_language | languageset_systemid`

wobei die Kombination aus `languageset_id` und `languageset_systemid` einmalig sein muss.
Das bedeutet, jeder Datensatz darf nur einmalig in einem Languageset vorkommen.
Das API bietet nun verschiedene Möglichkeiten, an ein Languageset zu gelangen. In der Regel erfolgt dies über die ID des Datensatzes. Wird beispielsweise im Portal eine Newsmeldung angezeigt, so wird das Languageset des Newsbeitrages anhand dessen ID (languageset_systemid) geladen. Die Sprachumschaltung kann dann dieses Set nach den möglicherweise in anderen Sprachen vorhandenen languageset_systemid durchsuchen. Wird ein Datensatz gefunden, so kann der Link in die andere Sprache um die entsprechende ID ergänzt werden.
Um eine Integration der Languagesets in ein Modul vorzunehmen, muss lediglich das Hinzufügen zu Languagesets implementiert werden, als Beispiel dient hier die Implementierung bei den News.
Durch den Objekt-Lifecycle von Kajona wird beim Löschen eines Datensatzes automatisch geprüft, ob dieser in einem Languageset vorkommt. Ist dies der Fall, wird der Eintrag automatisch mit entfernt – dies muss also nicht explizit durch das einbindende Modul (und damit dem Modulentwickler) erfolgen.