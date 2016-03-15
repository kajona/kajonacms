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
use Kajona\System\System\InterfaceJStreeNodeLoader;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;

/**
 * @package module_navigation
 * @author stefan.meyer1@yahoo.de
 *
 * @module navigation
 * @moduleId navigation_modul_id_
 */
class NavigationJStreeNodeLoader implements InterfaceJStreeNodeLoader
{

    const NODE_TYPE_PAGE = "page";
    const NODE_TYPE_FOLDER = "folder";

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


    private function getNodeNavigationPoint(NavigationPoint $objSinglePoint) {

        $arrNode = array(
            "id" => $objSinglePoint->getSystemid(),
            "text" => AdminskinHelper::getAdminImage($objSinglePoint->getStrIcon())."&nbsp;".$objSinglePoint->getStrDisplayName(),
            "a_attr"  => array(
                "href"     => Link::getLinkAdminHref("navigation", "list", "&systemid=".$objSinglePoint->getSystemid(), false),
            ),
            "type" => "navigationpoint",
            "children" => count($this->getChildrenObjects($objSinglePoint)) > 0
        );

        return $arrNode;
    }

    private function getNodeNavigationTree(NavigationTree $objSingleEntry) {
        $arrNode = array(
            "id" => $objSingleEntry->getSystemid(),
            "text" => AdminskinHelper::getAdminImage($objSingleEntry->getStrIcon())."&nbsp;".$objSingleEntry->getStrDisplayName(),
            "a_attr"  => array(
                "href"     => Link::getLinkAdminHref("navigation", "list", "&systemid=".$objSingleEntry->getSystemid(), false),
            ),
            "type" => "navigationtree",
            "children" => count($this->getChildrenObjects($objSingleEntry)) > 0
        );

        return $arrNode;
    }

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

    private function getChildrenObjects($objPage) {
        //Handle Children
        $arrNavigations = NavigationPoint::getNaviLayer($objPage->getSystemid());
        $arrNavigations = array_values(array_filter($arrNavigations, function($objPage) {return $objPage->rightView();}));
        return $arrNavigations;
    }
}
