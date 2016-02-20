<?php
namespace Kajona\Pages\Tests;

use Kajona\Pages\System\PagesFolder;
use Kajona\System\System\Testbase;

require_once __DIR__."/../../../core/module_system/system/Testbase.php";

class SortTest extends Testbase
{


    public function testSorting()
    {
        echo "testing sorting-behaviour....\n";


        $objRootPage = new \Kajona\Pages\System\PagesPage();

        $objRootPage->setStrName("test1");
        $objRootPage->updateObjectToDb();


        for ($intI = 1; $intI <= 10; $intI++) {
            $objPage = new \Kajona\Pages\System\PagesPage();
            $objPage->setStrName("sortsubpage_".$intI);
            $objPage->updateObjectToDb($objRootPage->getSystemid());
        }


        //check initial sort
        $arrPages = PagesFolder::getPagesAndFolderList($objRootPage->getSystemid());
        for ($intI = 1; $intI <= count($arrPages); $intI++) {
            $objPage = $arrPages[$intI - 1];
            $this->assertEquals($objPage->getStrName(), "sortsubpage_".$intI);
        }


        //shift record 7 to pos 1
        $objPage = $arrPages[6];
        $objPage->setAbsolutePosition(1);
        //new key:   0, 1, 2, 3, 4, 5, 6, 7, 8, 9
        //new order: 7, 1, 2, 3, 4, 5, 6, 8, 9, 10
        $arrPages = PagesFolder::getPagesAndFolderList($objRootPage->getSystemid());
        $objPage = $arrPages[0];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_7");
        $this->assertEquals(1, $objPage->getIntSort());
        $objPage = $arrPages[1];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_1");
        $this->assertEquals(2, $objPage->getIntSort());
        $objPage = $arrPages[2];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_2");
        $this->assertEquals(3, $objPage->getIntSort());
        $objPage = $arrPages[3];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_3");
        $this->assertEquals(4, $objPage->getIntSort());
        $objPage = $arrPages[4];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_4");
        $this->assertEquals(5, $objPage->getIntSort());
        $objPage = $arrPages[5];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_5");
        $this->assertEquals(6, $objPage->getIntSort());
        $objPage = $arrPages[6];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_6");
        $this->assertEquals(7, $objPage->getIntSort());
        $objPage = $arrPages[7];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_8");
        $this->assertEquals(8, $objPage->getIntSort());
        $objPage = $arrPages[8];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_9");
        $this->assertEquals(9, $objPage->getIntSort());
        $objPage = $arrPages[9];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_10");
        $this->assertEquals(10, $objPage->getIntSort());


        //shift record 3 to pos 8
        $objPage = $arrPages[2];
        $objPage->setAbsolutePosition(8);
        //old order: 7, 1, 2, 3, 4, 5, 6, 8, 9, 10
        //logical    1, 2, 3, 4, 5, 6, 7, 8, 9, 10
        //new key:   0, 1, 2, 3, 4, 5, 6, 7, 8, 9
        //new order: 7, 1, 3, 4, 5, 6, 8, 2, 9, 10
        $arrPages = PagesFolder::getPagesAndFolderList($objRootPage->getSystemid());
        $objPage = $arrPages[0];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_7");
        $this->assertEquals(1, $objPage->getIntSort());
        $objPage = $arrPages[1];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_1");
        $this->assertEquals(2, $objPage->getIntSort());
        $objPage = $arrPages[2];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_3");
        $this->assertEquals(3, $objPage->getIntSort());
        $objPage = $arrPages[3];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_4");
        $this->assertEquals(4, $objPage->getIntSort());
        $objPage = $arrPages[4];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_5");
        $this->assertEquals(5, $objPage->getIntSort());
        $objPage = $arrPages[5];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_6");
        $this->assertEquals(6, $objPage->getIntSort());
        $objPage = $arrPages[6];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_8");
        $this->assertEquals(7, $objPage->getIntSort());
        $objPage = $arrPages[7];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_2");
        $this->assertEquals(8, $objPage->getIntSort());
        $objPage = $arrPages[8];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_9");
        $this->assertEquals(9, $objPage->getIntSort());
        $objPage = $arrPages[9];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_10");
        $this->assertEquals(10, $objPage->getIntSort());


        //out of range shifts
        $objPage = $arrPages[2];
        $objPage->setAbsolutePosition(13);
        $arrPages = PagesFolder::getPagesAndFolderList($objRootPage->getSystemid());
        $objPage = $arrPages[0];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_7");
        $this->assertEquals(1, $objPage->getIntSort());
        $objPage = $arrPages[1];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_1");
        $this->assertEquals(2, $objPage->getIntSort());
        $objPage = $arrPages[2];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_3");
        $this->assertEquals(3, $objPage->getIntSort());
        $objPage = $arrPages[3];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_4");
        $this->assertEquals(4, $objPage->getIntSort());
        $objPage = $arrPages[4];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_5");
        $this->assertEquals(5, $objPage->getIntSort());
        $objPage = $arrPages[5];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_6");
        $this->assertEquals(6, $objPage->getIntSort());
        $objPage = $arrPages[6];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_8");
        $this->assertEquals(7, $objPage->getIntSort());
        $objPage = $arrPages[7];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_2");
        $this->assertEquals(8, $objPage->getIntSort());
        $objPage = $arrPages[8];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_9");
        $this->assertEquals(9, $objPage->getIntSort());
        $objPage = $arrPages[9];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_10");
        $this->assertEquals(10, $objPage->getIntSort());


        $objPage = $arrPages[2];
        $objPage->setAbsolutePosition(0);
        $arrPages = PagesFolder::getPagesAndFolderList($objRootPage->getSystemid());
        $objPage = $arrPages[0];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_7");
        $this->assertEquals(1, $objPage->getIntSort());
        $objPage = $arrPages[1];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_1");
        $this->assertEquals(2, $objPage->getIntSort());
        $objPage = $arrPages[2];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_3");
        $this->assertEquals(3, $objPage->getIntSort());
        $objPage = $arrPages[3];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_4");
        $this->assertEquals(4, $objPage->getIntSort());
        $objPage = $arrPages[4];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_5");
        $this->assertEquals(5, $objPage->getIntSort());
        $objPage = $arrPages[5];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_6");
        $this->assertEquals(6, $objPage->getIntSort());
        $objPage = $arrPages[6];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_8");
        $this->assertEquals(7, $objPage->getIntSort());
        $objPage = $arrPages[7];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_2");
        $this->assertEquals(8, $objPage->getIntSort());
        $objPage = $arrPages[8];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_9");
        $this->assertEquals(9, $objPage->getIntSort());
        $objPage = $arrPages[9];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_10");
        $this->assertEquals(10, $objPage->getIntSort());


        //border-shifts
        $objPage = $arrPages[1];
        $objPage->setAbsolutePosition(1);

        //logical    1, 2, 3, 4, 5, 6, 7, 8, 9, 10
        //new key:   0, 1, 2, 3, 4, 5, 6, 7, 8, 9
        //new order: 1, 7, 3, 4, 5, 6, 8, 2, 9, 10
        $arrPages = PagesFolder::getPagesAndFolderList($objRootPage->getSystemid());
        $objPage = $arrPages[0];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_1");
        $this->assertEquals(1, $objPage->getIntSort());
        $objPage = $arrPages[1];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_7");
        $this->assertEquals(2, $objPage->getIntSort());
        $objPage = $arrPages[2];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_3");
        $this->assertEquals(3, $objPage->getIntSort());
        $objPage = $arrPages[3];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_4");
        $this->assertEquals(4, $objPage->getIntSort());
        $objPage = $arrPages[4];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_5");
        $this->assertEquals(5, $objPage->getIntSort());
        $objPage = $arrPages[5];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_6");
        $this->assertEquals(6, $objPage->getIntSort());
        $objPage = $arrPages[6];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_8");
        $this->assertEquals(7, $objPage->getIntSort());
        $objPage = $arrPages[7];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_2");
        $this->assertEquals(8, $objPage->getIntSort());
        $objPage = $arrPages[8];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_9");
        $this->assertEquals(9, $objPage->getIntSort());
        $objPage = $arrPages[9];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_10");
        $this->assertEquals(10, $objPage->getIntSort());


        $objPage = $arrPages[8];
        $objPage->setAbsolutePosition(10);

        //logical    1, 2, 3, 4, 5, 6, 7, 8, 9, 10
        //new key:   0, 1, 2, 3, 4, 5, 6, 7, 8, 9
        //new order: 1, 7, 3, 4, 5, 6, 8, 2, 10, 9
        $arrPages = PagesFolder::getPagesAndFolderList($objRootPage->getSystemid());
        $objPage = $arrPages[0];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_1");
        $this->assertEquals(1, $objPage->getIntSort());
        $objPage = $arrPages[1];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_7");
        $this->assertEquals(2, $objPage->getIntSort());
        $objPage = $arrPages[2];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_3");
        $this->assertEquals(3, $objPage->getIntSort());
        $objPage = $arrPages[3];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_4");
        $this->assertEquals(4, $objPage->getIntSort());
        $objPage = $arrPages[4];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_5");
        $this->assertEquals(5, $objPage->getIntSort());
        $objPage = $arrPages[5];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_6");
        $this->assertEquals(6, $objPage->getIntSort());
        $objPage = $arrPages[6];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_8");
        $this->assertEquals(7, $objPage->getIntSort());
        $objPage = $arrPages[7];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_2");
        $this->assertEquals(8, $objPage->getIntSort());
        $objPage = $arrPages[8];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_10");
        $this->assertEquals(9, $objPage->getIntSort());
        $objPage = $arrPages[9];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_9");
        $this->assertEquals(10, $objPage->getIntSort());

        //delete a page
        //logical    1, 2, 3, 4, 5, 6, 7, 8, 9, 10
        //new key:   0, 1, 2, 3, 4, 5, 6, 7, 8, 9
        //new order: 1, 7, 3, 4, 5, 6, 8, 2, 10, 9

        $arrPages = PagesFolder::getPagesAndFolderList($objRootPage->getSystemid());
        $objPage = $arrPages[3];
        $objPage->deleteObjectFromDatabase();
        //logical    1, 2, 3, 4, 5, 6, 7, 8, 9
        //new key:   0, 1, 2, 3, 4, 5, 6, 7, 8
        //new order: 1, 7, 3, 5, 6, 8, 2, 10, 9
        $arrPages = PagesFolder::getPagesAndFolderList($objRootPage->getSystemid());

        $objPage = $arrPages[0];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_1");
        $this->assertEquals(1, $objPage->getIntSort());
        $objPage = $arrPages[1];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_7");
        $this->assertEquals(2, $objPage->getIntSort());
        $objPage = $arrPages[2];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_3");
        $this->assertEquals(3, $objPage->getIntSort());
        $objPage = $arrPages[3];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_5");
        $this->assertEquals(4, $objPage->getIntSort());
        $objPage = $arrPages[4];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_6");
        $this->assertEquals(5, $objPage->getIntSort());
        $objPage = $arrPages[5];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_8");
        $this->assertEquals(6, $objPage->getIntSort());
        $objPage = $arrPages[6];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_2");
        $this->assertEquals(7, $objPage->getIntSort());
        $objPage = $arrPages[7];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_10");
        $this->assertEquals(8, $objPage->getIntSort());
        $objPage = $arrPages[8];
        $this->assertEquals($objPage->getStrName(), "sortsubpage_9");
        $this->assertEquals(9, $objPage->getIntSort());


        $objRootPage->deleteObjectFromDatabase();

    }


