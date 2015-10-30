<?php

require_once (__DIR__."/../../module_system/system/class_testbase.php");

class class_test_event_provider extends class_testbase
{
    public function testProvider()
    {
        $objPluginManager = new class_pluginmanager(interface_event_provider::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();
        $arrNames = array();

        $objStartDate = new class_date(strtotime('-1 month'));
        $objEndDate = new class_date(strtotime('+1 month'));

        foreach ($arrPlugins as $objPlugin) {
            // get all categories
            $arrCategories = $objPlugin->getCategories();
            $arrNames[] = $objPlugin->getName();

            $this->assertInstanceOf('interface_event_provider', $objPlugin);
            $this->assertTrue($objPlugin->getName() != "");
            $this->assertTrue(is_array($arrCategories));

            foreach ($arrCategories as $strCategory => $strCategoryLabel) {
                // get all todos per category
                $arrTodos = $objPlugin->getEventsByCategoryAndDate($strCategory, $objStartDate, $objEndDate);

                $this->assertTrue(is_array($arrTodos));
                $this->assertTrue(!empty($strCategoryLabel));

                foreach ($arrTodos as $objTodo) {
                    $this->assertInstanceOf('class_event_entry', $objTodo);
                }
            }
        }

        // check whether we have duplicate names if so its an error condition
        $this->assertEquals(count(array_unique($arrNames)), count($arrNames));
    }
}
