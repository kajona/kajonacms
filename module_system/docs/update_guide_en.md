#Kajona update guide

Kajona is updated regularly to provide both, patches and bugfixes to released versions and new, feature-packed releases.

The availability of updates can be checked using the integrated package management, module `packagemanagement`. 

When it comes to updating an installation, there are two general approaches:

- Minor updates (`x.x.x -> x.x.y`) are handled using the administration backend directly
- Major updates (`x.x -> x.y` or `x.x -> y.y`) are handled by a special upgrade script

While updates are tested on our site, there's always the chance of something going nuts. Either due to untested system-configurations, 
third-party components or the weather.
Therefore every updates starts by backing up the installation

##1. Backup the installation

- Create a backup of the database, either by using your own tools, or by using the system task: `Module System -> System-Tasks -> Backup Database`
- Create a backup if the whole installation, so the filesystem. Use your (S)FTP client of choice, a bash-command or whatever you prefer

##2. Chose the installation method

Differ between minor- and major updates:

If you want to update just a single package, e.g. since a new minor-release is available, use the internal package management.
It may be used for updates from 5.0 to 5.0.1 or 5.1.1 to 5.1.4 - so anything past the first two numbers.

If you want to install a new release or a bunch of packages, use the major upgrade script.

> Heads ups! Make sure to have a valid backup before updating - just in case :)


##3. Minor Updates
In order to search, download and install new packages make use of the module `Packagemanagement -> Installed packages`. The system will look for
new versions automatically. If given, a click on the download-button starts the update sequence.

##4. Major Updates
Major updates, so bumps on the first or second release version number (e.g. `5.0 -> 5.1` or `5.1 -> 6.0`) often introduce fundamental changes
to the underlying framework.

Therefore updates using the administration backend may fail in some cases since the backend only updates a single package at once. 
Normally, major updates are handled by downloading new version of all installed modules and calling the installer afterwards.

In order to automate and simplify this process, we created a simple upgrade-script available on GitHub at
https://github.com/kajona/systemupgrade/blob/master/upgrade.php

Download the file directly from https://raw.githubusercontent.com/kajona/systemupgrade/master/upgrade.php` and save it as `upgrade.php` in the root-
directory of your Kajona installation, so on the same level as `index.php` and `download.php`.
Call the script using your browser of choice, e.g. `http://www.mykajona.domain/upgrade.php`. The script will perform the following tasks:

- Scan the `/core` directory of your installations for phar-based modules
- Compare the versions of the locally installed ones with the versions in our package-repository
- Download the new versions of modules to `/project/temp` , if available
- Move the new modules to your `/core` directory
- Create a `installer.php` file on the root-level of your installation
- Redirect to the installer in order to perform the schema upgrades

That's it, the installer will guide you through the remaining update steps

> Heads up! Make sure to delete the `installer.php` and `upgrade.php` files after finishing the update! 


