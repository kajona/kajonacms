#Simple Pageelement


This tutorial focuses on the development of a simple page element for Kajona. The only task of the element is to print the date of the last modification of the current portal-page. This means, when opening a portal-page in the browser, a timestamp is printed on the page. This allows visitors to see whether the page changed since their last visit or not.
Since the element doesn't require an entry in the database (it's not configurable), the element is of the simplest possible nature.
Based on the structure described below, the element could be used as a template for simple elements, or even as an entry version for more complex elements.

The latest version of the lastmodified-element is available for download on http://www.kajona.de. 

This tutorial is based on Kajona V4.3.

##Creating the filesystem layout

When creating a new element, it's main focus is to be installed on your system/website. Nevertheless, since all elements share a common structure, you may enrich the element with your copyright data and a few credits and publish it to other Kajona users later on. In this way, your work may be spread to other Kajona users and installations and you could earn all the glory.

Since we want to create an element rendering the date of the last modification, we'll call it “lastmodified”. Therefore the root-folder of the element is called “element_lastmodified”.

Start by creating the following structure of files and folders in your installations' /core folder:

```
element_lastmodified
    |- admin
    |    |- elements
    |         |- class_element_lastmodified_admin.php
    |
    |- installer
    |    |- class_installer_element_lastmodified.php
    |
    |- lang
    |    |- module_elements
    |         |- lang_lastmodified_[de|en|bg|pt].php
    |- portal
    |    |-elements
    |         |- class_element_lastmodified_portal.php
    |
    |- metadata.xml
```    
    
To get a first understanding of the structure, we'll have a look at each file:

* /admin/elements/class_element_lastmodified_admin.php
This file contains the backend-representation of the element. As soon as a user creates or edits a lastmodified-element, this class takes care of the backend-view.

* /installer/class_installer_element_lastmodified.php
As the name already indicates, the installer takes care of setting up the element during the installation of the element. The installation may be run during a full system-installation or afterwards, when adding the element to an installation already existing. 

* /portal/elements/class_element_lastmodified_portal.php
The portal-class takes care of rendering the contents on a portal-page. It is called each time a portal-page is generated, e.g. when a visitor opens the page in a web browser.

* /lang/module_elements/lang_lastmodified_en.php
All strings and translations are placed in a lang-file. If you want to provide translations for different languages, add additional files with the matching suffix (e.g. lang_lastmodified_de.php containing the german translations).

* /metadata.xml
The metadata.xml file contains a description of the element and requirements.
 
##Implementing the required classes
Each file contains a special part of the element, therefore we'll step through each of them.

###Installer
The filename of the installer is based on the scheme class_installer_element_name.php, in this case class_installer_element_lastmodified.php. 

The main purpose of the installer is to register the element with the pages-module, otherwise the element is unknown and could not be created using the backend.

Let's step through the installer line by line:

```
<?php
```

We start with the declaration of the installer-class. By definition, an installer has to extend the class_installer_base base-class, inheriting all relevant methods. In addition, the implementation of the interface interface_installer ensures you provide all relevant methods.

Please note the annotation `@moduleId`. This annotation is required in order to categorize the element within Kajonas system-structure. For all page-elements, the @moduleId value is _pages_content_modul_id_, only modules may use other values.

```
/**
 * @moduleId _pages_content_modul_id_
 */
class class_installer_element_lastmodified extends class_installer_base implements interface_installer {
```

The “install” method is the main method of the installer and being called by the framework. The method is used to set up all relevant data and to register the element with the system.

Therefore we check if the element is already installed since we don't want to have the element being registered twice.

If the element is still missing, the installation is handled by a new instance of the class “class_module_pages_element”. We use the object to pass all relevant properties and settings of the lastmodified element:

* setStrName is used to register the name of the new element, “lastmodified”. By this name the element may be used in the portal and templates. When registering the element named “lastmodified”, a valid syntax for placeholders in templates would be %%title_lastmodified%%.
* setStrClassAdmin stores the filename of the admin-representation.
 *setStrClassPortal stores the filename of the portal-representation.
* setIntCachetime takes the maximum number of seconds a generated portal-representation may be cached. This means, a generated portal-output won't be regenerated for the given amount of seconds.
* setIntRepeat allows or disallows to have more then one instance of the element per placeholder (the element is “repeatable”).
* setStrVersion passes the version of the current element from the metadata.xml file to the system.

By calling updateObjectToDb(), the passed data is stored to the database and the element is registered within the system. 

