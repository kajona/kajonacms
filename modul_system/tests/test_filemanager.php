<?php

include_once(_systempath_."/class_modul_filemanager_repo.php");

class class_test_filemanager implements interface_testable {



    public function test() {
        
        $objDB = class_carrier::getInstance()->getObjDB();

        echo "\tmodul_filemanager...\n";

        echo "\tcreating 100 filemanager repos...\n";
        $intNrOfRepos = count(class_modul_filemanager_repo::getAllRepos());
        $arrRepoIDs = array();
        for($intI = 0; $intI <= 100; $intI++) {
            $objRepo = new class_modul_filemanager_repo();
            $objRepo->setStrPath("/portal/pics");
            $strName = "Repo_".generateSystemid();
            $objRepo->setStrName($strName);
            $objRepo->saveObjectToDb();
            $strRepoID = $objRepo->getSystemid();
            $arrRepoIDs[] = $strRepoID;
            $objRepo = new class_modul_filemanager_repo($strRepoID);
            class_assertions::assertEqual($objRepo->getStrName(), $strName, __FILE__." checkCreateRepo");
            $objDB->flushQueryCache();
        }
        class_assertions::assertEqual(count(class_modul_filemanager_repo::getAllRepos()), $intNrOfRepos+$intI, __FILE__." checkNrOfReposCreated");

        echo "\tdeleting repos created...\n";
        foreach ($arrRepoIDs as $strOneRepo) {
            $objRepo = new class_modul_filemanager_repo($strOneRepo);
            $objRepo->deleteRepo();
            $objDB->flushQueryCache();
        }
        class_assertions::assertEqual(count(class_modul_filemanager_repo::getAllRepos()), $intNrOfRepos, __FILE__." checkNrOfReposDeleted");
    }

}

?>