<?php
/*"******************************************************************************************************
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_carrier.php 4059 2011-08-09 14:52:41Z sidler $                                            *
********************************************************************************************************/

/**
 * Classloader for all Kajona classes.
 * @package modul_system
 */
class class_classloader {

    /**
     * The classloader itself. Scans the folders for the required class based on
     * the passed classname.
     * @param $strClassName
     * @return void
     */
    public function loadClass($strClassName) {

        //---ADMIN CLASSES-------------------------------------------------------------------------------
        //adminwidgets
        if(preg_match("/(class|interface)_adminwidget(.*)/", $strClassName)) {
            if(require(_adminpath_."/widgets/".$strClassName.".php"))
                return;
        }

        //systemtasks
        if(preg_match("/(class|interface)(.*)systemtask(.*)/", $strClassName)) {
            if(require(_adminpath_."/systemtasks/".$strClassName.".php"))
                return;
        }

        //statsreports
        if(preg_match("/(class)_(.*)stats_report(.*)/", $strClassName)) {
            if(require(_adminpath_."/statsreports/".$strClassName.".php"))
                return;
        }

        //admin classes
        //TODO: wtf? why strpos needed? whats wrong with that regex?
        if(preg_match("/(class|interface)_(.*)admin(_xml)?/", $strClassName) && !strpos($strClassName, "adminwidget")) {
            if(require(_adminpath_."/".$strClassName.".php"))
                return;
        }


        //---PORTAL CLASSES------------------------------------------------------------------------------

        //search plugins
        if(preg_match("/interface_search(.*)/", $strClassName)) {
            if(require(_portalpath_."/searchplugins/".$strClassName.".php"))
                return;
        }

        //portal classes
        if(preg_match("/(class|interface)_(.*)portal(.*)/", $strClassName)) {
            if(require(_portalpath_."/".$strClassName.".php"))
                return;
        }

        //---SYSTEM CLASSES------------------------------------------------------------------------------
        //db-drivers
        if(preg_match("/(class|interface)_db_(.*)/", $strClassName)) {
            if(require(_systempath_."/db/".$strClassName.".php"))
                return;
        }

        //usersources
        if(preg_match("/(class|interface)_usersources_(.*)/", $strClassName)) {
            if(require(_systempath_."/usersources/".$strClassName.".php"))
                return;
        }

        //workflows
        if(preg_match("/class_workflow_(.*)/", $strClassName)) {
            if(require(_systempath_."/workflows/".$strClassName.".php"))
                return;
        }

        //system-classes
        if(preg_match("/(class|interface)_(.*)/", $strClassName)) {
            if(require(_systempath_."/".$strClassName.".php"))
                return;
        }

    }


}
?>