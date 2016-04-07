#Update Guide: Kajona v4 to v5


This guide helps when updating a Kajona v4 installation to the new Kajona v5.

##Preparations

###Update your installation
The Kajona v4 installation should be update to the latest 4 release. This is Kajona v4.7 (module system to v4.7.1). The update is supported using the integrated package management.

### Backup your installation
As usual, when it comes to updating a Kajona system make sure to back up **ALL**  data. This includes both, the filesystem and the database.

##V5 pre update
We created a simple script that will take care of updating your template pack so nothing gets lost during the update.


Download or copy the file 
	
	https://github.com/kajona/v5preupdate/blob/master/v5preupdate.php 
	
to your Kajonas root-folder so the file is on the same level as the index.php, download.php and image.php files.

Open up your browser and point it to the newly created file:

	http://your-kajona-installation/v5preupdate.php
	
The script should print a bunch of messages and report to you, that it saved all templates to either a newly created template pack, or that it added the missing templates to your current template pack.

In addition, the script moves some if the files below /project to their new location.

Delete the ```/v5preupdate.php```script and proceed.

##Download v5 packages
Starting with v5, modules and elements are shipped as phar-files. Therefore all module- and element-directories below ```/core``` should be replaced by the corresponding phar. Go to https://www.kajona.de and download the relevant packages according to your installation and delete the v4 folders.

> Heads up! In Kajona V5 every package is a module. This means that the previous ```element_formular``` is now called ```module_formular```.

If you finished downloading the packages, only phar-files should be listed below ```/core```.

##Update your root-files
Replace your download.php, image.php, index.php, installer.php and xml.php files with a v5 version. You'll find them at the following locations:

| file | v5 download location |
|------|-----|
| /download.php |[https://raw.githubusercontent.com/kajona/kajonacms/master/module_mediamanager/download.php.root](https://raw.githubusercontent.com/kajona/kajonacms/master/module_mediamanager/download.php.root)|
| /image.php |[https://raw.githubusercontent.com/kajona/kajonacms/master/module_system/image.php.root](https://raw.githubusercontent.com/kajona/kajonacms/master/module_system/image.php.root)|
| /index.php |[https://raw.githubusercontent.com/kajona/kajonacms/master/module_system/index.php.root](https://raw.githubusercontent.com/kajona/kajonacms/master/module_system/index.php.root)|
| /installer.php |[https://raw.githubusercontent.com/kajona/kajonacms/master/module_installer/installer.php.root](https://raw.githubusercontent.com/kajona/kajonacms/master/module_installer/installer.php.root)|
| /xml.php |[https://raw.githubusercontent.com/kajona/kajonacms/master/module_system/xml.php.root](https://raw.githubusercontent.com/kajona/kajonacms/master/module_system/xml.php.root)|

##Prepare your database
Now that you've replaced all the v4 modules with fancy new phar-packages and that you've updated the root-files, you may prepare the database for v5. 

Speaking in technical words, kajona v5 organizes all classes in namespaces, therefore the class-names in your database need to be migrated.

As you may have guessed, we provide another script to prepare your database. It is located in the /core directory and named ```/core/V4toV5Migration.php```. If you don't have the script in your folder, please download it from [https://raw.githubusercontent.com/kajona/kajonacms/master/V4toV5Migration.php](https://raw.githubusercontent.com/kajona/kajonacms/master/V4toV5Migration.php).

Open your browser and point it to the file: 
	
	http://your-kajona-installation/core/V4toV5Migration.php
	
The script should spill out a bunch of messages and finish silently. No error means everything went well, the last entry could be s.th. like

	Updating table module_xmlfilenameadmin@system_module
	updating class_module_tags_admin_xml.php to TagsAdminXml.php
	updating class_module_system_admin_xml.php to SystemAdminXml.php
	updating class_module_dashboard_admin_xml.php to DashboardAdminXml.php
	updating class_module_mediamanager_admin_xml.php to MediamanagerAdminXml.php
	updating class_module_stats_admin_xml.php to StatsAdminXml.php										
From now on it's pretty straight forward:

##Call the installer
Call the installer and let you guide through the last parts of the update:

	http://your-kajona-installation/installer.php
	
If all modules have been updated, the system is back living and based on your old layout.

Happy v5ing!	