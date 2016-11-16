<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Navigation\System;

use Kajona\Pages\System\PagesPage;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\JStreeNodeLoaderInterface;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\SystemJSTreeNode;

/**
 * @package module_navigation
 * @author stefan.meyer1@yahoo.de
 *
 * @module navigation
 * @moduleId navigation_modul_id_
 */
class NavigationJStreeNodeLoader implements JStreeNodeLoaderInterface
{

    const NODE_TYPE_NAVIGATIONPOINT = "navigationpoint";
    const NODE_TYPE_NAVIGATIONTREE = "navigationtree";

    private $objToolkit = null;

    /**
     * NavigationJStreeNodeLoader constructor.
     */
    public function __construct()
    {
        $this->objToolkit = Carrier::getInstance()->getObjToolkit("admin");
    }

    public function getChildNodes($strSystemId) {
        $arrNodes = array();

        //1. Get Page
        /** @var PagesPage $objSingleProcess */
        $objSingleProcess = Objectfactory::getInstance()->getObject($strSystemId);

        //2. Handle Children
        $arrChildrenProcesse = $this->getChildrenObjects($objSingleProcess);

        //3. Prozesse Childs
        foreach($arrChildrenProcesse as $objSubProcess) {
            $arrNodes[] = $this->getNode($objSubProcess->getStrSystemid());
        }

        return $arrNodes;
    }

    /**
     * @param NavigationPoint $objSinglePoint
     *
     * @return SystemJSTreeNode
     */
    private function getNodeNavigationPoint(NavigationPoint $objSinglePoint) {

        $objNode = new SystemJSTreeNode();
        $objNode->setStrId($objSinglePoint->getSystemid());
        $objNode->setStrText(AdminskinHelper::getAdminImage($objSinglePoint->getStrIcon())."&nbsp;".$objSinglePoint->getStrDisplayName());
        $objNode->setStrType(self::NODE_TYPE_NAVIGATIONPOINT);
        $objNode->setArrChildren(count($this->getChildrenObjects($objSinglePoint)) > 0);
        $objNode->addAAttrAttr(
            SystemJSTreeNode::STR_NODE_AATTR_HREF,
            Link::getLinkAdminHref("navigation", "list", "&systemid=".$objSinglePoint->getSystemid(), false)
        );
        $objNode->addDataAttr(
            SystemJSTreeNode::STR_NODE_DATA_RIGHTEDIT,
            $objSinglePoint->rightEdit()
        );

        return $objNode;
    }

    /**
     * @param NavigationTree $objSingleEntry
     *
     * @return SystemJSTreeNode
     */
    private function getNodeNavigationTree(NavigationTree $objSingleEntry) {

        $objNode = new SystemJSTreeNode();
        $objNode->setStrId($objSingleEntry->getSystemid());
        $objNode->setStrText(AdminskinHelper::getAdminImage($objSingleEntry->getStrIcon())."&nbsp;".$objSingleEntry->getStrDisplayName());
        $objNode->setStrType(self::NODE_TYPE_NAVIGATIONTREE);
        $objNode->setArrChildren(count($this->getChildrenObjects($objSingleEntry)) > 0);
        $objNode->addAAttrAttr(
            SystemJSTreeNode::STR_NODE_AATTR_HREF,
            Link::getLinkAdminHref("navigation", "list", "&systemid=".$objSingleEntry->getSystemid(), false)
        );
        $objNode->addDataAttr(
            SystemJSTreeNode::STR_NODE_DATA_RIGHTEDIT,
            $objSingleEntry->rightEdit()
        );

        return $objNode;
    }

    /**
     * @inheritdoc
     */
    public function getNode($strSystemId) {

        //1. Get Process
        $objSingleEntry = Objectfactory::getInstance()->getObject($strSystemId);

        if ($objSingleEntry instanceof NavigationPoint) {
            return $this->getNodeNavigationPoint($objSingleEntry);
        }
        elseif ($objSingleEntry instanceof NavigationTree) {
            return $this->getNodeNavigationTree($objSingleEntry);
        }

        return null;
    }

    /**
     * @param $objPage
     *
     * @return NavigationPoint[]
     */
    private function getChildrenObjects($objPage) {
        //Handle Children
        $arrNavigations = NavigationPoint::getNaviLayer($objPage->getSystemid());
        $arrNavigations = array_values(array_filter($arrNavigations, function($objPage) {return $objPage->rightView();}));
        return $arrNavigations;
    }
}
