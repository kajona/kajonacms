<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                  *
********************************************************************************************************/

/*
    PLEASE READ:

    There's no need to change anything in this file.
    All values and settings may be overridden by placing them in the projects' config-file at

    /project/system/conig.php

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

    $config['cache_texts']          = false;                                   //caches read lang files with the users' session. Enable only on productive
                                                                               //environments. Could consume up a lot of ram.

    $config['loginproviders']       = "kajona";                                //A chain of login-providers, each implementing a single usersource. The providers
                                                                               //are queried in the order of appearance. The list is comma-separated, no blanks allowed.

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

switch($_SERVER['SERVER_NAME']) {

    case "aquarium":
    case "terrarium":
    case "mango":

        //Database-Access
        $config['dbhost']               = "localhost";                             //Server name
        $config['dbusername']           = "kajona_v4";                             //Username
        $config['dbpassword']           = "kajona_v4";                             //Password
        $config['dbname']               = "kajona_v4";                             //Database name
        $config['dbdriver']             = "mysqli";                                //DB-Driver, one of:  mysqli, postgres, sqlite3, oci8
        $config['dbprefix']             = "kajona_";                               //table-prefix
        $config['dbport']               = "";                                      //Database port, default: ""

        break;


    default:

        //Database-Access
        $config['dbhost']               = "%%defaulthost%%";                       //Server name
        $config['dbusername']           = "%%defaultusername%%";                   //Username
        $config['dbpassword']           = "%%defaultpassword%%";                   //Password
        $config['dbname']               = "%%defaultdbname%%";                     //Database name
        $config['dbdriver']             = "%%defaultdriver%%";                     //DB-Driver, one of: mysqli, postgres, sqlite3, oci8
        $config['dbprefix']             = "%%defaultprefix%%";                     //table-prefix
        $config['dbport']               = "%%defaultport%%";                       //Database port, default: ""

        break;

}