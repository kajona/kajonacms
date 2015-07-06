

# Languagesets / Languageswitching for News

ntroduced with Version 3.3.0, Kajona supports so called languagesets. What those sets mean and how they work should be explained with this howto. As an example, the languageswitching of news should be configured.
In addition, the second chapter takes a look at the details and how languagesets work behind the scenes.

##Languageswitching for news
The current concept of languages implemented in Kajona (see http://www.kajona.de/manual_modul_languages.html) only takes care of switching pages directly.
It's possible to switch the language a page is displayed by using the languageswitch, as long as the site exists with elements in another language.
But it would be desirable to provide the possibility to switch the language based on the current contents, so not only the page itself, too.
The detailed view of a news-message is created by a news-element, configured to provide a detailed-view (“detailed mode”) of the news-message requested. In general, this element is being placed on a separate page, e.g. named “newsdetails”.

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/howto_languageset_01.png&maxWidth=820)

If a single news-message is shown, the switching of the current language is possible (indicated by the flags at the page-header). But after switching the language, the page remains empty. This is totally correct, since Kajona can't know the matching news-message in another language.

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/howto_languageset_02.png&maxWidth=785)

###Languagesets as a bridge
To fill this gap, so called languagesets were introduced to Kajona in version 3.3. As an example, those sets are implemented to be used by the news – a direct language-switching can be possible :).
Therefore, the loose news have to be consolidated by creating languagesets – they become coupled. Afterwards Kajona not only knows what language a single news is assigned to, Kajona even knows the logically connected news (defined by sets), too.
###Set up a languageset
To achieve the relations mentioned above, the management of languagesets is integrated into the module news. The administrative parts are only shown, if there is more then one portal-language defined in the module languages. In the default installation there is only one language and the icon remains invisible. But as soon as there are at least two languages available, the icon becomes visible.

![](https://www.kajona.de/image.php?image=/files/images/upload/manual/v4_langswitch_01.jpg&maxWidth=600)

The assignment to languagesets is done by the action “edit language assignment”.
In the initial situation, all news are arranged side by side, without any relations between each other. The languages exist in parallel.


![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/howto_languageset_04_de.png&maxWidth=637)





The first call of the action “edit language assignment” shows the form to assign the current news to a language. By submitting the form, a new languageset is created in the background.

![](https://www.kajona.de/image.php?image=/files/images/upload/manual/v4_langswitch_02.jpg&maxWidth=600)

All additional assignments, the adding of other news to the current set, are done in the following steps.

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/howto_languageset_06_de.png&maxWidth=630)








What now follows is the combination of logically connected news. In the language-overview of the news-message edited before, the news is shown in the row of the language used in the form before. All other languages show “not maintained” as the currently assigned news-message. By using the dropdown below the list, other news can be added to the unmaintained languages.


![](https://www.kajona.de/image.php?image=/files/images/upload/manual/v4_langswitch_03.jpg&maxWidth=600)

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/howto_languageset_08_de.png&maxWidth=590)





##Integration in the portal
The portal is now capable of switching the language including the switching of the contents, any further configuration is not necessary. The languageswitch (located on the page) checks if there are any related news-messages in other languages, coupled with the current news-message. If this is the case, the links are generated to point to the foreign news-message instead of only the foreign page – the language switch takes the current content into account when switching the language.

##Behind the Scenes
Right from the beginning, the languagesets have been planned to be used by any module. The goal was to create a mechanism to link contents over the boundaries of languages.
The implementation of languagesets in the module news is only a proof of concept, the usage in other modules is possible by implication, e.g. in the module faqs.
A single languageset consists of a list of data-records and languages. Seen from a relational point of view, a single entry consists of the fields

`languageset_id | languageset_language | languageset_systemid`

whereas the combination of  `languageset_id` and languageset_systemid` has to be unique.
This means, every record can appear only in a single languageset.
The API provides several ways to load a languageset. In most cases, the loading is done by specifying the id of the data-record. E.g. if a news-message is loaded, the languageset is  determined by the news' id (languageset_systemid). The languageswitch is then capable of searching the set for different news-messages in different languages. If there are any records, the links to the foreign languages are created using the systemids of the matching ids found.
To integrate languagesets into a module, only the adding and modification of languagesets has to be implemented, as an example have a look at the implementation provided by the module news.
Because of the object-lifecycle in Kajona, deleting a single entry of a languageset is done automatically. Each time a system record is deleted, the system searches for occurrences in languagesets and removes the record - this has not to be done by the using module (and so the developer of the module).