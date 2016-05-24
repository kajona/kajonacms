Kajona Content Management Framework
==========

Welcome to the sources of Kajona, an open source content management system.

Please refer to our website at http://www.kajona.de for further details and prepared builds ready for use.

**For prepared builds see [http://www.kajona.de/downloads.html](http://www.kajona.de/downloads.html)**


Bugtracker / Issues
---

Please feel free to report issues, ideas an general feedback using the GitHub issue pages
https://github.com/kajona/kajonacms/issues

Build-System
---
We currently provide various build-scripts in order to test, clean, build and package a project out of the sources.
Please have a look at the ant-scripts located at ```_buildfiles```: ```build_jenkins.xml```, ```build_project.xml```


Quickstart
---

You only have to follow a few steps in order to build a Kajona project out of the sources:

* Create a folder in your webroot, used to store the later Kajona project, e.g. ```kajona```
* Create a folder named ```core``` within the folder created before, e.g. ```kajona/core```
* Clone the Git-repo inside the core-folder: Change to the new directory and use the following command:
```git clone https://github.com/kajona/kajonacms.git .```

* The folder ```kajona/core``` should now be filled with a structure similar to:

	```
 /_debugging
 /module_system
 /module_pages
 /module_samplecontent
 /module_system
 /module_tags
 .htaccess
 bootstrap.php
 setupproject.php
```

* Open the file ```kajona/core/setupproject.php``` using the webbrowser of your choice (btw, you could run this script on the command line, too)
* After a few log-outputs, your ```kajona``` folder is now setup like a real Kajona project, so there should be a structure similar to

	```
 /core (as created manually)
 /files
 /project
 /templates
 .htaccess
 debug.php
 image.php
 index.php
 installer.php
 xml.php
```

_!!! Have a look at the end of the results: If you see some red lines (composer messages) the setupproject could NOT run the composer commands successfully! Please run the commands manually from the command line or a terminal window._

Done! All you have to do is to fire up your browser, opening the file ```kajona/installer.php``` and the installer will guide you through the process.
Whenever you make changes to s.th. below /core, don't forget to create a pull-request with all those changes - and be sure to earn the glory!

