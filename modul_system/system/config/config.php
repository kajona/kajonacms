<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
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
    $config['dbusername']           = "kajona";                                //Username
    $config['dbpassword']           = "kajona";                                //Password
    $config['dbname']               = "kajona";                                //Database name
    $config['dbdriver']             = "mysqli";                                //DB-Driver, one of: mysql, mysqli, postgres, sqlite
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
    $config['dbdriver']             = "%%defaultdriver%%";                     //DB-Driver, one of: mysql, mysqli, postgres, sqlite
    $config['dbprefix']             = "%%defaultprefix%%";                     //table-prefix
    $config['dbport']               = "%%defaultport%%";                       //Database port, default: ""

    break;

}



/*
    NOTE:

    Change the properties defined below only, if you now what you do!
    In most cases, those should be left as they are.
    Incorrect values could make the system unuseable!
*/

//--common settings -------------------------------------------------------------------------------------

    $config['diradmin']             = "/admin";                                //Path containing the admin-classes
    $config['dirportal']            = "/portal";                               //Path containing the portal-classes
    $config['dirtemplates']         = "/templates";                            //Path containing the templates
    $config['dirsystem']            = "/system";                               //Path containing the system classes
    $config['dirlang']              = "/lang";                                 //Path containing the language-files
    $config['dirskins']             = "/skins";                                //Path containing the skin-files

    $config["images_cachepath"]     = "/portal/pics/cache/";                   //Path used to store the cached and manipulated images

    $config['adminlangs']           = "de,en,pt,ru,bg";                        //Available languages for the administration



//--system settings--------------------------------------------------------------------------------------

    $debug = array();

    //Debug options
    $debug['time']                  = false;                                   //Calculates the time needed to create the requested page
    $debug['dbnumber']              = false;                                   //Counts the number of queries passed to the db / retrieved from the cache
    $debug['templatenr']            = false;                                   //Counts the number of templates retrieved from the cache
    $debug['memory']                = false;                                   //Displays the memory used by kajona to generate the current page

    $debug['dblog']                 = false;                                   //Logs all queries sent to the db into a logfile

    $debug['debuglevel']            = 0;                                       //Current level of debugging. There are several states:
                                                                                   // 0: fatal errors will be displayed
                                                                                   // 1: fatal and regular errors will be displayed
    $debug['debuglogging']          = 2;                                       //Configures the logging-engine:
                                                                                   // 0: Nothing is logged to file
                                                                                   // 1: Errors are logged
                                                                                   // 2: Errors and warning
                                                                                   // 3: Errors, warnings and infos are logged
?>