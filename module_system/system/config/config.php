<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                  *
********************************************************************************************************/

/*
    PLEASE READ:

    There's no need to change anything in this file.
    All values and settings may be overridden by placing them in the projects' config-file at

    /project/system/config.php

    A minimal config-file will be created during the installation of the system.


*/


//--common settings -------------------------------------------------------------------------------------

    $config['dirtemplates']         = "/templates";                            //Path containing the templates
    $config['dirlang']              = "/lang";                                 //Path containing the language-files
    $config['dirproject']           = "/project";                              //Path containing the project-files
    $config['dirfiles']             = "/files";                                //Path containing the files-directory

    $config["images_cachepath"]     = "/files/cache/";                         //Path used to store the cached and manipulated images

    $config['adminlangs']           = "de,en,pt,ru,bg,sv";                     //Available languages for the administration

    $config['admintoolkit']         = "class_toolkit_admin";                   //The admin-toolkit class to use. If you created your own implementation,
                                                                               //e.g. by extending the Kajona-class, set the name of the class here.

    $config['https_header']         = "HTTPS";                                 //Http-header used to validate if the current connection is encrypted by https.
                                                                               //If your application server uses another value, set it here

    $config['https_header_value']   = "on";                                    //If the presence of the header is not enough to validate the https status,
                                                                               //set the required value to compare against here

    $config['textcachetime']          = 10;                                    //Number of seconds language-files are cached. Cached entries are shared between sessions. Reduce this amount during
                                                                               //development (probably changing the lang-files a lot) and set it to a high value as soon as the website is in
                                                                               //production. Requires APC. Attention: 0 = infinite!

    $config['loginproviders']       = "kajona";                                //A chain of login-providers, each implementing a single usersource. The providers
                                                                               //are queried in the order of appearance. The list is comma-separated, no blanks allowed.


    $config['templatecachetime']    = 10;                                      //Number of seconds templates are cached. Cached entries are shared between sessions. Reduce this amount during
                                                                               //development (probably changing the templates a lot) and set it to a high value as soon as the website is in
                                                                               //production. Requires APC. Attention: 0 = infinite!

    $config['resourcecaching']      = false;                                   //If enabled, the resource- and class-loader save their meta-information to the filesystem and the APC cache. The cache-files will
                                                                               //be stored under /project/temp and may be deleted without consequences. The folder needs to be writable.
                                                                               //Disable caching during development (and remove the cache-files), enable on production sites.

//--system settings -------------------------------------------------------------------------------------

    //Debug options
    $debug['time']                  = false;                                   //Calculates the time needed to create the requested page
    $debug['dbnumber']              = false;                                   //Counts the number of queries passed to the db / retrieved from the cache
    $debug['templatenr']            = false;                                   //Counts the number of templates retrieved from the cache
    $debug['memory']                = false;                                   //Displays the memory used by Kajona to generate the current page
    $debug['cache']                 = false;                                   //Counts the internal cache-hits and requests to save s.th. to the cache

    $debug['dblog']                 = false;                                   //Logs all queries sent to the db into a logfile. If set to true, the
                                                                               //debuglogging has to be set to 3, since queries are leveled as information

    $debug['debuglevel']            = 0;                                       //Current level of debugging. There are several states:
                                                                                   // 0: fatal errors will be displayed
                                                                                   // 1: fatal and regular errors will be displayed
    $debug['debuglogging']          = 2;                                       //Configures the logging-engine:
                                                                                   // 0: Nothing is logged to file
                                                                                   // 1: Errors are logged
                                                                                   // 2: Errors and warning
                                                                                   // 3: Errors, warnings and information are logged


//--database access -------------------------------------------------------------------------------------


    $config['dbhost']               = "%%defaulthost%%";                       //Server name
    $config['dbusername']           = "%%defaultusername%%";                   //Username
    $config['dbpassword']           = "%%defaultpassword%%";                   //Password
    $config['dbname']               = "%%defaultdbname%%";                     //Database name
    $config['dbdriver']             = "%%defaultdriver%%";                     //DB-Driver, one of: mysqli, postgres, sqlite3, oci8
    $config['dbprefix']             = "%%defaultprefix%%";                     //table-prefix
    $config['dbport']               = "%%defaultport%%";                       //Database port, default: ""

