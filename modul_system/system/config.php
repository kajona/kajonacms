<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   config.php                                                                                          *
*   Used to define global properties                                                                    *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                  *
********************************************************************************************************/

/*
    NOTE:

    Since Kajona 2.1 it's possible to define all needed values using the installer!

    This file allows to specify different server-configs.

    !!!!!! Make sure that the live-server uses the default-section of this file !!!!!!!

    The following database-drivers could be used with Kajona:

    mysql, mysqli, postgres


*/

switch($_SERVER['SERVER_NAME']) {

//--Server1----------------------------------------------------------------------------------------------
case "testpage.mulchprod.intern":
case "aquarium":
case "mango":
    $config = array();

    //Database-Access
    $config['dbhost']               = "localhost";                             //Servername
    $config['dbusername']           = "kajona";                                //Username
    $config['dbpassword']           = "kajona";                                //Password
    $config['dbname']               = "kajona";                                //Databasename
    $config['dbdriver']             = "mysqli";                                //DB-Driver, one of: mysql, mysqli
    $config['dbprefix']             = "kajona_";                               //table-prefix
    $config['dbport']               = "";                                      //Databaseport, default: ""

    break;


//--Standard---------------------------------------------------------------------------------------------
default:
    $config = array();

    //Database-Access
    $config['dbhost']               = "%%defaulthost%%";                       //Servername
    $config['dbusername']           = "%%defaultusername%%";                   //Username
    $config['dbpassword']           = "%%defaultpassword%%";                   //Password
    $config['dbname']               = "%%defaultdbname%%";                     //Databasename
    $config['dbdriver']             = "%%defaultdriver%%";                     //DB-Driver, one of: mysql, mysqli
    $config['dbprefix']             = "%%defaultprefix%%";                     //table-prefix
    $config['dbport']               = "%%defaultport%%";                       //Databaseport, default: ""

    break;

}



/*
    NOTE:

    Change the properties defined below only, if you now what you do!
    In most cases, those could be left "as-is".
    Incorrect values could make the system unuseable!

*/

//--common settings -------------------------------------------------------------------------------------

    $config['diradmin']             = "/admin";	                               //Path containing the admin-classes
    $config['dirportal']            = "/portal";                               //Path containing the portal-classes
    $config['dirtemplates']         = "/templates";                            //Path containing the templates
    $config['dirsystem']            = "/system";                               //Path containing the system classes
    $config['dirtexte']             = "/texte";	                               //Path containing the text-files
    $config['dirskins']             = "/skins";                                //Path containing the skin-files

    $config['adminlangs']           = "de,en";                                 //Available languages for the administration

    $config['portallanguage']       = "de";                                    //This is the default language for texts being loaded in the portal.
                                                                               //Please note: This setting is only used, if no languages are installed.


//--system settings--------------------------------------------------------------------------------------


    $debug = array();

    //Debug Optionen
    $debug['time']                  = false;                                   //Calculates the time needed to create the requested page
    $debug['dbnumber']              = false;                                   //Counts the number of queries passed to the db / retrieved from the cache
    $debug['templatenr']            = false;                                   //Counts the number of templates retrieved from the cache
    $debug['memory']                = false;                                   //Displays the memory used by kajona to generate the current page

    $debug['dblog']                 = false;                                   //Logs all queries sent to the db into a logfile

    $debug['debuglevel']            = 0;                                       //Current level of debugging. There are several states:
                                                                                   // 0: fatal errors will be displayed
                                                                                   // 1: fatal and regular errors will be displayed
    $debug['debuglogging']          = 1;                                       //Configures the logging-engine:
                                                                                   // 0: Nothing is logged to file
                                                                                   // 1: Errors are logged
                                                                                   // 2: Errors and warning
                                                                                   // 3: Errors, warnings and infos are logged
?>