<?php

namespace Kajona\System\Tests;

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

            /** @var TodoProviderInterface[] $arrCategories */
            /** @var TodoProviderInterface $objPlugin */
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
                    $this->assertInstanceOf('Kajona\\Dashboard\\System\\TodoEntry', $objTodo);
                }
            }
        }
    }
}
