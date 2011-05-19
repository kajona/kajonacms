<?php

require_once (dirname(__FILE__)."/../system/class_testbase.php");
require_once (dirname(__FILE__)."/../system/interface_versionable.php");

/**
 * Tests a few aspects of the changelog-component
 *
 * @author sidler@mulchprod.de
 * @package modul_system
 */
class test_systemchangelogTest extends class_testbase {

    public function testChangelog() {
        
//        $objSetting = class_modul_system_setting::getConfigByName("_system_changehistory_enabled_");
//        $strOldValue = $objSetting->getStrValue();
//        
//        $objSetting->setStrValue("true");
//        $objSetting->updateObjectToDb();
        
        class_carrier::getInstance()->getObjDB()->flushQueryCache();
        

        $objSystemCommon = new class_modul_system_common();

        $strSystemid = $objSystemCommon->createSystemRecord($objSystemCommon->getModuleSystemid("system"), "autotest dummy record");

        $objChanges = new class_modul_system_changelog();

        $objChanges->createLogEntry(new dummyObject($strSystemid), "1");
        $this->assertEquals(1, class_modul_system_changelog::getLogEntriesCount($strSystemid));

        class_carrier::getInstance()->getObjDB()->flushQueryCache();

        $objChanges->createLogEntry(new dummyObject($strSystemid), "2");
        $this->assertEquals(1, class_modul_system_changelog::getLogEntriesCount($strSystemid));

        class_carrier::getInstance()->getObjDB()->flushQueryCache();

        $objChanges->createLogEntry(new dummyObject($strSystemid), "2", true);
        $this->assertEquals(2, class_modul_system_changelog::getLogEntriesCount($strSystemid));

        $objSystemCommon->deleteSystemRecord($strSystemid);
        
        
//        $objSetting->setStrValue($strOldValue);
//        $objSetting->updateObjectToDb();

    }
}


class dummyObject implements interface_versionable {
    private $strSystemid;
    function __construct($strSystemid) {
        $this->strSystemid = $strSystemid;
    }

    public function getSystemid() {
        return $this->strSystemid;
    }
    
    public function getPrevid() {
        return "";
    }

    public function setStrSystemid($strSystemid) {
        $this->strSystemid = $strSystemid;
    }

    public function renderValue($strProperty, $strValue) {
        return $strValue;
    }


    public function getActionName($strAction) {
        return "dummy";
    }

    public function getChangedFields($strAction) {
        if($strAction == "1")
            return array(array("property" => "test", "oldvalue" => "0", "newvalue" => "1"));

        if($strAction == "2")
            return array(array("property" => "test", "oldvalue" => "3", "newvalue" => "3"));
    }

    public function getClassname() {
        return __CLASS__;
    }

    public function getModuleName() {
        return "dummy";
    }

    public function getPropertyName($strProperty) {
        return $strProperty;
    }

    public function getRecordName() {
        return "dummy";
    }

}
?>
