<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Pages\System;

use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\JStreeNodeLoaderInterface;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\SystemJSTreeNode;
use Kajona\System\System\SystemModule;


/**
 * @author stefan.meyer1@yahoo.de
 *
 * @module pages
 * @moduleId _pages_modul_id_
 */
class PagesJstreeNodeLoader implements JStreeNodeLoaderInterface
{

    const NODE_TYPE_PAGE_MODULE = "page_module";
    const NODE_TYPE_PAGE = "page";
    const NODE_TYPE_FOLDER = "folder";

    private $objToolkit = null;

    /**
     * PagesJstreeNodeLoader constructor.
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
        $arrNodes = array();

        //1. Get Page
        /** @var PagesPage $objSinglePage */
        $objSinglePage = Objectfactory::getInstance()->getObject($strSystemId);

        //2. Handle Children
        $arrChildrenPages = $this->getChildrenObjects($objSinglePage);

        //3. Node Childs
        foreach($arrChildrenPages as $objSubPage) {
            $arrNodes[] = $this->getNode($objSubPage->getStrSystemid());
        }


        return $arrNodes;
    }

    /**
     * @inheritdoc
     */
    private function getNodeModule(SystemModule $objModule)
    {
        $strLink = "";
        if($objModule->rightEdit()) {
            $strLink = Link::getLinkAdminHref("pages", "list", "", false);
        }

        $objNode = new SystemJSTreeNode();
        $objNode->setStrId($objModule->getSystemid());
        $objNode->setStrText(AdminskinHelper::getAdminImage($objModule->getStrIcon())."&nbsp;".$objModule->getStrDisplayName());
        $objNode->setStrType(self::NODE_TYPE_PAGE_MODULE);
        $objNode->setArrChildren(count($this->getChildrenObjects($objModule)) > 0);
        $objNode->addAAttrAttr(
            SystemJSTreeNode::STR_NODE_AATTR_HREF,
            $strLink
        );
        $objNode->addDataAttr(
            SystemJSTreeNode::STR_NODE_DATA_RIGHTEDIT,
            $objModule->rightEdit()
        );

        return $objNode;
    }

    private function getNodeFolder(PagesFolder $objSingleEntry)
    {
        $strLink = "";
        if($objSingleEntry->rightEdit()) {
            $strLink = Link::getLinkAdminHref("pages", "list", "systemid=".$objSingleEntry->getSystemid(), false);
        }

        $objNode = new SystemJSTreeNode();
        $objNode->setStrId($objSingleEntry->getSystemid());
        $objNode->setStrText(AdminskinHelper::getAdminImage($objSingleEntry->getStrIcon())."&nbsp;".$objSingleEntry->getStrDisplayName());
        $objNode->setStrType(self::NODE_TYPE_FOLDER);
        $objNode->setArrChildren(count($this->getChildrenObjects($objSingleEntry)) > 0);
        $objNode->addAAttrAttr(
            SystemJSTreeNode::STR_NODE_AATTR_HREF,
            $strLink
        );
        $objNode->addDataAttr(
            SystemJSTreeNode::STR_NODE_DATA_RIGHTEDIT,
            $objSingleEntry->rightEdit()
        );

        return $objNode;
    }

    private function getNodePage(PagesPage $objSingleEntry)
    {

        $strTargetId = $objSingleEntry->getSystemid();
        if($objSingleEntry->getIntType() == PagesPage::$INT_TYPE_ALIAS && PagesPage::getPageByName($objSingleEntry->getStrAlias()) != null) {
            $strTargetId = PagesPage::getPageByName($objSingleEntry->getStrAlias())->getSystemid();
        }

        $strLink = "";
        if($objSingleEntry->getIntType() == PagesPage::$INT_TYPE_ALIAS && Objectfactory::getInstance()->getObject($strTargetId)->rightEdit()) {
            $strLink = Link::getLinkAdminHref("pages_content", "list", "systemid=".$strTargetId, false);
        }
        else if($objSingleEntry->getIntType() == PagesPage::$INT_TYPE_PAGE && $objSingleEntry->rightEdit()) {
            $strLink = Link::getLinkAdminHref("pages_content", "list", "systemid=".$objSingleEntry->getSystemid(), false);
        }

        $objNode = new SystemJSTreeNode();
        $objNode->setStrId($objSingleEntry->getSystemid());
        $objNode->setStrText(AdminskinHelper::getAdminImage($objSingleEntry->getStrIcon())."&nbsp;".$objSingleEntry->getStrDisplayName());
        $objNode->setStrType(self::NODE_TYPE_PAGE);
        $objNode->setArrChildren(count($this->getChildrenObjects($objSingleEntry)) > 0);
        $objNode->addAAttrAttr(
            SystemJSTreeNode::STR_NODE_AATTR_HREF,
            $strLink
        );
        $objNode->addDataAttr(
            SystemJSTreeNode::STR_NODE_DATA_RIGHTEDIT,
            $objSingleEntry->rightEdit()
        );

        return $objNode;
    }

    public function getNode($strSystemId)
    {

        //1. Get Process
        /** @var PagesPage $objSinglePage */
        $objSingleEntry = Objectfactory::getInstance()->getObject($strSystemId);

        if($objSingleEntry instanceof SystemModule) {
            return $this->getNodeModule($objSingleEntry);
        }
        else if($objSingleEntry instanceof PagesFolder) {
            return $this->getNodeFolder($objSingleEntry);
        }
        else if($objSingleEntry instanceof PagesPage) {
            return $this->getNodePage($objSingleEntry);
        }

        return null;
    }

    /**
     * @param $objPage
     *
     * @return PagesFolder[]|PagesPage[]
     */
    private function getChildrenObjects($objPage)
    {
        //Handle Children
        $arrPages = PagesFolder::getPagesAndFolderList($objPage->getSystemid());
        $arrPages = array_values(array_filter($arrPages, function ($objPage) {
            return $objPage->rightView();
        }));
        return $arrPages;
    }
}
