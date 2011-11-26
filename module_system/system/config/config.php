<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                  *
********************************************************************************************************/

/*
    NOTE:

    Since Kajona 2.1 it's possible to define all needed values using the installer.
    This file allows to specify different server-configs via the server hostname.
    !!!!!! Make sure that the live-server uses the default-section of this file !!!!!!
*/

switch($_SERVER['SERVER_NAME']) {

//--Server1----------------------------------------------------------------------------------------------
case "testpage.mulchprod.intern":
case "aquarium":
case "mango":
case "vserver":
    $config = array();

    //Database-Access
    $config['dbhost']               = "localhost";                             //Server name
    $config['dbusername']           = "kajona_v4";                                //Username
    $config['dbpassword']           = "kajona_v4";                                //Password
    $config['dbname']               = "kajona_v4";                                //Database name
    $config['dbdriver']             = "mysqli";                                //DB-Driver, one of:  mysqli, postgres, sqlite3, oci8
    $config['dbprefix']             = "kajona_";                               //table-prefix
    $config['dbport']               = "";                                      //Database port, default: ""

    break;


//--Standard---------------------------------------------------------------------------------------------
default:
    $config = array();

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



/*
    NOTE:

    Change the properties defined below only, if you now what you do!
    In most cases, those should be left as they are.
    Incorrect values could make the system unusable!
*/

//--common settings -------------------------------------------------------------------------------------

    $config['diradmin']             = "/admin";                                //Path containing the admin-classes
    $config['dirportal']            = "/portal";                               //Path containing the portal-classes
    $config['dirtemplates']         = "/templates";                            //Path containing the templates
    $config['dirsystem']            = "/system";                               //Path containing the system classes
    $config['dirlang']              = "/lang";                                 //Path containing the language-files
    $config['dirskins']             = "/skins";                                //Path containing the skin-files
    $config['dirproject']           = "/project";                              //Path containing the project-files

    $config["images_cachepath"]     = "/portal/pics/cache/";                   //Path used to store the cached and manipulated images

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

//--system settings--------------------------------------------------------------------------------------

    $debug = array();

    //Debug options
    $debug['time']                  = false;                                   //Calculates the time needed to create the requested page
    $debug['dbnumber']              = false;                                   //Counts the number of queries passed to the db / retrieved from the cache
    $debug['templatenr']            = false;                                   //Counts the number of templates retrieved from the cache
    $debug['memory']                = false;                                   //Displays the memory used by Kajona to generate the current page
    $debug['cache']                 = false;                                   //Counts the internal cache-hits and requests to save s.th. to the cache

    $debug['dblog']                 = false;                                   //Logs all queries sent to the db into a logfile

    $debug['debuglevel']            = 1;                                       //Current level of debugging. There are several states:
                                                                                   // 0: fatal errors will be displayed
                                                                                   // 1: fatal and regular errors will be displayed
    $debug['debuglogging']          = 2;                                       //Configures the logging-engine:
                                                                                   // 0: Nothing is logged to file
                                                                                   // 1: Errors are logged
                                                                                   // 2: Errors and warning
                                                                                   // 3: Errors, warnings and information are logged
?>