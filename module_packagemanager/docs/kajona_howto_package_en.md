# Packages

Since Kajona V4 all relevant components of the system are bundled, deployed and managed in packages. A packages may be a collection of elements, a module or even a template-pack (in other words an isolated layout).
Packages may be deployed using the built in package management. Therefore they must be built following a few conventions and contain some metadata.

##Module / Element package
Module- and element-packages each contain a full filesystem layout. It follows the regular guideline and contains the folder admin, installer, lang, portal, system, templates and the later on described file metadata.xml.

##Template package
Template packages are built in a common way, too. On top level, they are made of the folders css, images and tpl in nearly all cases. In addition, the metadata.xml file is added to the package.
##metadata.xml
The metadata.xml file is added to each packages and describes the package itself. Beside common information like the name of the package and a readable description, it contains technical information such as the version of the package or the requirements for installing the package.
The structure of the file should be explained using an example:

```
<?xml version="1.0" encoding="UTF-8"?>
<package
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="http://apidocs.kajona.de/xsd/package.xsd"
    >
    <title>faqs</title>
```
    
The title describes the package in a short, precise and unique name. The following description can be used to add textual description of the packages' content.

```
    <description>A module to organize frequently asked questions.</description>
```
    
One of the most important entries is the version-number. Based on this number the system is able to detect the availability of updates for installed packages.

```
    <version>3.4.9</version>
    <author>Kajona Team</author>
```
    
The naming of the packages' author is not that important, but required to earn the glory.

The next element “target” names the folder of the package-content, during installation the package-manager copies the content to the folder specified. Nevertheless, the element is optional. If not specified, the target path is built out of the elements' title. You only have to specify the target if the title and the folder in file system differ.

```
    <target>module_faqs</target>
```

The type of the package not only categorizes the content, it even triggers the matching package-controller of the package-manager. Currently, the only values allowed are MODULE, ELEMENT, TEMPLATE.

```
    <type>MODULE</type>
    <providesInstaller>TRUE</providesInstaller>
```

If a package is deployed with an installer of without any installer is defined using the value of providesInstaller (TRUE or FALSE). Only if set to true the packages' installer is triggered during installation, otherwise the installer is skipped. Templates have to define the value FALSE.

By specifying a list of requiredModules, it's possible to define a list of dependencies. Only if all listed modules are installed in at least the version given, the installation may be finalized successfully. If you don't require any module, you could leave the requiredModules element empty.

```
    <requiredModules>
        <module name="system" version="3.4.9.3" />
        <module name="pages" version="3.4.9.1" />
    </requiredModules>
```

For some packages such as templates some screenshots provide more than words. Therefore you could use the optional screenshots-element and add up to three screenshot-elements. Each element is required to have an attribute path, describing the path to the screenshot inside the package. You are allowed to use images with on of the following extensions: png, jpg and gif.

```
    <screenshots>
        <screenshot path=”/screenshot.jpg” />
    </screenshots>
</package>
```

##ZIP-structure
All packages are built and deployed in a zip-archive. When creating the archive, make sure to have the contained folders and the metadata.xml file on top level. A folder like “module_faqs” is not allowed to be included inside the package.