```
public function install() {
    $strReturn = "";
    //Register the element
    $strReturn .= "Registering lastmodified-element...\n";
    //check, if not already existing
    if(class_module_pages_element::getElement("lastmodified") == null) {
        $objElement = new class_module_pages_element();
        $objElement->setStrName("lastmodified");
        $objElement->setStrClassAdmin("class_element_lastmodified_admin.php");
        $objElement->setStrClassPortal("class_element_lastmodified_portal.php");
        $objElement->setIntCachetime(60);
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

Page-elements may be updated as soon as a new version is released (the version found in the package is higher then the version of the installed element). In case of an update, the framework calls the update() method. Place your update-sequences (if required) within the method or an additional update-method. Make sure to update at least the version of the element to the latest one.

```
public function update() {
  $strReturn = "";

  if(class_module_pages_element::getElement("lastmodified")->getStrVersion() == "4.2") {
      $strReturn .= "Updating element lastmodified to 4.3...\n";
      $this->updateElementVersion("lastmodified", "4.3");
      $this->objDB->flushQueryCache();
  }
  return $strReturn;
}
```

###Backend-View
When placing an element on a page, a simple form is shown to enter all relevant data. Since the lastmodified element doesn't handle any additional settings, the backend-class is kept rather short.

By convention, an elements' backend class has to extend the base-class class_element_admin and implement the interface interface_admin_element.

Nevertheless, the lastmodified-element class remains empty:

```
<?php

class class_element_lastmodified_admin extends class_element_admin implements interface_admin_element {  

}
```

Since the element doesn't take any arguments, no additional code has to be added. If the element could be parametrized, the different properties would be added (see the tutorial “complex page element”).

When creating a new lastmodified element using the backend, the framwork creates the following form:

![](https://www.kajona.de/image.php?image=/files/images/upload/howtos/kajona_howto_simplepageelement_en.odt.png&maxWidth=557)

###Portal-View
The third file is the portal-class of the element. As you already guessed, the portal-class has to extend a base-class and implement an interface, too.

In this case, the base class is class_element_portal whereas the interface is named interface_portal_element.

The interface guarantees you implement the method “loadData”, the hook-method being called by the framework as soon as a page is being generated.

```
<?php

class class_element_lastmodified_portal extends class_element_portal implements interface_portal_element {
```

The real work is done in loadData. Since we want to print the date of the last modification of the current page, the first task is to fetch an instance of the current page. This is done via class_module_pages_page::getPageByName(). By using $this->getPagename() the framework looks up the name of the page being generated and passes it to the factory-method getPageByName.
 
By querying the page-object using getIntLmTime(), the date of the last modification is returned as an unix-timestamp. timeToString simply transforms the timestamp to a readable string.

Since a prefix like “Last modified:” would be nice, the text is loaded from the lang-file (see below) using $this->getLang().

```
  public function loadData() {
    $strReturn = "";
    //load the current page
    $objPage = class_module_pages_page::getPageByName($this->getPagename());
    $strReturn .= $this->getLang("lastmodified").timeToString($objPage->getIntLmTime());
    return $strReturn;
  }
}
```

The generated output looks like following:


###Language-Entries
In order to load a lang-entry named “lastmodified”, the entry has to be placed into the lang-file, here lang_lastmodified_en.php.

The file contains two entries: “element_lastmodified_name” is a placeholder being loaded by the framework in order to label the “new”-button when editing a pages' contents. The name of the entry is defined by convention: “element_name_name”.

```
<?php
$lang["element_lastmodified_name"]       = "Date of last modification";
$lang["lastmodified"]                    = "Last modified: ";
```

The second entry, „lastmodifed“ is the text being loaded during the portal-generation of the class. The key „lastmodified“ is the same as when calling $this->getLang(„lastmodified“) and therefore the missing glue between the lang-file and the language-object.

###Metadata.xml
The metadata.xml file contains a descriptive xml-document. It contains general information such as the name of the element or the description, but also technical requirements and the author of the element. Since the metadata.xml is parsed by both, the package-management and the KajonaBase (the central repository for user-extensions), this file is the place to earn all the glory for the element.

```
<?xml version="1.0" encoding="UTF-8"?>
<package
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:noNamespaceSchemaLocation="http://apidocs.kajona.de/xsd/package.xsd"
    >
  <title>lastmodified</title>
  <description>Renders the date of the pages' last modification.</description>
  <version>4.3</version>
  <author>Kajona Team</author>
  <type>ELEMENT</type>
  <providesInstaller>TRUE</providesInstaller>
  <requiredModules>
    <module name="system" version="4.3" />
    <module name="pages" version="4.3" />
  </requiredModules>
</package>
```

Most of the values are self-describing and require no further explanation.
Make sure that the value of the title-element is the same as the name of the element when installing the element, otherwise the installer may get confused when searching for elements.


That's it, your first element is ready to be used. Deploy it to your local system and give it a try.
If you want to spread your element, make use of the KajonaBase to publish the element to other users. The website contains all relevant information on how to package and upload your element: http://www.kajonabase.net 


Have fun extending Kajona!