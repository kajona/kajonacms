
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

Files regarding the configuration of the system and  will be placed below ```/project```. This includes the configuration of the database-access, logfiles or files overwriting something from the core. There's no need to have the contents below ```/project```accessible by your visitors, therefore a default ```.htaccess``` denies all requests.

###Packages
Having a look into ```/core```, you may have noticed that all modules are shipped as phar-files. The phar-files provide a super easy way when it comes to updating the installation - just replace the old phar with the new one and your done. In addition, the phar-files are harder top modify - not only due to security reasons, but also to avoid that users change system-files directly (don't worry, you are allowed and encouraged to redefine and change everything). 

While phar-files are cool for production systems, they may slow down the development process: After changing a file, the phar-creation and deployment are two steps too much and way to time-consuming. Therefore, the framework also supports to handle extracted modules. If you have a look at our [git-repo](https://github.com/kajona/kajonacms), all modules are organized within plain folders. If you want to have a look at a module or one of its files, go ahead and replace the module-phar with the corresponding github version or extract the phar. The system will just keep working as before. Yes, this means that e.g. ```/module_pages.phar``` will be handled the same way as ```/module_pages``` ([Gitib Master](https://github.com/kajona/kajonacms/tree/master/module_pages)). 

###/files/extract
Naturally, webbased software comes with a bunch of statical resources such as javascript files, css files or images. By default, those resources are bundled in the phar-file are therefore not accessible by the browser directly (yes, we know that the files could be made accessible by php). Therefore the framework extracts all statical contents in to the world-readable directory ```/files/extract```. This extraction is fired as soon as a phar-files changes, the detection whether a phar changes is handled by the Kajona bootstrap and the [subsystems](https://github.com/kajona/kajonacms/blob/master/module_system/system/PharModuleExtractor.php) automatically.

But - of the path of the files change, how to load them? Whats the right place to load a js-file from, would it be:

	/core/module_pages/admin/scripts/pages.js
	
or

	/files/extract/module_pages/admin/scripts/pages.js
		
For most cases, the matching path is resolved internally and automatically using the Kajona-Loader:

	<script type="text/javascript">
    KAJONA.admin.loader.loadFile('/core/module_pages/admin/scripts/pages.js', function() {
	    KAJONA.admin.pages.initBlockSort();
    });
	</script>

This means, just include everything as you would when developing a new module, so below ```/core```. The js-loader takes care of 

 * detecting if a module is deployed as a phar or an extracted module
 * rewriting the path if necessary

When working in templates, you may make use of the [webpath-scriptlet](https://github.com/kajona/kajonacms/blob/master/module_system/system/scriptlets/ScriptletWebpath.php), resolving everything automatically, too:
 
    <link rel="stylesheet" href="_webpath_/[webpath,module_system]/admin/scripts/jqueryui/css/smoothness/jquery-ui.custom.css?_system_browser_cachebuster_" type="text/css" />


###Change it!
But if the default ships phars, then how to change something from the core? Isn't one of the main advantages (and disadvantages) of PHP, that you are able to read and change files easily? Say no more, this is still possible using the ```/project``` folder. Just place a file in a similar named folder below ```/project```. Let's have a look at an example - if you need to change the mail-class, the file to change would be ```/core/module_system/system/Mail.php```. Since the file is packaged in a phar, you need to copy the file to ```/project/module_system/system/Mail.php```. Change the file according to your needs and hit the reload button.

> Heads up! Since Kajona caches various internal information, you may need to delete the cache-directory ```/project/temp/cache```.



##Configuration / Performance / Debugging / Caching

##Permissions

