<?php
/*"******************************************************************************************************
*   (c) 2010-2015 ARTEMEON                                                                              *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

namespace Kajona\Dashboard\System;

use Kajona\System\System\Carrier;
use Kajona\System\System\InterfaceJStreeNodeLoader;
use Kajona\System\System\SystemJSTreeNode;

/**
 * @package module_prozessverwaltung
 * @author christoph.kappestein@artemeon.de
 */
class TodoJstreeNodeLoader implements InterfaceJStreeNodeLoader
{
    private $objToolkit = null;

    /**
     * TodoJstreeNodeLoader constructor.
     */
    public function __construct()
    {
        $this->objToolkit = Carrier::getInstance()->getObjToolkit("admin");
    }

    /**
     * @inheritdoc
     */
    public function getChildNodes($strSystemId)
    {
        $arrProvider = array();
        $arrCategories = TodoRepository::getAllCategories();
        foreach($arrCategories as $strProviderName => $arrTaskCategories) {
            foreach($arrTaskCategories as $strKey => $strCategoryName) {
                if(!isset($arrProvider[$strProviderName])) {
                    $arrProvider[$strProviderName] = array();
                }

                $arrProvider[$strProviderName][$strKey] = $strCategoryName;
            }
        }

        $arrProviderNodes = array();
        foreach($arrProvider as $strProviderName => $arrCats) {

            $arrCategoryNodes = array();
            foreach($arrCats as $strKey => $strCategoryName) {
                $strJsonKey = json_encode($strKey);

                $objNode = new SystemJSTreeNode();
                $objNode->setStrId(generateSystemid());
                $objNode->setStrText($this->objToolkit->getTooltipText($strCategoryName, $strCategoryName));
                $objNode->setArrChildren(false);
                $objNode->setStrType("navigationpoint");
                $objNode->addAAttrAttr(
                    SystemJSTreeNode::STR_NODE_AATTR_HREF,
                    "#"
                );
                $objNode->addAAttrAttr(
                    "onclick",
                    "KAJONA.admin.dashboard.todo.loadCategory($strJsonKey,'')"
                );
                $objNode->addStateAttr(
                    SystemJSTreeNode::STR_NODE_STATE_OPENED,
                    true
                );
                $arrCategoryNodes[] = $objNode;
            }

            $strKeys = implode(",", array_keys($arrCats));
            $strKeysJson = json_encode($strKeys);

            $objNode = new SystemJSTreeNode();
            $objNode->setStrId(generateSystemid());
            $objNode->setStrText('<i class="fa fa-folder-o"></i>&nbsp;'.$this->objToolkit->getTooltipText($strProviderName, $strProviderName));
            $objNode->setArrChildren($arrCategoryNodes);
            $objNode->setStrType("navigationpoint");
            $objNode->addAAttrAttr(
                SystemJSTreeNode::STR_NODE_AATTR_HREF,
                "#"
            );
            $objNode->addAAttrAttr(
                "onclick",
                "KAJONA.admin.dashboard.todo.loadCategory($strKeysJson,'')"
            );
            $objNode->addStateAttr(
                SystemJSTreeNode::STR_NODE_STATE_OPENED,
                true
            );
        }

        return $arrProviderNodes;
    }

    /**
     * @inheritdoc
     */
    public function getNode($strSystemId)
    {
        $objNode = new SystemJSTreeNode();
        $objNode->setStrId(generateSystemid());
        $objNode->setStrText($this->objToolkit->getTooltipText("Kategorien", "Kategorien"));
        $objNode->setStrType("navigationpoint");
        $objNode->addAAttrAttr(
            SystemJSTreeNode::STR_NODE_AATTR_HREF,
            "#"
        );
        $objNode->addAAttrAttr(
            "onclick",
            "KAJONA.admin.dashboard.todo.loadCategory('','')"
        );

        return $objNode;

    }
}