    public function testHierarchicalSort()
    {
        $objRootPage = new \Kajona\Pages\System\PagesPage();

        $objRootPage->setStrName("test1");
        $objRootPage->updateObjectToDb();


        $objPageL1 = new \Kajona\Pages\System\PagesPage();
        $objPageL1->setStrName("layer_1");
        $objPageL1->updateObjectToDb($objRootPage->getSystemid());

        $objPageL2 = new \Kajona\Pages\System\PagesPage();
        $objPageL2->setStrName("layer_2");
        $objPageL2->updateObjectToDb($objRootPage->getSystemid());

        $objPage = new \Kajona\Pages\System\PagesPage();
        $objPage->setStrName("layer_1_1");
        $objPage->updateObjectToDb($objPageL1->getSystemid());

        $objPage = new \Kajona\Pages\System\PagesPage();
        $objPage->setStrName("layer_1_2");
        $objPage->updateObjectToDb($objPageL1->getSystemid());

        $objPage = new \Kajona\Pages\System\PagesPage();
        $objPage->setStrName("layer_1_3");
        $objPage->updateObjectToDb($objPageL1->getSystemid());

        $objPage = new \Kajona\Pages\System\PagesPage();
        $objPage->setStrName("layer_2_1");
        $objPage->updateObjectToDb($objPageL2->getSystemid());

        $objPage = new \Kajona\Pages\System\PagesPage();
        $objPage->setStrName("layer_2_2");
        $objPage->updateObjectToDb($objPageL2->getSystemid());

        $objPage = new \Kajona\Pages\System\PagesPage();
        $objPage->setStrName("layer_2_3");
        $objPage->updateObjectToDb($objPageL2->getSystemid());


        $this->assertEquals(1, \Kajona\Pages\System\PagesPage::getPageByName("layer_1")->getIntSort());
        $this->assertEquals(2, \Kajona\Pages\System\PagesPage::getPageByName("layer_2")->getIntSort());

        $this->assertEquals(1, \Kajona\Pages\System\PagesPage::getPageByName("layer_1_1")->getIntSort());
        $this->assertEquals(2, \Kajona\Pages\System\PagesPage::getPageByName("layer_1_2")->getIntSort());
        $this->assertEquals(3, \Kajona\Pages\System\PagesPage::getPageByName("layer_1_3")->getIntSort());

        $this->assertEquals(1, \Kajona\Pages\System\PagesPage::getPageByName("layer_2_1")->getIntSort());
        $this->assertEquals(2, \Kajona\Pages\System\PagesPage::getPageByName("layer_2_2")->getIntSort());
        $this->assertEquals(3, \Kajona\Pages\System\PagesPage::getPageByName("layer_2_3")->getIntSort());


        //shifting hierarchies
        \Kajona\Pages\System\PagesPage::getPageByName("layer_2_2")->updateObjectToDb($objPageL1->getSystemid());

        $this->assertEquals(1, \Kajona\Pages\System\PagesPage::getPageByName("layer_1_1")->getIntSort());
        $this->assertEquals(2, \Kajona\Pages\System\PagesPage::getPageByName("layer_1_2")->getIntSort());
        $this->assertEquals(3, \Kajona\Pages\System\PagesPage::getPageByName("layer_1_3")->getIntSort());
        $this->assertEquals(4, \Kajona\Pages\System\PagesPage::getPageByName("layer_2_2")->getIntSort());

        $this->assertEquals(1, \Kajona\Pages\System\PagesPage::getPageByName("layer_2_1")->getIntSort());
        $this->assertEquals(2, \Kajona\Pages\System\PagesPage::getPageByName("layer_2_3")->getIntSort());


        $objRootPage->deleteObjectFromDatabase();
    }


