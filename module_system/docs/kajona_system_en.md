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

	/core/module_pages/scripts/kajona/pages.js
	
or

	/files/extract/module_pages/scripts/kajona/pages.js
		
For most cases, the matching path is resolved internally and automatically using the requirejs-loader:

	<script type="text/javascript">
	require(['pages], function(pages) {
	    pages.initBlockSort();
    });
	</script>

This means, just include everything as you would when developing a new module, so below ```/core```. The js-loader takes care of 

 * detecting if a module is deployed as a phar or an extracted module
 * rewriting the path if necessary

When working in templates, you may make use of the [webpath-scriptlet](https://github.com/kajona/kajonacms/blob/master/module_system/system/scriptlets/ScriptletWebpath.php), resolving everything automatically, too:
 
    <link rel="stylesheet" href="_webpath_/[webpath,module_system]/scripts/jqueryui/css/smoothness/jquery-ui.custom.css?_system_browser_cachebuster_" type="text/css" />
    
If you want to resolve a modules' location directly in PHP, the ```Resourceloader```is yours:

	Resourceloader::getInstance()->getWebPathForModule('module_system');    
	
And if you don't need the path accessible by a browser but rather the absolute path in the filesystem, including the ```phar://```stream wrapper if required:
	
	Resourceloader::getInstance()->getAbsolutePathForModule('module_system');
	    


###Change it!
But if the default ships phars, then how to change something from the core? Isn't one of the main advantages (and disadvantages) of PHP, that you are able to read and change files easily? Say no more - this is still possible using the ```/project``` folder. Just place a file in a similar named folder below ```/project```. Let's have a look at an example - if you need to change the mail-class, the file to change would be ```/core/module_system/system/Mail.php```. Since the file is packaged in a phar, you need to copy the file to ```/project/module_system/system/Mail.php```. Change the file according to your needs and hit the reload button.

> Heads up! Since Kajona caches various internal information, you may need to delete the cache-directory ```/project/temp/cache```.

Another example: You don't like the labeling of our modules or fields and therefore want to change the properties. You just want the FAQs module to be named ```Q&A```and no longer ```FAQ```.

Copy the file 

	/core/module_faqs/lang/module_faqs/lang_faqs_en.php
	
to

	/project/module_faqs/lang/module_faqs/lang_faqs_en.php	
and change the entry 
	
	$lang["modul_titel"] = "FAQs";
	
to

	$lang["modul_titel"] = "Q&A";	
	
> Heads up! If you only have the phar-packages, grab a copy of the file from [GitHub](	https://github.com/kajona/kajonacms/blob/master/module_faqs/lang/module_faqs/lang_faqs_en.php).	


##Configuration / Performance / Debugging / Caching

Many modules provide various config options. Kajona provides two ways of configuration options:

* web-based options, changeable by the user / administrator. Accessible by the admin-backend: module system -> system-settings
* file-based options, changeable if you have access to the filesystem, only. Located at each modules ```/system/config``` directory.

While all options are either self-describing or well documented, changing the file-based ones requires an additional, manual step. As you may have guessed, you need to copy the files-to-be-changed to your ```/project``` directory. It's not necessary to copy the whole file, you only need to add the values to be changed to the config file.

By default, the installer creates the file ```/project/module_system/system/config/config.php```. It contains at least the parameters to access the database. If you have a look at the [full config-file](https://github.com/kajona/kajonacms/blob/master/module_system/system/config/config.php), you may notice additional options. Most interesting are the options regarding the caching and the debugging. By default, caches are enabled in order to speed up the page-generation. If you're going to change various property-files, it makes sense to disable the location-caching for property-files by turning

    $config['bootstrapcache_lang'] = true;
    
to 
	
    $config['bootstrapcache_lang'] = false;
	    
Copy the file to ```/project/module_system/system/config/config.php```, flush the cache manually by deleting ```/project/temp/cache``` and the changed config value will be recognized by the framework.


##Permissions / System table

Kajona provides a sophisticated permission management system, allowing full control for each record. For each record may be granted one of the following permissions:

* view
* edit
* delete
* permissions
* changelog
* right 1
* right 2
* right 3
* right 4
* right 5

While the first five permissions are the same for each module, the last five ones are optional and to be used by each module individually. Whenever the five basic permissions are not sufficient for your use-case, make use of an individual one.

> Heads up! Since permissions are assigned to user-groups, a user assigned to not at least a single group won't be able to access anything on the website.

One of Kajonas central database-tables is the ```system``` table. The system table stores the metadata for each record, plus is creates the tree of records. Yes, that's right: All records are stored in a hierarchical structure.

The root-node has the system-id ```0```, below are the root-nodes for each module. A modules' records are located below the module-node, creating deeper structures if required:

* Root record
	* Module System
		* Aspect 1
		* Aspect 2	 	
	* Module Pages
		* Index pages
			* Sample page 1
			* Sample page 2
				* Subpage 1 	
	* Module FAQs	 
		* FAQ 1
		* FAQ 2

If not disabled, each records inherits the permissions from the superior record. As soon as a records creates its' own set of permissions, the inheritage-chain is interrupted, subordinate records will inherit the records' "new" permissions. 



