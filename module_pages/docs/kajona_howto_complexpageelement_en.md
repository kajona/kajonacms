#Complex Pageelement


This tutorial describes the creation of a complex page-element. Compared to a simple element, the complex one stores data in the database and uses the data to render the content in the portal.

Since complex and simple element are the same when it comes to the elements' structure, this tutorial is based on the „simple pageelement“ tutorial. This means that all explanations made in the simple-element tutorial won't be repeated. 

The latest version of the simple pageelement tutorial is available on http://www.kajona.de.

The complex pageelement to be created should be able to fetch a remote RSS-feed, process the entries and print them in the portal.

Therefore the element is split into two parts: The backend-part allows you to enter the URL of the RSS-feed (and storing the url in the database). The portal-part is used to fetch the remote-feed, parse the content and to wrap the entries into a template. Therefore the element makes use of various APIs such as the xml-api, the template-api and the object-to-database mapper.

A current version of the rssfeed-element is available for download at http://www.kajona.de.

This tutorial is based on Kajona V4.3.

##Create the filesystem
The filesystem of the element is based on the same structure as the simple-elements' version. Since we'll call the element „rssfeed“, the name of the top-level folder (under /core) will be „element_rssfeed“.

Start by creating the following structure of files and folders in your installations' /core folder:

```
element_rssfeed
    |- admin
    |    |- elements
    |         |- class_element_rssfeed_admin.php
    |
    |- installer
    |    |- class_installer_element_rssfeed.php
    |
    |- portal
    |    |- elements
    |         |- class_element_rssfeed_portal.php
    |
    |- templates
    |    |- default
    |         |- tpl
    |              |- element_rssfeed
    |                   |- rssfeed.tpl
    |
    |- lang	
    |    |- module_elements
    |         |- lang_rssfeed_[de|en|...].php
    |
    |- metadata.xml
```

Compared to the simple-element, the template-files have been added.

Since the meaning of each file was already introduced in the simple-element tutorial, we'll skip this part right here.

##Implementing the required classes
Since all files are created, we'll continue to fill them with the relevant code-snippets.
Therefore we'll start with the installer.

###Installer
The name of the installers' class (and therefore the filename) is based on the name of the element.  The name follows the schema class_installer_element_name.php, so „class_installer_element_rssfeed“ in this case since our element will be named „rssfeed“.

The installer is created in the same way as the simple-element installer:

```
<?php
```
As usual, the installer extends “class_installer_base” and implements the interface interface_installer. Please take care to set the annotation “@moduleId” along with the value “_pages_content_modul_id_”. This annotation is used by the framework to register the element at the matching position in the database. All page-elements use the moduleId “_pages_content_modul_id_”, so there's no need to change the value.

```
/**
 * Installer to install a rssfeed-element to use in the portal
 *
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_rssfeed extends class_installer_base implements interface_installer {
```


The install()-method (required by the interface) is the place to trigger an elements' installation. It takes care of registering the element within the system.
Since the rss-feed element makes use of the generic “element_universal” table to store the url of the rss-feed, there's no need to create an additional table. Nevertheless, if the element would require an additional table, e.g. to store a set of fields, the install-method would be the right place to create such a table, too. A simple way to create a new table would be:

```
$arrFields = array();
$arrFields["content_id"]            = array("char20", false);
$arrFields["tellafriend_template"]  = array("char254", true);
$arrFields["tellafriend_error"]     = array("char254", true);
$arrFields["tellafriend_success"]   = array("char254", true);

if(!$this->objDB->createTable("element_tellafriend", $arrFields, array("content_id")))
    $strReturn .= "An error occured! ...\n";
    
```
    
The statements create the table “element_tellafriend” with four columns. Tables are created using an abstract syntax of keywords, so no real DDL/SQL code. This allows Kajonas database layer to generate the DDL/SQL code matching your selected target-database. The createTable() method expects an array of 

```
$arrFields[„columnname“] = array(„datatype“, „isnull“ [, default value])
```

definitions.
Since the rssfeed element makes use of the universal-table, we stick to simply registering the element:  

```
public function install() {
    $strReturn = "";
    //Register the element
    $strReturn .= "Registering rssfeed-element...\n";
    //check, if not already existing
    if(class_module_pages_element::getElement("rssfeed") == null) {
        $objElement = new class_module_pages_element();
        $objElement->setStrName("rssfeed");
        $objElement->setStrClassAdmin("class_element_rssfeed_admin.php");
        $objElement->setStrClassPortal("class_element_rssfeed_portal.php");
        $objElement->setIntCachetime(3600);
        $objElement->setIntRepeat(0);
        $objElement->setStrVersion($this->objMetadata->getStrVersion());
        $objElement->updateObjectToDb();
        $strReturn .= "Element registered...\n";
    }
    else {
        $strReturn .= "Element already installed!...\n";
    }
    return $strReturn;
}
```