    public function testRandomSortTest()
    {
        $objRootPage = new \Kajona\Pages\System\PagesPage();

        $objRootPage->setStrName("randomSortTest");
        $objRootPage->updateObjectToDb();

        $arrNodes = array();


        for ($intI = 0; $intI < 5; $intI++) {
            $objPage = new \Kajona\Pages\System\PagesPage();
            $objPage->setStrName("l1_".$intI);
            $objPage->updateObjectToDb($objRootPage->getSystemid());

            $arrNodes[] = $objPage->getSystemid();

            for ($intK = 0; $intK < 10; $intK++) {
                $objPageK = new \Kajona\Pages\System\PagesPage();
                $objPageK->setStrName("l2_".$intI);
                $objPageK->updateObjectToDb($objPage->getSystemid());

                $arrNodes[] = $objPageK->getSystemid();
            }
        }

        $intMax = count($arrNodes) - 1;
        for ($intI = 0; $intI < 50; $intI++) {
            $objPage = new \Kajona\Pages\System\PagesPage($arrNodes[rand(0, $intMax)]);

            $objPage->updateObjectToDb($arrNodes[rand(0, $intMax)]);
        }

        $this->validateSingleLevelSort($objRootPage->getSystemid());
        $objRootPage->deleteObjectFromDatabase();
    }

    private function validateSingleLevelSort($strParentId)
    {
        $arrNodes = PagesFolder::getPagesAndFolderList($strParentId);

        for ($intI = 1; $intI <= count($arrNodes); $intI++) {
            $this->validateSingleLevelSort($arrNodes[$intI - 1]->getSystemid());

            $this->assertEquals($intI, $arrNodes[$intI - 1]->getIntSort());
        }
    }


}

