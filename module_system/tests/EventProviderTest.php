<?php

namespace Kajona\System\Tests;

use Kajona\Dashboard\System\EventProviderInterface;
use Kajona\System\System\Date;
use Kajona\System\System\Pluginmanager;

class EventProviderTest extends Testbase
{
    public function testProvider()
    {
        $objPluginManager = new Pluginmanager(EventProviderInterface::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();
        $arrNames = array();

        $objStartDate = new Date(strtotime('-1 month'));
        $objEndDate = new Date(strtotime('+1 month'));

        foreach ($arrPlugins as $objPlugin) {
            // get all categories
            $arrCategories = $objPlugin->getCategories();
            $arrNames[] = $objPlugin->getName();

            $this->assertInstanceOf('Kajona\\Dashboard\\System\\EventProviderInterface', $objPlugin);
            $this->assertTrue($objPlugin->getName() != "");
            $this->assertTrue(is_array($arrCategories));

            foreach ($arrCategories as $strCategory => $strCategoryLabel) {
                // get all todos per category
                $arrTodos = $objPlugin->getEventsByCategoryAndDate($strCategory, $objStartDate, $objEndDate);

                $this->assertTrue(is_array($arrTodos));
                $this->assertTrue(!empty($strCategoryLabel));

                foreach ($arrTodos as $objTodo) {
                    $this->assertInstanceOf('Kajona\\Dashboard\\System\\EventEntry', $objTodo);
                }
            }
        }

        // check whether we have duplicate names if so its an error condition
        $this->assertEquals(count(array_unique($arrNames)), count($arrNames));
    }
}