The second method required to be implemented by the interface is an “update()” method. As the name indicates, it's used to update the element, e.g. if a new version is released. The update-method is called by the framework and your spot to update the contents of the element. If you don't want to execute any special update-sequences, just update the elements' version-number and that's it:

```
public function update() {
    $strReturn = "";
    
    if(class_module_pages_element::getElement("rssfeed")->getStrVersion() == "4.2") {
        $strReturn .= "Updating element rssfeed to 4.3...\n";
        $this->updateElementVersion("rssfeed", "4.3");
        $this->objDB->flushQueryCache();
    }
    return $strReturn;
}
```

###Backend-View
The elements' backend-view is shown as soon as a user creates or modified a rssfeed element on a page. The view is used to enter the url of the feed to be shown, to select a template to format the portal-output and finally it's used to set up the amount of news-entries to be shown in the portal.

```
<?php
/**
 * @targetTable element_universal.content_id
 */
class class_element_rssfeed_admin extends class_element_admin implements interface_admin_element {
```

As you may have guessed already, the element extends from a common base class and implements an interface.

But – since the element stores data in a table, it's now time to declare the target-table. The object-mapper integrated within Kajona will use this table to store the values of the element. The table is set up using the syntax

```
@targetTable tablename.primaryColumn
```


By definition, the primary-column of an element-table has to be named content_id. You can't use another column name for the primary key in this case. The annotation has to be placed on class-level.

Since it's a common way to name a classes' properties first, we'll stick to that and name the properties we want to fetch from the user. In other words: Each element of the elements' form is represented by a single property:

```
/**
 * @var string
 * @tableColumn element_universal.char1
 *
 * @fieldType template
 * @fieldLabel template
 *
 * @fieldTemplateDir /element_rssfeed
 */
private $strChar1;
```

The first property we introduce is called „strChar1“. You are totally free when it comes to naming a property, there's no reason why the property was named „char1“. You may use „template“, „bazinga“ or anything else. The magic is created by the properties' annotations:
@tableColumn maps the property to a column of the database-table introduced before. As soon as this mapping is set up, the framework is able to load the value of the property from this column and to store the value back to this field.

@fieldType controls the rendering in the backend. Since we want to use the property to store the elements' template, we want a dropdown of templates available. This is achieved by using the formType „template“. Please note that this formentry requires the annotation @fieldTemplateDir in order to know what folders should be scanned for possible templates. The framework scans all template-folders under /core and all template-packs to search for elements saved in an element_rssfeed named folder.

@fieldLabel is used to define a lang-key used to label the field. The label is rendered right before the input-element. The text is loaded from the lang-file located at element_rssfeed/lang/module_elements/lang_rssfeed_en.php.

```
/**
 * @var string
 * @tableColumn element_universal.char2
 *
 * @fieldType text
 * @fieldLabel rssfeed_url
 * @fieldMandatory
 *
 * @elementContentTitle
 */
private $strChar2;
```

Char2 will be used to store the url of the rssfeed to be loaded. Since this information is required in order to render a feed, we mark the property with @fieldMandatory. If the user tries to save the element without an url, the system will show a validation error rather then saving the element to the database.

```
/**
 * @var string
 * @tableColumn element_universal.int1
 *
 * @fieldType text
 * @fieldLabel rssfeed_numberofposts
 */
private $intInt1;
```

What is still missing from the class (but required by the database-mapper) is the full list of getters and setters in order to access the properties:

```
 public function setStrChar2($strChar2) {
     $this->strChar2 = $strChar2;
 }
 public function getStrChar2() {
     return $this->strChar2;
 }
 public function setStrChar1($strChar1) {
     $this->strChar1 = $strChar1;
 }
 public function getStrChar1() {
     return $this->strChar1;
 }
 public function setIntInt1($intInt1) {
     $this->intInt1 = $intInt1;
 }
 public function getIntInt1() {
     return $this->intInt1;
 }
}
```

If you want to get an overview of all formentry-types available:

http://www.kajona.de/en/Support/Documentation-V4/Formentries-Overview/v4_formentries.html

And the full list of annotations available:
http://www.kajona.de/en/Support/Documentation-V4/Annotations-Overview/v4_annotations.html
To get an impression of what those few lines achieve – here is the mandatory screenshot of the elements backend-view:


![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/kajona_howto_complexpageelement_en.odt.png&maxWidth=547)

