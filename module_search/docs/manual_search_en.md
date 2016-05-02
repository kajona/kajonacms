#Search

Starting from Kajona 4.5, the new, index-based search is fully integrated in Kajona. This means that both, the portal- and the backend search make use of the new engine.

In order to have your own objects being added to the index, too, there are some aspects to be considered.

##General
In general, all properties marked with the annotations
`@addSearchIndex` 
are added to the index. As soon as an object is inserted, updated or deleted, the system analyzes the object for properties marked with `@addSearchIndex`. if found, the value of those properties are read, analyzed and added to the index. 
Therefore it is essential to have valid getters and setters for those properties!

##Backend search
All objects in the index are available to the backend search by default. If you don't want any special behavior, you won't need to implement anything (besides marking the relevant properties with `@addSearchIndex`).
Nevertheless, there are some cases where the default behavior, especially when rendering an object in the autocomplete search result list, should be changed. Therefore the object may optionally implement the interface
`SearchResultobjectInterface`
adding the method `getSearchAdminLinkForObject()`. Use this method to generate and return a different link (see `Link::getLinkAdminHref()`) to be used as soon as a user chooses the object out of the autocomplete list.

##Portal search
The portal-search is slightly more complicated: Instead of only finding matching objects, the search has to find a matching site (containing the object) and the current language has to be taken into account.
Therefore all objects te be found by the portal search have to implement the interface
`SearchPortalobjectInterface`
The methods added are the following:
`getContentLang()`
Returns the language of the objects' contents (e.g. en, de, it)
`updateSearchResult(SearchResult $objResult)`
This is where all the magic happens. It is up to your implementation to transform the object found into a real page. Therefore you should add the pagename and the pagelink to the result object.
If required, you can even split the single object into multiple objects and return a list of pages. In contrast, returning null removes the object from the result set.

Side node: Up to V4.5, the portal-search was handled using search-plugins. Those got obsolete in 4.5 an above. Nevertheless, you might reuse your search-plugin code for the updateSearchResult() method (searching the site relevant for the current object).