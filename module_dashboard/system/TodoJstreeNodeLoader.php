<?php
/*"******************************************************************************************************
*   (c) 2010-2015 ARTEMEON                                                                              *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

namespace Kajona\Dashboard\System;

use Kajona\System\System\Carrier;
use Kajona\System\System\InterfaceJStreeNodeLoader;
use Kajona\System\System\Link;

/**
 * @package module_prozessverwaltung
 * @author christoph.kappestein@artemeon.de
 */
class TodoJstreeNodeLoader implements InterfaceJStreeNodeLoader
{
    private $objToolkit = null;

    public function __construct()
    {
        $this->objToolkit = Carrier::getInstance()->getObjToolkit("admin");
    }

    public function getChildNodes($strSystemId)
    {
        $arrProvider = array();
        $arrCategories = TodoRepository::getAllCategories();
        foreach ($arrCategories as $strProviderName => $arrTaskCategories) {
            foreach ($arrTaskCategories as $strKey => $strCategoryName) {
                if (!isset($arrProvider[$strProviderName])) {
                    $arrProvider[$strProviderName] = array();
                }

                $arrProvider[$strProviderName][$strKey] = $strCategoryName;
            }
        }

        $arrProviderNodes = array();
        foreach ($arrProvider as $strProviderName => $arrCats) {

            $arrCategoryNodes = array();
            foreach ($arrCats as $strKey => $strCategoryName) {
                $strJsonKey =  json_encode($strKey);

                $arrCategoryNodes[] = array(
                    "id" => generateSystemid(),
                    "text" => $this->objToolkit->getTooltipText($strCategoryName, $strCategoryName),
                    "type" => "navigationpoint",
                    "a_attr"  => array(
                        "href"    => "#",
                        "onclick" => "KAJONA.admin.dashboard.todo.loadCategory($strJsonKey,'')"
                    ),
                    "state" => array(
                        "opened"  => true
                    ),
                    "children" => false
                );
            }

            $strKeys = implode(",", array_keys($arrCats));
            $strKeysJson = json_encode($strKeys);
            $arrProviderNodes[] = array(
                "id" => generateSystemid(),
                "text" => '<i class="fa fa-folder-o"></i>&nbsp;' . $this->objToolkit->getTooltipText($strProviderName, $strProviderName),
                "type" => "navigationpoint",
                "a_attr"  => array(
                    "href"    => "#",
                    "onclick" => "KAJONA.admin.dashboard.todo.loadCategory($strKeysJson,'')"
                ),
                "state" => array(
                    "opened"  => true
                ),
                "children" => $arrCategoryNodes
            );
        }

        return $arrProviderNodes;
    }

    public function getNode($strSystemId)
    {
        return array(
            "id" => generateSystemid(),
            "text" => $this->objToolkit->getTooltipText("Kategorien", "Kategorien"),
            "type" => "navigationpoint",
            "a_attr"  => array(
                "href"    => "#",
                "onclick" => "KAJONA.admin.dashboard.todo.loadCategory('','')"
            )
        );
    }
}