###Portal-View
Compared to the rather simple backend-part of the element, the real logic is hidden in the portal-part. It's the part where the remote-feed is being loaded, parsed and wrapped into a template before passing the content back to the page and to the browser.
The definition and the prologue is nearly the same as in the backend:

```
<?php
/**
 * Loads the rssfeed specified in the element-settings and prepares the output
 *
 * @targetTable element_universal.content_id
 */
class class_element_rssfeed_portal extends class_element_portal implements interface_portal_element {
```

The element extends a common base class and implements interface_portal_element.
In addition, the @targetTable annotation is declared, too. This parametrizes the database-mapper and tells the mapper where to fetch the content from.

The data is available in the array “arrElementData”, so via 

```
$this->arrElementData[“columnName”]
```

The only method required to be implemented is “loadData()”. The hook method is called by the framework and passes control to the element. It's now up to the element to generate the portal-content.

```
 public function loadData() {
  $strReturn = "";
```
  
To load the remote-feed, the element makes use of Kajonas remote-loader. The loader handles all calls and connection logic and passes back the content of the rss-feed as a simple string:

```
$strFeed = "";
try {
    $objRemoteloader = new class_remoteloader();
    $objRemoteloader->setStrHost(str_ireplace("http://", "", $this->arrElementData["char2"]));
    $objRemoteloader->setIntPort(0);
    $strFeed = $objRemoteloader->getRemoteContent();
}
catch (class_exception $objExeption) {
    $strFeed = "";
}
```

Please be aware that 

```
$this->arrElementData["char2"]
```

references the value saved in the column char2 of the mapped table. This is the logical connection to the values created by the elements backend view.
Another task is to load all required template sections from the filesystem using the template-engine integrated into Kajona. The engine returns an identifier which will be used to reference the templates later on:
  
```  
  $strFeedTemplateID = $this->objTemplate->readTemplate("/element_rssfeed/".$this->arrElementData["char1"], "rssfeed_feed");

  $strPostTemplateID = $this->objTemplate->readTemplate("/element_rssfeed/".$this->arrElementData["char1"], "rssfeed_post");
```

Again,

```
$this->arrElementData["char1"]
```

is the reference to the template defined by the elements' backend view.

Before starting to parse the xml-contents of the feed, check if anything was loaded and print a message in case of errors:

```
  $strContent = "";
  $arrTemplate = array();
  if(uniStrlen($strFeed) == 0) {
    $strContent = $this->getLang("rssfeed_errorloading");
  }
  else {
```

The processing of the feed is based on a simple array, therefore the internal methods of Kajona are used to transform the string into an xml-tree and to transform it into an array. Please note that there are a ton of xml-processors for PHP such as simpleXML or others, feel free to use the library of your choice.

```
   $objXmlparser = new class_xml_parser();
   $objXmlparser->loadString($strFeed);
   $arrFeed = $objXmlparser->xmlToArray();
   if(count($arrFeed) >= 1) {
```

Since the element supports RSS- and and ATOM-feeds there are slightly different processing-parts for each source-type.

```
//rss feed
if(isset($arrFeed["rss"])) {

  $arrTemplate["feed_title"] = $arrFeed["rss"][0]["channel"][0]["title"][0]["value"];
  $arrTemplate["feed_link"] = $arrFeed["rss"][0]["channel"][0]["link"][0]["value"];
  $arrTemplate["feed_description"] = $arrFeed["rss"][0]["channel"][0]["description"][0]["value"];
  $intCounter = 0;
  foreach ($arrFeed["rss"][0]["channel"][0]["item"] as $arrOneItem) {
   $arrMessage = array();
   $arrMessage["post_date"] = (isset($arrOneItem["pubDate"][0]["value"]) ? $arrOneItem["pubDate"][0]["value"] : "");

   $arrMessage["post_title"] = (isset($arrOneItem["title"][0]["value"]) ? $arrOneItem["title"][0]["value"] : "");

   $arrMessage["post_description"] = (isset($arrOneItem["description"][0]["value"]) ? $arrOneItem["description"][0]["value"] : "");

   $arrMessage["post_link"] = (isset($arrOneItem["link"][0]["value"]) ? $arrOneItem["link"][0]["value"] : "");
```

All common properties of the feed are collected in the array $arrTemplate. While iterating over the feed-entries, the properties of the single elements are stored in $arrMessage. Each message is then rendered into the template read before. The template-section read before was setup with “rssfeed_post”, so this is the section where an entries' properties will be rendered to. 

By definition, an entry of the array will be rendered to the placeholder with the same name as the key of an element. This means: The entry $arrMessage[“post_title”] = “News title” will be rendered to the placeholder %%post_title%%. The mapping is array-key = placeholder-name.


