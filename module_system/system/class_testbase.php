<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                             *
********************************************************************************************************/


require_once(__DIR__ . "../../../bootstrap.php");


/**
 * The class_testbase is the common baseclass for all testcases.
 * Triggers the methods required to run proper PHPUnit tests such as starting the system-kernel
 *
 * @package module_system
 * @since 3.4
 * @author sidler@mulchprod.de
 */
class class_testbase extends PHPUnit_Framework_TestCase {

    protected function setUp() {

        if(!defined("_block_config_db_loading_")) {
            define("_block_config_db_loading_", true);
        }

        if(!defined("_autotesting_")) {
            define("_autotesting_", true);
        }

        $objCarrier = class_carrier::getInstance();

        $strSQL = "UPDATE "._dbprefix_."system_config SET system_config_value = 'true'
                    WHERE system_config_name = '_system_changehistory_enabled_'";

        $objCarrier->getObjDB()->_query($strSQL);
        $objCarrier->getObjDB()->flushQueryCache();

        class_config::getInstance()->loadConfigsDatabase(class_db::getInstance());
    }

    /**
     * For the sake of phpunit
     */
    public function testTest() {

    }

    protected function flushDBCache() {
        return class_carrier::getInstance()->getObjDB()->flushQueryCache();
    }

}

