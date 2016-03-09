<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Portal;

use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\ArrayIterator;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Carrier;
use Kajona\System\System\Link;
use Kajona\System\System\Toolkit;


/**
 * Portal-Part of the toolkit.
 * Provides a few helpers
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class ToolkitPortal extends Toolkit
{


    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Creates a forward / backward pager out of the passed array
     *
     * @param int $intNumber Records per page
     * @param int $intPage current page
     * @param string $strForward text for the forwardlink
     * @param string $strBack text for the backwardslink
     * @param string $strAction action on the targetpage
     * @param string $strPage title of the targetpage
     * @param mixed $arrData the array of records
     * @param string $strAdd additional params
     * @param string $strPvParam the param used to create the pagenumber-entries
     *
     * @return mixed array containing the created data:
     *                         return => [arrData] = array containing the shortened data
     *                                   [strForward] = link to the next page
     *                                   [strBack]    = link to the previous page
     *                                   [strPages] = Pager ( [0][1] ...)
     */
    public function pager($intNumber, $intPage = 1, $strForward = "next", $strBack = "back", $strAction = "list", $strPage = "", $arrData = array(), $strAdd = "", $strPvParam = "pv")
    {

        if ($intPage <= 0) {
            $intPage = 1;
        }

        if ((int)$intNumber <= 0) {
            $intNumber = 100;
        }

        $arrReturn = array(
            "arrData"    => array(),
            "strForward" => "",
            "strBack"    => "",
            "strPages"   => ""
        );

        //create array-iterator
        $objIterator = new ArrayIterator($arrData);
        $objIterator->setIntElementsPerPage($intNumber);

        //Anything to create?
        if ($objIterator->getNrOfPages() == 1) {
            $arrReturn["arrData"] = $arrData;
        }
        else {
            $strLinkPages = "";
            $strLinkForward = "";
            $strLinkBack = "";

            $arrReturn["arrData"] = $objIterator->getElementsOnPage($intPage);

            //FowardLink
            if ($intPage < $objIterator->getNrOfPages()) {
                $strLinkForward = Link::getLinkPortal($strPage, "", null, $strForward, $strAction, "&".$strPvParam."=".($intPage + 1).$strAdd);
            }
            //BackLink
            if ($intPage > 1) {
                $strLinkBack = Link::getLinkPortal($strPage, "", null, $strBack, $strAction, "&".$strPvParam."=".($intPage - 1).$strAdd);
            }

            //just load the current +-6 pages and the first/last +-3
            $intCounter2 = 1;
            for ($intI = 1; $intI <= $objIterator->getNrOfPages(); $intI++) {
                $bitDisplay = false;
                if ($intCounter2 <= 3) {
                    $bitDisplay = true;
                }
                elseif ($intCounter2 >= ($objIterator->getNrOfPages() - 3)) {
                    $bitDisplay = true;
                }
                elseif ($intCounter2 >= ($intPage - 6) && $intCounter2 <= ($intPage + 6)) {
                    $bitDisplay = true;
                }


                if ($bitDisplay) {
                    if ($intI == $intPage) {
                        $strLinkPages .= "  <strong>".Link::getLinkPortal($strPage, "", null, "[".$intI."]", $strAction, "&".$strPvParam."=".$intI.$strAdd)."</strong>";
                    }
                    else {
                        $strLinkPages .= "  ".Link::getLinkPortal($strPage, "", null, "[".$intI."]", $strAction, "&".$strPvParam."=".$intI.$strAdd);
                    }
                }
                $intCounter2++;
            }


            $arrReturn["strForward"] = $strLinkForward;
            $arrReturn["strBack"] = $strLinkBack;
            $arrReturn["strPages"] = $strLinkPages;
        }

        return $arrReturn;
    }


    /**
     * Creates a forward / backward pager out of the passed array
     *
     * @param ArraySectionIterator $objArraySectionIterator
     * @param string $strForward text for the forwardlink
     * @param string $strBack text for the backwardslink
     * @param string $strAction action on the targetpage
     * @param string $strPage title of the targetpage
     * @param string $strAdd additional params
     * @param string $strPvParam the param used to create the pagenumber-entries
     * @param string $strTemplate if passed, the pager will render all links using the passed template (if the sections are present). Expected sections: pager_fwd, pager_back, pager_entry, pager_entry_active
     *
     * @return mixed array containing the created data:
     *                         return => [strForward] = link to the next page
     *                                   [strBack]    = link to the previous page
     *                                   [strPages] = Pager ( [0][1] ...)
     */
    public function simplePager($objArraySectionIterator, $strForward = "next", $strBack = "back", $strAction = "list", $strPage = "", $strAdd = "", $strPvParam = "pv", $strTemplate = "")
    {

        $arrReturn = array(
            "arrData"    => array(),
            "strForward" => "",
            "strBack"    => "",
            "strPages"   => ""
        );


        //read the template-sections, of given
        $bitTemplate = $strTemplate != "";

        $strLinkPages = "";
        $strLinkForward = "";
        $strLinkBack = "";

        $arrReturn["arrData"] = array();

        $intPage = $objArraySectionIterator->getPageNumber();

        //FowardLink
        if ($intPage < $objArraySectionIterator->getNrOfPages()) {
            if ($bitTemplate) {
                $strLinkForward = $this->objTemplate->fillTemplate(array("pageHref" => Link::getLinkPortalHref($strPage, "", $strAction, "&".$strPvParam."=".($intPage + 1).$strAdd)), $strTemplate, "pager_fwd");
            }
            else {
                $strLinkForward = Link::getLinkPortal($strPage, "", null, $strForward, $strAction, "&".$strPvParam."=".($intPage + 1).$strAdd);
            }

        }
        //BackLink
        if ($intPage > 1) {
            if ($bitTemplate) {
                $strLinkBack = $this->objTemplate->fillTemplateFile(array("pageHref" => Link::getLinkPortalHref($strPage, "", $strAction, "&".$strPvParam."=".($intPage - 1).$strAdd)), $strTemplate, "pager_back");
            }
            else {
                $strLinkBack = Link::getLinkPortal($strPage, "", null, $strBack, $strAction, "&".$strPvParam."=".($intPage - 1).$strAdd);
            }

        }


        //just load the current +-6 pages and the first/last +-3
        $intCounter2 = 1;
        for ($intI = 1; $intI <= $objArraySectionIterator->getNrOfPages(); $intI++) {
            $bitDisplay = false;
            if ($intCounter2 <= 3) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($objArraySectionIterator->getNrOfPages() - 3)) {
                $bitDisplay = true;
            }
            elseif ($intCounter2 >= ($intPage - 6) && $intCounter2 <= ($intPage + 6)) {
                $bitDisplay = true;
            }


            if ($bitDisplay) {

                if ($bitTemplate) {
                    if ($intI == $intPage) {
                        $strLinkPages .= $this->objTemplate->fillTemplateFile(array("pageHref" => Link::getLinkPortalHref($strPage, "", $strAction, "&".$strPvParam."=".$intI.$strAdd), "pageNumber" => $intI), $strTemplate, "pager_entry_active");
                    }
                    else {
                        $strLinkPages .= $this->objTemplate->fillTemplateFile(array("pageHref" => Link::getLinkPortalHref($strPage, "", $strAction, "&".$strPvParam."=".$intI.$strAdd), "pageNumber" => $intI), $strTemplate, "pager_entry");
                    }
                }
                else {
                    if ($intI == $intPage) {
                        $strLinkPages .= "  <strong>".Link::getLinkPortal($strPage, "", null, "[".$intI."]", $strAction, "&".$strPvParam."=".$intI.$strAdd)."</strong>";
                    }
                    else {
                        $strLinkPages .= "  ".Link::getLinkPortal($strPage, "", null, "[".$intI."]", $strAction, "&".$strPvParam."=".$intI.$strAdd);
                    }
                }
            }
            $intCounter2++;
        }

        if ($objArraySectionIterator->getNrOfPages() > 1) {
            $arrReturn["strForward"] = $strLinkForward;
            $arrReturn["strBack"] = $strLinkBack;
            $arrReturn["strPages"] = $strLinkPages;
        }

        return $arrReturn;
    }

    // ******************************************************************************************************
    // --- PORTALEDITOR -------------------------------------------------------------------------------------

    /**
     * Creates the portaleditor toolbar at top of the page
     *
     * @return string
     */
    public function getPeToolbar()
    {
        $strAdminSkin = Carrier::getInstance()->getObjSession()->getAdminSkin();
        $strReturn = $this->objTemplate->fillTemplateFile(array(), AdminskinHelper::getPathForSkin($strAdminSkin)."/elements.tpl", "pe_toolbar");
        return $strReturn;
    }


}
