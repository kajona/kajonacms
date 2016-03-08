<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                  *
********************************************************************************************************/

/*
    PLEASE READ:

    There's no need to change anything in this file.
    All values and settings may be overridden by placing them in the projects' config-file at

    /project/system/config/config.php

    A minimal config-file will be created during the installation of the system.


*/


//--database access -------------------------------------------------------------------------------------


$config['dbhost']               = "%%defaulthost%%";               //Server name
$config['dbusername']           = "%%defaultusername%%";           //Username
$config['dbpassword']           = "%%defaultpassword%%";           //Password
$config['dbname']               = "%%defaultdbname%%";             //Database name
$config['dbdriver']             = "%%defaultdriver%%";             //DB-Driver, one of: mysqli, postgres, sqlite3, oci8
$config['dbprefix']             = "%%defaultprefix%%";             //table-prefix
$config['dbport']               = "%%defaultport%%";               //Database port, default: ""




//--common settings -------------------------------------------------------------------------------------

    $config['dirtemplates']         = "/templates";                    //Path containing the templates
    $config['dirlang']              = "/lang";                         //Path containing the language-files
    $config['dirproject']           = "/project";                      //Path containing the project-files
    $config['dirfiles']             = "/files";                        //Path containing the files-directory

    $config["images_cachepath"]     = "/files/cache/";                 //Path used to store the cached and manipulated images

    $config['adminlangs']           = "de,en,pt,ru,bg,sv";             //Available languages for the administration

    $config['admintoolkit']         = "ToolkitAdmin";                  //The admin-toolkit class to use. If you created your own implementation,
                                                                       //e.g. by extending the Kajona-class, set the name of the class here.

    $config['https_header']         = "HTTPS";                         //Http-header used to validate if the current connection is encrypted by https.
                                                                       //If your application server uses another value, set it here

    $config['https_header_value']   = "on";                            //If the presence of the header is not enough to validate the https status,
                                                                       //set the required value to compare against here

    $config['loginproviders']       = "kajona";                        //A chain of login-providers, each implementing a single usersource. The providers
                                                                       //are queried in the order of appearance. The list is comma-separated, no blanks allowed.

//--caching ---------------------------------------------------------------------------------------------

//TODO: will be replaced by the CacheManager
    $config['textcachetime']        = 10;                              //Number of seconds language-files are cached. Cached entries are shared between sessions. Reduce this amount during
                                                                       //development (probably changing the lang-files a lot) and set it to a high value as soon as the website is in
                                                                       //production. Requires APC. Attention: 0 = infinite!

//TODO: will be replaced by the CacheManager
    $config['templatecachetime']    = 10;                              //Number of seconds templates are cached. Cached entries are shared between sessions. Reduce this amount during
                                                                       //development (probably changing the templates a lot) and set it to a high value as soon as the website is in
                                                                       //production. Requires APC. Attention: 0 = infinite!


    $config['bootstrapcache_pharsums']       = true;                   //Enables the detection of phar-changes in order to redeploy the static contents. Should be disabled for non-phar installations only.
                                                                       //The cache is created under /project/temp/cache

    $config['bootstrapcache_pharcontent']    = true;                   //Enables the caching of phar-contents. Should be enabled by default.
                                                                       //The cache is created under /project/temp/cache

    $config['bootstrapcache_objects']        = true;                   //Caches the mapping of systemid to class-names. Should be enabled by default.
                                                                       //The cache is created under /project/temp/cache

    $config['bootstrapcache_foldercontent']  = true;                   //Caches the merge of the core- and project folders. Should be enabled on production systems but disabled on development systems.
                                                                       //The cache is created under /project/temp/cache

    $config['bootstrapcache_reflection']     = true;                   //Caches all static analysis by the reflection API, e.g. parsing of annotations. Should be enabled on production systems but disabled on development systems.
                                                                       //The cache is created under /project/temp/cache

    $config['bootstrapcache_lang']           = true;                   //Caches all locations of language files. Should be enabled on production systems but disabled on development systems.
                                                                       //The cache is created under /project/temp/cache

    $config['bootstrapcache_modules']        = true;                   //Caches the list of locally installed modules. Should be enabled on production systems but disabled on development systems.
                                                                       //The cache is created under /project/temp/cache

    $config['bootstrapcache_pharmodules']    = true;                   //Caches the list of modules deployed as phars. Should be enabled on production systems but disabled on development systems.
                                                                       //The cache is created under /project/temp/cache

    $config['bootstrapcache_classes']        = true;                   //Caches the locations of class-definitions collected by the classloader. Should be enabled on production systems but disabled on development systems.
                                                                       //The cache is created under /project/temp/cache

    $config['bootstrapcache_templates']      = true;                   //Caches the locations of templates fetched by the template-engine. Should be enabled on production systems but disabled on development systems.
                                                                       //The cache is created under /project/temp/cache


//--system settings -------------------------------------------------------------------------------------

    //Debug options
    $debug['time']                  = false;                           //Calculates the time needed to create the requested page
    $debug['dbnumber']              = false;                           //Counts the number of queries passed to the db / retrieved from the cache
    $debug['templatenr']            = false;                           //Counts the number of templates retrieved from the cache
    $debug['memory']                = false;                           //Displays the memory used by Kajona to generate the current page

    $debug['dblog']                 = false;                           //Logs all queries sent to the db into a logfile. If set to true, the
                                                                       //debuglogging has to be set to 3, since queries are leveled as information

    $debug['debuglevel']            = 0;                               //Current level of debugging. There are several states:
                                                                           // 0: fatal errors will be displayed
                                                                           // 1: fatal and regular errors will be displayed
    $debug['debuglogging']          = 2;                               //Configures the logging-engine:
                                                                           // 0: Nothing is logged to file
                                                                           // 1: Errors are logged
                                                                           // 2: Errors and warning
                                                                           // 3: Errors, warnings and information are logged


