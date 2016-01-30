#Quick Install Guide

This document describes the installation of a new Kajona-system in a few steps.
More detailed information can be found at www.kajona.de.

##Download
If not already done, the installation of Kajona starts with downloading the system from www.kajona.de. 
You'll find two packages: a full and a light download. Compared to the full-package, the light one misses a few modules and elements.
Nevertheless, you're able to extend your installation later on easily. New modules and elements may be added anytime.

##Extract the files
Right after downloading the zip-file you should extract the files – either on your webserver or, most commonly, on your local machine.
After extracting the package, you should find a file-structure like following (the list may vary depending on the modules and packages downloaded):

	/admin
	/core
	/files
	/project
	.htaccess
	download.php
	index.php
	installer.php
	image.php
	xml.php
	
You may now proceed with the step „Upload“.

##Upload
The system is now ready to be uploaded to the webserver. In most cases, this is done by FTP or SSH. If you're going to install Kajona in your local test environment you may just use your file manager to copy the files into your webserver's document root. When all files are available through your webserver, proceed with „Setting up file-permissions“.

##Setting up file-permissions

In order to complete the installation successfully, you have to set up a few file- and folder permissions. Rather the webserver needs write-permissions on the following files and folders:

	/core
	/project/system/config
	/project/dbdumps
	/project/log
	/project/temp
	/files/cache
	/files/images
	/files/public
	/files/downloads
	
The permissions can be set using nearly any FTP/SSH-client (hint: chmod 0777). If all permissions are set up, proceed with „Create database“. 

##Create database
The database to be used by Kajona has to be set up in advance. Kajona doesn't need its own database, it can be installed along with other applications into an existing database. You should note the database name, user and password.

New database often can be created by using a tool like phpMyAdmin or your webhoster's admin-panel.
Go on with „Loading the installer“.

##Loading the installer
As soon as the database is available, the installer may be called. When opening your installation via a webbrowser for the first time, the system detects the missing database-initialization and forwards to the installer.

To load the installer, open ``http://www.my-domain.xy`` in your webbrowser.
If the Kajona is located in a subfolder, the path has to be extended, e.g.: ``http://www.my-domain.xy/my_subfolder``.
At first the installer checks the correct file-permissions as described in the step „Setting up file-permissions“. In addition, the availability of some php-modules is checked.

Now you have to provide the database name, user and password as noted before.
After clicking on „write to config.php“, another form is being shown. Using this form, you can set up the username and password of the admin user-account of your system along with your e-mail adress.

Afterwards, you may chose the installation mode. In nearly all cases, the “automatic installation” is the way to go – all modules and elements available will be installed.
If all modules and page elements are installed, your installation of Kajona is complete.
Out of security-reasons you now should delete the /installer.php and the /v4_v4_postupdate.php-files (if given). Go on with „The portal is available“.

##The portal is available
After the installation, make a first test: Load the portal by opening the root folder of the installation in your webbrowser, e.g.
``www.my-domain.xy/``
or 
``www.my-domain.xy/my_installation/``.

If you installed the module „samplecontent“, a welcome-page should be loaded. Otherwise you should at least get an error, complaining about a missing index-site.
Congratulations – Kajona was installed successfully. To change the contents of your portal, go on and „Log in at the administration“.

##Log in at the administration
To log in at the administration, attach the folder „/admin“ to your portal-url:
``www.my-domain.xy/admin/``
or
``www.my-domain.xy/my_installation/admin/``.

You should now see a login-form. Provide the username and password you've chosen during the installation and log in.


Have fun using Kajona! ;-)