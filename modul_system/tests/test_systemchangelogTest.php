<?php

require_once (dirname(__FILE__)."/../system/class_testbase.php");

/**
 * Tests a few aspects of the changelog-component
 *
 * @author sidler
 * @package modul_system
 */
class test_systemchangelogTest extends class_testbase {

    public function testChangelog() {

        $objSystemCommon = new class_modul_system_common();

        $strSystemid = $objSystemCommon->createSystemRecord($objSystemCommon->getModuleSystemid("system"), "autotest dummy record");

        $objChanges = new class_modul_system_changelog();

        $objChanges->createLogEntry("autotest", "test", $strSystemid, "autotest", "0", "1");
        $this->assertEquals(1, class_modul_system_changelog::getLogEntriesCount($strSystemid));

        class_carrier::getInstance()->getObjDB()->flushQueryCache();

        $objChanges->createLogEntry("autotest", "test", $strSystemid, "autotest", "3", "3");
        $this->assertEquals(1, class_modul_system_changelog::getLogEntriesCount($strSystemid));

        class_carrier::getInstance()->getObjDB()->flushQueryCache();

        $objChanges->createLogEntry("autotest", "test", $strSystemid, "autotest", "3", "3", true);
        $this->assertEquals(2, class_modul_system_changelog::getLogEntriesCount($strSystemid));

        $objSystemCommon->deleteSystemRecord($strSystemid);

    }
}
?>
