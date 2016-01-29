#Statistiken

Das Modul protokolliert alle Portalzugriffe, im Admin-Bereich können diese dann ausgewertet und in verschiedenen Reports betrachtet werden.

Das Modul unterstützt die Erstellung eigener Auswertungen. So ist es möglich, eigene Reports zu schreiben, die von Kajona automatisch eingelesen und ausgewertet werden. Dies erfolgt durch ein Plugin-Konzept.
Zusätzlich sind die Stats um so genannte „Worker“-Tasks erweitert. Diese kommen immer dann zum Einsatz, wenn die gesammelten Daten der Besucher auf irgendeine Art und Weise bearbeitet werden sollen. Eine Anwendung ist beispielsweise, die IP-Adressen der Besucher in aussage-kräftigere Hostnames zu wandeln. Da dieser Vorgang aber längere Zeit ist Anspruch nimmt, wurde hierfür ein extra Worker-Task erstellt.

## Settings

Der Wert „Anzahl Einträge“ legt fest, wie viele Zeilen in den Reports angezeigt werden sollen. 

Mit dem Parameter „Anzahl Sekunden“ wird festgelegt, in welchem Zeitraum ein User als online gilt. Liegt sein letzter Zugriff auf das Portal schon länger als diese Zeitspanne zurück, dann gilt er als offline.

Über die Liste, die in „Auszuschließende Domains“ hinterlegt wird, können bestimmte Domains (wie beispielsweise die lokale Testinstallation des Systems) aus den Reports ausgeschlossen werden. Dies macht unter Anderem bei den Referrern Sinn.
