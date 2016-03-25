
#Kajona in general

Kajona is a content management framework released under an open source license.
By default the framework is bundled as a web content management framework with a comprehensive set of modules and features, providing a strict separation of layout and content.

Due to the modular design and the integrated template engine, Kajona is easy to extend and customize.

Kajona is split into two main subsystems:
The backend, used to administrate and maintain the website and the portal, rendering all contents created and maintained by the backend.
The content and the portal are glued by templates, controlling the behavior and the layout of your template.

A fully integrated portal-editor allows in-page editing, making the fully fledged backend obsolete for most everyday tasks. The portal-editor is enabled automatically as soon as you are logged in with an admin-account browsing the portal.

The portal is opened automatically when opening the installation-folder if Kajona with your webbrowser. To access the administration backend, just append ```/admin``` to the installation-directory, e.g. ```http://my-website.com/kajona/admin``` or ```http://my-website.com/admin```. Log in using the credentials provided during the installation.


##Requirements
Kajona is written in PHP and requires at least PHP version 5.5. Kajona requires the following PHP-modules to be available in order to run smoothly:

* mbstring
* gd-lib
* xml
* zip
* openssl

In addition, one of the following databases is required:

* Mysql
* MariaDB
* PostgreSQL
* Oracle
* Sqlite 3

New databases may be connected by adding a new database-driver to the database-abstraction.

PHP need to be installed along a webserver like Apache HTTPD, but every other webserver like IIS oder nginx is totally fine, too.



##Filesystem
Right after downloading and extracting the framework, filesystem contains a structure similar to the following layout:

	/admin
	/core
	/files
	/project
	/templates
	download.php
	image.php
	index.php
	installer.php
	xml.php
	
The ```/admin``` folder is used a simple placeholder to access the administration backend. In fact, the folder only contains a file with a redirect to the ```index.php?admin=1```url.

All modules are located under ```/core```. This means, here's the real logic. Kajona modules are downloaded into this directory, updates are deployed to this directory, too. The name ```core``` is fixed, but you are allowed (and invited) to add your own core-directories, too. Let's say you're developing a new module but want to keep it separate from the Kajona-packages, you could create a new core-directory ```/core_addons``` and start working there. As long as the folder is prefixed with ```core_``` everything will run smoothly.

All uploads and media such as images or files go into ```/files```. This folder needs to be writeable and readable by the webserver, otherwise your users won't be able to upload new files.

Files regarding the configuration of the system and  will be placed below ```/project```. This includes the configuration of the database-access, logfiles or files overwriting something from the core. There's no need to have the contents below ```/project```accessible by your visitors, therefore a default ```.htaccess``` denies all.

###Packages

###/files/extract



##Configuration / Perfomance / Debugging / Caching

##Permissions