```
      $strContent .= $this->objTemplate->fillTemplate($arrMessage, $strPostTemplateID);
      if(++$intCounter >= $this->arrElementData["int1"])
       break;
      }
     }
```

The structure of an atom-element differs only slightly, so we won't dive into details.

```
 //atom feed
 if(isset($arrFeed["feed"]) && isset($arrFeed["feed"][0]["entry"])) {
  $arrTemplate["feed_title"] = $arrFeed["feed"][0]["title"][0]["value"];
  $arrTemplate["feed_link"] = $arrFeed["feed"][0]["link"][0]["attributes"]["href"];
  $arrTemplate["feed_description"] = $arrFeed["feed"][0]["subtitle"][0]["value"];
  $intCounter = 0;
  foreach ($arrFeed["feed"][0]["entry"] as $arrOneItem) {
   $arrMessage = array();
   $arrMessage["post_date"] = (isset($arrOneItem["updated"][0]["value"]) ? $arrOneItem["updated"][0]["value"] : "");

   $arrMessage["post_title"] = (isset($arrOneItem["title"][0]["value"]) ? $arrOneItem["title"][0]["value"] : "");

   $arrMessage["post_description"] = (isset($arrOneItem["summary"][0]["value"]) ? $arrOneItem["summary"][0]["value"] : "");

   $arrMessage["post_link"] = (isset($arrOneItem["link"][0]["attributes"]["href"]) ? $arrOneItem["link"][0]["attributes"]["href"] : "");

   $strContent .= $this->objTemplate->fillTemplate($arrMessage, $strPostTemplateID);
   if(++$intCounter >= $this->arrElementData["int1"])
    break;
   }
  }
 }
 else {
  $strContent = $this->getLang("rssfeed_errorparsing");
 }
}
```

Finally all single feed-entries are placed into a wrapping template-section, e.g. to render a div around the list or to render other global code. And that's it!

```

   $arrTemplate["feed_content"] = $strContent;
   $strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strFeedTemplateID);
   return $strReturn;
  }
 }
```

Mandatory screenshot of the portal output:

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/kajona_howto_complexpageelement_en.odt2.png&maxWidth=482)

##Template
The first section of the template is used to render properties of the feed and to provide a wrapper for the entries. Common strings are loaded from the language-file using the [lang,key,elements] syntax. The placeholder feed_content will contain the list of newsfeed-entries.

```
<rssfeed_feed>
  <div class="rssfeed">
    <p>
      [lang,commons_title,elements] %%feed_title%% (%%feed_link%%)<br />
      [lang,commons_description,elements] %%feed_description%%
    </p>
    <ul>%%feed_content%%</ul>
  </div>
</rssfeed_feed>
```

The second section, rssfeed_post is used to render a single newsfeed-entry:

```
<rssfeed_post>
    <li>
        <div><a href="%%post_link%%" target="_blank">%%post_title%%</a> (%%post_date%%)</div>
        <div>%%post_description%%</div>
    </li>
</rssfeed_post>
```

##Language-Entries
The language file stores all strings used to label form-entries or portal-elements. The file is based on an array with key – value pairs:

```
<?php

$lang["element_rssfeed_name"]            = "RSS feed";
$lang["rssfeed_numberofposts"]           = "Number of messages to show";
$lang["rssfeed_url"]                     = "RSS feed url";
$lang["rssfeed_errorloading"]            = "Error loading the feed.";
$lang["rssfeed_errorparsing"]            = "Error while parsing the feed.";
$lang["rssfeed_noentry"]                 = "No entries available";
```


##metadata.xml
To finalize the element we'll have a look at the metadata.xml file, too. It is used to store all relevant meta-information of the element, such as the name of the element, the version or the author (that could be you!). The file is processed by the package-management in order to validate if the package may be installed or not. 

```
<?xml version="1.0" encoding="UTF-8"?>
<package
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="http://apidocs.kajona.de/xsd/package.xsd"
        >
    <title>rssfeed</title>
    <description>Include external rss-/atom sources into your website.</description>
    <version>4.3</version>
    <author>Kajona Team</author>
    <type>MODULE</type>
    <providesInstaller>TRUE</providesInstaller>
    <requiredModules>
        <module name="system" version="3.4.9.3" />
        <module name="pages" version="3.4.9.1" />
    </requiredModules>
</package>
```

Most of the values are self-describing and require no further explanation.
Make sure that the value of the title-element is the same as the name of the element when installing the element, otherwise the installer may get confused when searching for elements.
That's it, your first complex element is ready to be used. Deploy it to your local system and give it a try.

If you want to spread your element, make use of the KajonaBase to publish the element to other users. The website contains all relevant information on how to package and upload your element: http://www.kajonabase.net 


Have fun extending Kajona!