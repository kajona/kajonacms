<?php

namespace Kajona\System\Tests;

require_once __DIR__ . "../../../core/module_system/system/Testbase.php";

use Kajona\Dashboard\System\TodoProviderInterface;
use Kajona\System\System\Pluginmanager;
use Kajona\System\System\Testbase;

class TodoProviderTest extends Testbase
{
    public function testProvider()
    {
        $objPluginManager = new Pluginmanager(TodoProviderInterface::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();
        $arrNames = array();

        foreach ($arrPlugins as $objPlugin) {
            // get all categories
            $arrCategories = $objPlugin->getCategories();
            $arrNames[] = $objPlugin->getName();

            $this->assertInstanceOf("Kajona\\Dashboard\\System\\TodoProviderInterface", $objPlugin);
            $this->assertTrue($objPlugin->getName() != "");
            $this->assertTrue(is_array($arrCategories));

            foreach ($arrCategories as $strCategory => $strCategoryLabel) {
                // get all todos per category
                $arrTodos = $objPlugin->getCurrentTodosByCategory($strCategory);

                $this->assertTrue(is_array($arrTodos));
                $this->assertTrue(!empty($strCategoryLabel));

                foreach ($arrTodos as $objTodo) {
                    $this->assertInstanceOf('class_todo_entry', $objTodo);
                }
            }
        }

        // check whether we have duplicate names if so its an error condition
        $this->assertEquals(count(array_unique($arrNames)), count($arrNames));
    }
}
