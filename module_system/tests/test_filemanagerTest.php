<?php

require_once (dirname(__FILE__)."/../../module_system/system/class_testbase.php");

class class_test_filemanager extends class_testbase  {

    public function test() {

        $objDB = class_carrier::getInstance()->getObjDB();

        echo "\tmodul_filemanager...\n";

        echo "\tcreating 100 filemanager repos...\n";
        $intNrOfRepos = count(class_module_filemanager_repo::getAllRepos());
        $arrRepoIDs = array();
        for($intI = 0; $intI <= 100; $intI++) {
            $objRepo = new class_module_filemanager_repo();
            $objRepo->setStrPath("/portal/pics");
            $strName = "Repo_".generateSystemid();
            $objRepo->setStrName($strName);
            $objRepo->updateObjectToDb();
            $strRepoID = $objRepo->getSystemid();
            $arrRepoIDs[] = $strRepoID;
            $objRepo = new class_module_filemanager_repo($strRepoID);
            $this->assertEquals($objRepo->getStrName(), $strName, __FILE__." checkCreateRepo");
            $objDB->flushQueryCache();
        }
        $this->assertEquals(count(class_module_filemanager_repo::getAllRepos()), $intNrOfRepos+$intI, __FILE__." checkNrOfReposCreated");

        echo "\tdeleting repos created...\n";
        foreach ($arrRepoIDs as $strOneRepo) {
            echo "\t  deleting repo with id ".$strOneRepo."...\n";
            $objRepo = new class_module_filemanager_repo($strOneRepo);
            $objRepo->deleteObject();
            $objDB->flushQueryCache();

        }
        $this->assertEquals(count(class_module_filemanager_repo::getAllRepos()), $intNrOfRepos, __FILE__." checkNrOfReposDeleted");
    }

}

